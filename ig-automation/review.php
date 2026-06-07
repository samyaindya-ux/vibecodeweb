<?php
/**
 * Review dashboard — the human approval gate.
 *
 * Open at:  http://localhost/vibecodeweb/ig-automation/review.php
 * Shows every post awaiting review with its image(s), caption, hashtags and
 * scheduled time. Approve -> publisher will post it when due. Reject -> skipped.
 *
 * Nothing is ever published until you Approve it here.
 */

require __DIR__ . '/auth.php';
if (empty($_SESSION['uid'])) { header('Location: login.php'); exit; }
require __DIR__ . '/lib/queue.php';
require __DIR__ . '/lib/db.php';

if (!file_exists(__DIR__ . '/config.php')) {
    http_response_code(500);
    exit('Missing config.php — fill in credentials (including db_*) first.');
}
$config = require __DIR__ . '/config.php';

date_default_timezone_set($config['timezone'] ?? 'Asia/Kolkata');
$queue = new Queue(vcw_db($config));

// ---- handle actions ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    vcw_csrf_check();
    $id     = $_POST['id'] ?? '';
    $action = $_POST['action'] ?? '';
    if ($id && $action === 'approve') {
        $queue->update($id, function (&$it) {
            $it['status'] = 'approved';
            $it['approved_at'] = date('c');
            if (!empty($_POST['scheduled_at'] ?? '')) {
                $it['scheduled_at'] = $_POST['scheduled_at'];
            }
        });
    } elseif ($id && $action === 'reject') {
        $queue->update($id, fn(&$it) => $it['status'] = 'rejected');
    } elseif ($id && $action === 'save') {
        $queue->update($id, function (&$it) {
            $it['caption']      = $_POST['caption']      ?? $it['caption'];
            $it['hashtags']     = $_POST['hashtags']     ?? $it['hashtags'];
            $it['scheduled_at'] = $_POST['scheduled_at'] ?? $it['scheduled_at'];
        });
    }
    header('Location: review.php');
    exit;
}

$items   = $queue->all();
$pending = array_values(array_filter($items, fn($i) => ($i['status'] ?? '') === 'pending_review'));
$counts  = array_count_values(array_map(fn($i) => $i['status'] ?? 'unknown', $items));
$assetsRel = '../ig-assets/'; // served from localhost/vibecodeweb/
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VibeCodeWeb · IG Review</title>
<style>
  :root { --bg:#020617; --card:#1e293b; --saffron:#f97316; --neon:#10b981; --primary:#3b82f6; --muted:#94a3b8; }
  * { box-sizing:border-box; }
  body { margin:0; background:var(--bg); color:#f8fafc; font-family:'Segoe UI',system-ui,sans-serif; }
  header { padding:24px 32px; border-bottom:1px solid rgba(255,255,255,.08);
    background:linear-gradient(90deg,rgba(249,115,22,.12),rgba(59,130,246,.12),rgba(16,185,129,.12)); }
  h1 { margin:0; font-size:22px; } .sub { color:var(--muted); font-size:13px; margin-top:4px; }
  .wrap { max-width:1100px; margin:0 auto; padding:24px 32px; }
  .stats { display:flex; gap:16px; flex-wrap:wrap; margin-bottom:24px; }
  .stat { background:var(--card); border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:10px 16px; font-size:13px; }
  .stat b { color:var(--neon); }
  .post { display:grid; grid-template-columns:300px 1fr; gap:20px; background:var(--card);
    border:1px solid rgba(255,255,255,.08); border-radius:18px; padding:18px; margin-bottom:20px; }
  .post img { width:100%; border-radius:12px; border:1px solid rgba(255,255,255,.08); display:block; }
  .thumbs { display:flex; gap:6px; flex-wrap:wrap; }
  .thumbs img { width:64px; height:64px; object-fit:cover; }
  .badge { display:inline-block; font-size:11px; padding:3px 10px; border-radius:999px;
    background:rgba(59,130,246,.18); color:#93c5fd; text-transform:uppercase; letter-spacing:.05em; }
  textarea, input[type=text], input[type=datetime-local] { width:100%; background:#0f172a; color:#f8fafc;
    border:1px solid rgba(255,255,255,.12); border-radius:10px; padding:10px; font:inherit; margin-top:6px; }
  textarea { min-height:120px; resize:vertical; }
  label { font-size:12px; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; }
  .row { display:flex; gap:12px; margin-top:14px; flex-wrap:wrap; align-items:flex-end; }
  button { border:none; border-radius:999px; padding:11px 22px; font-weight:700; cursor:pointer; font-size:14px; }
  .approve { background:var(--neon); color:#022; }
  .reject  { background:transparent; color:#fca5a5; border:1px solid #fca5a5; }
  .save    { background:transparent; color:#93c5fd; border:1px solid #93c5fd; }
  .empty { text-align:center; color:var(--muted); padding:80px 0; }
</style>
</head>
<body>
<header>
  <h1>📸 VibeCodeWeb — Instagram Review</h1>
  <div class="sub">Approve posts to queue them for publishing · @vibecodeweb.in</div>
  <div style="margin-top:8px;display:flex;gap:10px;flex-wrap:wrap;">
    <a href="create.php" style="display:inline-block;background:#10b981;color:#022;border-radius:999px;padding:9px 20px;font-weight:700;font-size:13px;text-decoration:none;">✏️ New Post</a>
    <a href="users.php" style="display:inline-block;color:#94a3b8;border:1px solid rgba(255,255,255,.12);border-radius:999px;padding:9px 18px;font-size:13px;text-decoration:none;">👥 Users</a>
    <a href="logout.php" style="display:inline-block;color:#94a3b8;border:1px solid rgba(255,255,255,.12);border-radius:999px;padding:9px 18px;font-size:13px;text-decoration:none;">Log out (<?= htmlspecialchars($_SESSION['uname'] ?? '') ?>)</a>
  </div>
</header>
<div class="wrap">
  <div class="stats">
    <div class="stat">Pending review: <b><?= $counts['pending_review'] ?? 0 ?></b></div>
    <div class="stat">Approved: <b><?= $counts['approved'] ?? 0 ?></b></div>
    <div class="stat">Published: <b><?= $counts['published'] ?? 0 ?></b></div>
    <div class="stat">Rejected: <b><?= $counts['rejected'] ?? 0 ?></b></div>
    <?php if (!empty($counts['failed'])): ?><div class="stat">⚠️ Failed: <b style="color:#fca5a5"><?= $counts['failed'] ?></b></div><?php endif; ?>
  </div>

  <?php if (!$pending): ?>
    <div class="empty">🎉 Nothing waiting for review. The content engine will add more.</div>
  <?php endif; ?>

  <?php foreach ($pending as $p): ?>
    <div class="post">
      <div>
        <?php $media = $p['media'] ?? []; ?>
        <?php if ($media): ?>
          <img src="<?= htmlspecialchars($assetsRel . basename($media[0])) ?>" alt="preview"
               onerror="this.style.opacity=.3;this.alt='image not generated yet';">
          <?php if (count($media) > 1): ?>
            <div class="thumbs" style="margin-top:8px;">
              <?php foreach (array_slice($media, 1) as $m): ?>
                <img src="<?= htmlspecialchars($assetsRel . basename($m)) ?>" alt="">
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
        <div style="margin-top:10px;"><span class="badge"><?= htmlspecialchars($p['type'] ?? 'image') ?></span></div>
      </div>
      <form method="post">
        <?= vcw_csrf_field() ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
        <label>Caption</label>
        <textarea name="caption"><?= htmlspecialchars($p['caption'] ?? '') ?></textarea>
        <label>Hashtags</label>
        <textarea name="hashtags" style="min-height:70px;"><?= htmlspecialchars($p['hashtags'] ?? '') ?></textarea>
        <label>Scheduled time</label>
        <input type="datetime-local" name="scheduled_at"
               value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($p['scheduled_at'] ?? 'now'))) ?>">
        <div class="row">
          <button class="approve" name="action" value="approve">✓ Approve & schedule</button>
          <button class="save"    name="action" value="save">Save edits</button>
          <button class="reject"  name="action" value="reject">✕ Reject</button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>
</div>
</body>
</html>
