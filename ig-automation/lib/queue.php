<?php
/**
 * Content queue — MySQL-backed store for posts.
 *
 * Drop-in replacement for the former JSON-file queue: the public API
 * (all/save/update/byStatus) and the item-array shape are unchanged, so the
 * publisher, insights, review, create and seed scripts work without changes
 * beyond their constructor call (`new Queue(vcw_db($config))`).
 *
 *   $q = new Queue($pdo);
 *   $q->all();                 // array of item arrays
 *   $q->byStatus('approved');  // filtered
 *   $q->save($items);          // upsert each item by id (transaction)
 *   $q->update($id, fn);       // read-modify-write one item (transaction)
 */

class Queue
{
    private PDO $db;

    /** Scalar columns (everything except `media`, which is JSON-encoded). */
    private const COLS = [
        'id', 'type', 'caption', 'hashtags', 'scheduled_at', 'status',
        'created_at', 'approved_at', 'published_at', 'failed_at',
        'media_id', 'cover', 'error',
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /** All posts, ordered by id, as item arrays. */
    public function all(): array
    {
        $rows = $this->db->query('SELECT * FROM posts ORDER BY id')->fetchAll();
        return array_map([$this, 'rowToItem'], $rows);
    }

    /** Posts with a given status. */
    public function byStatus(string $status): array
    {
        $st = $this->db->prepare('SELECT * FROM posts WHERE status = ? ORDER BY id');
        $st->execute([$status]);
        return array_map([$this, 'rowToItem'], $st->fetchAll());
    }

    /** Upsert every item by id, in one transaction. */
    public function save(array $items): void
    {
        $sql = $this->upsertSql();
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($items as $item) {
                $stmt->execute($this->itemToParams($item));
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Read one item, apply the mutator by reference, write it back. */
    public function update(string $id, callable $mutator): bool
    {
        $this->db->beginTransaction();
        try {
            $st = $this->db->prepare('SELECT * FROM posts WHERE id = ?');
            $st->execute([$id]);
            $row = $st->fetch();
            if (!$row) {
                $this->db->rollBack();
                return false;
            }
            $item = $this->rowToItem($row);
            $mutator($item);
            $this->db->prepare($this->upsertSql())->execute($this->itemToParams($item));
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ---- internals ----------------------------------------------------------

    /** DB row -> item array (matches the old JSON shape; drops null/empty keys). */
    private function rowToItem(array $r): array
    {
        $item = [];
        foreach (self::COLS as $c) {
            if (isset($r[$c]) && $r[$c] !== '') {
                $item[$c] = $r[$c];
            }
        }
        $item['media'] = isset($r['media']) && $r['media'] !== null
            ? (json_decode($r['media'], true) ?: [])
            : [];
        return $item;
    }

    /** Item array -> ordered params for the upsert (id, …cols, media). */
    private function itemToParams(array $it): array
    {
        $params = [];
        foreach (self::COLS as $c) {
            $params[] = $it[$c] ?? null;
        }
        $params[] = json_encode($it['media'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return $params;
    }

    /** INSERT … ON DUPLICATE KEY UPDATE for all columns. */
    private function upsertSql(): string
    {
        $cols    = array_merge(self::COLS, ['media']);
        $place   = implode(', ', array_fill(0, count($cols), '?'));
        $colList = implode(', ', $cols);
        $updates = implode(', ', array_map(
            fn($c) => "{$c} = VALUES({$c})",
            array_filter($cols, fn($c) => $c !== 'id') // never update the PK
        ));
        return "INSERT INTO posts ({$colList}) VALUES ({$place}) "
             . "ON DUPLICATE KEY UPDATE {$updates}";
    }
}
