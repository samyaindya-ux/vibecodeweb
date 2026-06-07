<?php
/**
 * Content queue helpers — read/write queue.json with a file lock so the
 * publisher cron and the review dashboard never corrupt each other's writes.
 */

class Queue
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
        if (!file_exists($path)) {
            file_put_contents($path, "[]");
        }
    }

    /** Load all items. */
    public function all(): array
    {
        $raw = file_get_contents($this->path);
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /** Overwrite the whole queue (pretty-printed). */
    public function save(array $items): void
    {
        $fp = fopen($this->path, 'c+');
        if (!$fp) {
            throw new RuntimeException("Cannot open queue: {$this->path}");
        }
        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    /** Update a single item by id via a callback; returns true if found. */
    public function update(string $id, callable $mutator): bool
    {
        $items = $this->all();
        $found = false;
        foreach ($items as &$item) {
            if (($item['id'] ?? null) === $id) {
                $mutator($item);
                $found = true;
                break;
            }
        }
        unset($item);
        if ($found) {
            $this->save($items);
        }
        return $found;
    }

    /** Items matching a status. */
    public function byStatus(string $status): array
    {
        return array_values(array_filter($this->all(), fn($i) => ($i['status'] ?? '') === $status));
    }
}
