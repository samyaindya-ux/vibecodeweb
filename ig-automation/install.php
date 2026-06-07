<?php
/**
 * One-time installer — run once in the browser, then DELETE this file.
 *
 * Creates the posts + users tables, seeds the admin account, and imports any
 * existing queue.json into the posts table. Safe to re-run: it only seeds the
 * admin if the users table is empty and only imports the queue if the posts
 * table is empty.
 *
 * No login guard by design — the users table may not exist yet. It writes only
 * when tables are empty and prints no secrets.
 */

header('Content-Type: text/html; charset=utf-8');

require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/queue.php';

$configPath = __DIR__ . '/config.php';
$steps = [];   // [ok(bool), message]

function step(array &$steps, bool $ok, string $msg): void { $steps[] = [$ok, $msg]; }

$fatal = '';
$pdo   = null;

if (!file_exists($configPath)) {
    $fatal = 'Missing config.php — copy config.sample.php to config.php and fill in the db_* keys first.';
} else {
    $config = require $configPath;
    try {
        $pdo = vcw_db($config);
        step($steps, true, 'Connected to database "' . htmlspecialchars($config['db_name'] ?? '') . '".');
    } catch (Throwable $e) {
        $fatal = 'Could not connect to the database: ' . $e->getMessage();
    }
}

if (!$fatal && $pdo) {
    // 1. Create tables.
    try {
        $sql = file_get_contents(__DIR__ . '/schema.sql');
        // Strip full-line SQL comments, then run each ;-terminated statement separately
        // (PDO can't always run multi-statement strings).
        $lines = array_filter(explode("\n", $sql), fn($l) => strpos(ltrim($l), '--') !== 0);
        $clean = implode("\n", $lines);
        foreach (array_filter(array_map('trim', explode(';', $clean))) as $stmt) {
            if ($stmt === '') continue;
            $pdo->exec($stmt);
        }
        step($steps, true, 'Tables ready (posts, users) and admin seeded.');
    } catch (Throwable $e) {
        step($steps, false, 'Schema step failed: ' . $e->getMessage());
    }

    // 2. Report admin.
    try {
        $adminExists = (bool) vcw_find_user($pdo, 'rehansh');
        step($steps, true, $adminExists
            ? 'Admin user "rehansh" present (existing password works).'
            : 'Admin user "rehansh" not found — check schema seed.');
    } catch (Throwable $e) {
        step($steps, false, 'User check failed: ' . $e->getMessage());
    }

    // 3. Import queue.json into posts (only if posts is empty).
    try {
        $postCount = (int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
        if ($postCount > 0) {
            step($steps, true, "posts table already has {$postCount} row(s) — import skipped.");
        } else {
            $jsonPath = $config['queue_path'] ?? (__DIR__ . '/queue.json');
            if (file_exists($jsonPath)) {
                $rows = json_decode(file_get_contents($jsonPath), true);
                if (is_array($rows) && $rows) {
                    (new Queue($pdo))->save($rows);
                    step($steps, true, 'Imported ' . count($rows) . ' post(s) from queue.json.');
                } else {
                    step($steps, true, 'queue.json was empty — nothing to import.');
                }
            } else {
                step($steps, true, 'No queue.json found — starting with an empty posts table.');
            }
        }
    } catch (Throwable $e) {
        step($steps, false, 'Queue import failed: ' . $e->getMessage());
    }
}

$allOk = !$fatal && !in_array(false, array_map(fn($s) => $s[0], $steps), true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VibeCodeWeb · Installer</title>
<style>
  body { margin:0; background:#020617; color:#f8fafc; font-family:'Segoe UI',system-ui,sans-serif;
    display:flex; min-height:100vh; align-items:center; justify-content:center; padding:24px; }
  .card { background:#1e293b; border:1px solid rgba(255,255,255,.08); border-radius:18px;
    padding:32px; max-width:560px; width:100%; }
  h1 { margin:0 0 18px; font-size:22px; }
  .step { padding:10px 14px; border-radius:10px; margin-bottom:8px; font-size:14px; }
  .ok   { background:rgba(16,185,129,.10); border:1px solid rgba(16,185,129,.3); color:#6ee7b7; }
  .bad  { background:rgba(239,68,68,.10); border:1px solid rgba(239,68,68,.3); color:#fca5a5; }
  .warn { background:rgba(245,158,11,.12); border:1px solid rgba(245,158,11,.4); color:#fcd34d;
    border-radius:12px; padding:16px; margin:22px 0 10px; font-weight:600; }
  a.btn { display:inline-block; margin-top:14px; background:#10b981; color:#022; text-decoration:none;
    border-radius:999px; padding:12px 26px; font-weight:700; }
</style>
</head>
<body>
  <div class="card">
    <h1>📸 VibeCodeWeb Installer</h1>
    <?php if ($fatal): ?>
      <div class="step bad">⚠️ <?= htmlspecialchars($fatal) ?></div>
    <?php else: ?>
      <?php foreach ($steps as [$ok, $msg]): ?>
        <div class="step <?= $ok ? 'ok' : 'bad' ?>"><?= $ok ? '✓' : '✕' ?> <?= htmlspecialchars($msg) ?></div>
      <?php endforeach; ?>
      <?php if ($allOk): ?>
        <div class="warn">🔒 Setup complete. DELETE this file (install.php) now — it should not stay on the server.</div>
        <a class="btn" href="login.php">Go to login →</a>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</body>
</html>
