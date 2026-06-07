<?php
/**
 * User management — list, add, and remove login accounts.
 * Any logged-in user can manage accounts (flat, no roles).
 */

require __DIR__ . '/auth.php';
if (empty($_SESSION['uid'])) { header('Location: login.php'); exit; }
require __DIR__ . '/lib/db.php';

$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    exit('Missing config.php — fill in credentials (including db_*) first.');
}
$config = require $configPath;
$db = vcw_db($config);

$notice = '';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    vcw_csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $username = trim($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        if ($username === '' || $password === '') {
            $error = 'Username and password are both required.';
        } elseif (mb_strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif (vcw_find_user($db, $username)) {
            $error = "User '{$username}' already exists.";
        } else {
            vcw_create_user($db, $username, $password);
            $notice = "User '{$username}' added.";
        }
    } elseif ($action === 'delete') {
        $delId = (int) ($_POST['id'] ?? 0);
        if (vcw_user_count($db) <= 1) {
            $error = 'Cannot remove the last remaining user.';
        } elseif ($delId === (int) $_SESSION['uid']) {
            $error = 'You cannot remove the account you are logged in as.';
        } else {
            vcw_delete_user($db, $delId);
            $notice = 'User removed.';
        }
    }
}

$users = vcw_list_users($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VibeCodeWeb · Users</title>
<style>
  :root { --bg:#020617; --card:#1e293b; --neon:#10b981; --primary:#3b82f6; --muted:#94a3b8; }
  * { box-sizing:border-box; }
  body { margin:0; background:var(--bg); color:#f8fafc; font-family:'Segoe UI',system-ui,sans-serif; }
  header { padding:24px 32px; border-bottom:1px solid rgba(255,255,255,.08);
    background:linear-gradient(90deg,rgba(249,115,22,.12),rgba(59,130,246,.12),rgba(16,185,129,.12));
    display:flex; align-items:center; gap:16px; flex-wrap:wrap; }
  h1 { margin:0; font-size:22px; flex:1; }
  .navlink { color:var(--muted); text-decoration:none; font-size:13px;
    border:1px solid rgba(255,255,255,.12); border-radius:999px; padding:6px 14px; }
  .navlink:hover { color:#f8fafc; }
  .wrap { max-width:720px; margin:0 auto; padding:32px; }
  .panel { background:var(--card); border:1px solid rgba(255,255,255,.08); border-radius:16px;
    padding:22px; margin-bottom:24px; }
  .panel h2 { margin:0 0 16px; font-size:16px; }
  label { display:block; font-size:12px; color:var(--muted); text-transform:uppercase;
    letter-spacing:.06em; margin-bottom:6px; }
  input { width:100%; background:#0f172a; color:#f8fafc; border:1px solid rgba(255,255,255,.12);
    border-radius:10px; padding:11px 14px; font:inherit; font-size:14px; margin-bottom:16px; }
  input:focus { outline:none; border-color:var(--neon); }
  button { border:none; border-radius:999px; padding:11px 22px; font-weight:700; cursor:pointer; font-size:14px; }
  .add { background:var(--neon); color:#022; }
  .del { background:transparent; color:#fca5a5; border:1px solid #fca5a5; padding:7px 16px; font-size:13px; }
  table { width:100%; border-collapse:collapse; }
  td, th { text-align:left; padding:12px 8px; border-bottom:1px solid rgba(255,255,255,.06); font-size:14px; }
  th { color:var(--muted); font-size:12px; text-transform:uppercase; letter-spacing:.05em; }
  .you { color:var(--neon); font-size:11px; margin-left:8px; }
  .notice { background:rgba(16,185,129,.12); border:1px solid rgba(16,185,129,.3); color:#6ee7b7;
    border-radius:10px; padding:11px 14px; font-size:13px; margin-bottom:20px; }
  .error  { background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.3); color:#fca5a5;
    border-radius:10px; padding:11px 14px; font-size:13px; margin-bottom:20px; }
</style>
</head>
<body>
<header>
  <h1>👥 Users</h1>
  <a class="navlink" href="review.php">← Review</a>
  <a class="navlink" href="logout.php">Log out (<?= htmlspecialchars($_SESSION['uname'] ?? '') ?>)</a>
</header>
<div class="wrap">

  <?php if ($notice): ?><div class="notice">✓ <?= htmlspecialchars($notice) ?></div><?php endif; ?>
  <?php if ($error):  ?><div class="error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>

  <div class="panel">
    <h2>Add a user</h2>
    <form method="post">
      <?= vcw_csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <label>Username</label>
      <input type="text" name="username" autocomplete="off">
      <label>Password (min 6 characters)</label>
      <input type="password" name="password" autocomplete="new-password">
      <button class="add" type="submit">+ Add user</button>
    </form>
  </div>

  <div class="panel">
    <h2>Existing users</h2>
    <table>
      <tr><th>Username</th><th>Created</th><th></th></tr>
      <?php foreach ($users as $u): ?>
        <tr>
          <td>
            <?= htmlspecialchars($u['username']) ?>
            <?php if ((int)$u['id'] === (int)$_SESSION['uid']): ?><span class="you">you</span><?php endif; ?>
          </td>
          <td style="color:var(--muted)"><?= htmlspecialchars(substr((string)($u['created_at'] ?? ''), 0, 10)) ?></td>
          <td style="text-align:right">
            <?php if ((int)$u['id'] !== (int)$_SESSION['uid']): ?>
              <form method="post" onsubmit="return confirm('Remove <?= htmlspecialchars($u['username']) ?>?');" style="margin:0">
                <?= vcw_csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button class="del" type="submit">Remove</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>

</div>
</body>
</html>
