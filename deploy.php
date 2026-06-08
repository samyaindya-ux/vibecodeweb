<?php
/**
 * VibeCodeWeb Auto-Deployer
 * Setup: copy to /public_html/deploy.php via cPanel File Manager (one-time)
 */
define('DEPLOY_SECRET', 'vibe_deploy_2026');

$payload = file_get_contents('php://input');
$sig     = $_SERVER['HTTP_X_DEPLOY_SIG'] ?? '';

if (!hash_equals(hash_hmac('sha256', $payload, DEPLOY_SECRET), $sig)) {
    http_response_code(403); die('Unauthorized');
}

$data    = json_decode($payload, true);
$file    = basename($data['file'] ?? '');
$content = base64_decode($data['content'] ?? '');

$allowed = ['index.html', 'index2.html', 'robots.txt', '.gitignore'];
if (!$file || !in_array($file, $allowed)) {
    http_response_code(400); die('Invalid file');
}

$bytes = file_put_contents(__DIR__ . '/' . $file, $content);
echo $bytes !== false
    ? date('[Y-m-d H:i:s]') . " OK: $file ({$bytes} bytes)
"
    : date('[Y-m-d H:i:s]') . " FAIL: $file
";
