<?php
/**
 * Login page — authenticates against the users table and starts a session.
 */

require __DIR__ . '/auth.php';
require __DIR__ . '/lib/db.php';

// Already logged in? Go straight to the dashboard.
if (!empty($_SESSION['uid'])) {
    header('Location: review.php');
    exit;
}

$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    exit('Missing config.php — copy config.sample.php and fill in credentials (including db_*).');
}
$config = require $configPath;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    vcw_csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    try {
        $db   = vcw_db($config);
        $user = vcw_find_user($db, $username);
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['uid']   = (int) $user['id'];
            $_SESSION['uname'] = $user['username'];
            header('Location: review.php');
            exit;
        }
        $error = 'Invalid username or password.';
    } catch (Throwable $e) {
        $error = 'Login unavailable: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VibeCodeWeb · Login</title>
<style>
  :root { --bg:#020617; --card:#1e293b; --neon:#10b981; --muted:#94a3b8; }
  * { box-sizing:border-box; }
  body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center;
    background:var(--bg); color:#f8fafc; font-family:'Segoe UI',system-ui,sans-serif;
    background-image:linear-gradient(135deg,rgba(249,115,22,.08),rgba(59,130,246,.08),rgba(16,185,129,.08)); }
  .card { background:var(--card); border:1px solid rgba(255,255,255,.08); border-radius:18px;
    padding:36px 32px; width:340px; box-shadow:0 20px 60px rgba(0,0,0,.4); }
  h1 { margin:0 0 4px; font-size:22px; }
  .sub { color:var(--muted); font-size:13px; margin-bottom:26px; }
  label { display:block; font-size:12px; color:var(--muted); text-transform:uppercase;
    letter-spacing:.06em; margin-bottom:6px; }
  input { width:100%; background:#0f172a; color:#f8fafc; border:1px solid rgba(255,255,255,.12);
    border-radius:10px; padding:11px 14px; font:inherit; font-size:14px; margin-bottom:18px; }
  input:focus { outline:none; border-color:var(--neon); }
  button { width:100%; border:none; border-radius:999px; padding:13px; font-weight:700;
    cursor:pointer; font-size:15px; background:var(--neon); color:#022; }
  button:hover { opacity:.9; }
  .error { background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.3); color:#fca5a5;
    border-radius:10px; padding:11px 14px; font-size:13px; margin-bottom:18px; }
</style>
</head>
<body>
  <form class="card" method="post">
    <h1>📸 VibeCodeWeb</h1>
    <div class="sub">Instagram automation — sign in</div>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?= vcw_csrf_field() ?>
    <label>Username</label>
    <input type="text" name="username" autofocus autocomplete="username"
           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
    <label>Password</label>
    <input type="password" name="password" autocomplete="current-password">
    <button type="submit">Sign in</button>
  </form>
</body>
</html>
