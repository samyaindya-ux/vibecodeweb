<?php
/**
 * VibeCodeWeb Auto-Deployer (v2)
 *
 * Receives files from the GitHub Actions workflow and writes them into the
 * document root. Setup: copy to /public_html/deploy.php via cPanel File Manager,
 * alongside a deploy_secret.php that is NEVER committed.
 *
 * Changes from v1, and why:
 *   - the shared secret moved out of this file. v1 hardcoded it while the repo
 *     was public, so anyone could forge a valid signature and overwrite live
 *     files. The secret now lives in deploy_secret.php, which is gitignored.
 *   - paths may name a subdirectory, so an app can deploy into its own folder
 *     instead of everything landing flat in the document root.
 *   - requests carry a timestamp and are rejected once stale, so a captured
 *     request cannot be replayed indefinitely.
 *   - writes are atomic, so a truncated upload never replaces a good file.
 */

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

const MAX_BYTES    = 8 * 1024 * 1024;  // per file
const MAX_SKEW_SEC = 600;              // reject requests older than 10 minutes

/**
 * Exactly which paths may be written, relative to this directory.
 *
 * Deliberately excluded:
 *   deploy.php, deploy_secret.php - the deployer must not be able to rewrite
 *     itself or its own credentials.
 *   farefinder/update-fares.php   - holds the fare API token. It is uploaded
 *     once by hand; a deploy would overwrite the token with a placeholder.
 *   farefinder/data.json          - generated on the server by the cron job.
 *     Deploying it would replace live fares with whatever is committed.
 */
const ALLOWED = [
    'index.html',
    'index2.html',
    'robots.txt',
    '.gitignore',
    'tools.php',
    'farefinder/index.html',
    'farefinder/places.json',
];

function bail(int $code, string $msg): never {
    http_response_code($code);
    echo date('[Y-m-d H:i:s] ') . $msg . "\n";
    exit;
}

// ---------------------------------------------------------------- secret
$secretFile = __DIR__ . '/deploy_secret.php';
if (!is_readable($secretFile)) {
    bail(500, 'FAIL: deploy_secret.php is missing. Copy deploy_secret.sample.php '
            . 'to deploy_secret.php and set a secret.');
}
$secret = require $secretFile;
if (!is_string($secret) || strlen($secret) < 24 || $secret === 'CHANGE_ME') {
    bail(500, 'FAIL: deploy secret is unset or too short (needs 24+ random chars).');
}

// ---------------------------------------------------------------- verify
$payload = (string)file_get_contents('php://input');
if ($payload === '' || strlen($payload) > MAX_BYTES * 2) {
    bail(400, 'FAIL: empty or oversized payload.');
}
$sig = (string)($_SERVER['HTTP_X_DEPLOY_SIG'] ?? '');
if (!hash_equals(hash_hmac('sha256', $payload, $secret), $sig)) {
    bail(403, 'FAIL: bad signature.');
}

$data = json_decode($payload, true);
if (!is_array($data)) {
    bail(400, 'FAIL: payload is not JSON.');
}

// Signed timestamp: a captured request stops working once it goes stale.
$ts = (int)($data['ts'] ?? 0);
if ($ts <= 0 || abs(time() - $ts) > MAX_SKEW_SEC) {
    bail(400, 'FAIL: missing or stale timestamp (server clock ' . time() . ').');
}

// ---------------------------------------------------------------- path
$rel = (string)($data['file'] ?? '');
// Reject anything that could escape the document root before consulting the
// allowlist, so a traversal attempt never reaches the filesystem.
if ($rel === ''
    || str_contains($rel, "\0")
    || str_contains($rel, '..')
    || str_contains($rel, '\\')
    || str_starts_with($rel, '/')
    || !in_array($rel, ALLOWED, true)) {
    bail(400, "FAIL: '$rel' is not a deployable path.");
}

$target = __DIR__ . '/' . $rel;
$dir    = dirname($target);
$root   = realpath(__DIR__);
if ($root === false) {
    bail(500, 'FAIL: cannot resolve document root.');
}
if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
    bail(500, "FAIL: cannot create directory for '$rel'.");
}
// Belt and braces: confirm the resolved directory really is inside the root.
$realDir = realpath($dir);
if ($realDir === false || !str_starts_with($realDir . DIRECTORY_SEPARATOR, $root . DIRECTORY_SEPARATOR)) {
    bail(400, "FAIL: '$rel' resolves outside the document root.");
}

// ---------------------------------------------------------------- write
$content = base64_decode((string)($data['content'] ?? ''), true);
if ($content === false) {
    bail(400, 'FAIL: content is not valid base64.');
}
if (strlen($content) > MAX_BYTES) {
    bail(413, 'FAIL: file exceeds ' . MAX_BYTES . ' bytes.');
}

// Write to a temp file and move it into place, so a failed upload never leaves
// a truncated file being served.
$tmp = $target . '.tmp';
if (file_put_contents($tmp, $content) === false) {
    bail(500, "FAIL: cannot write '$rel' (check permissions).");
}
if (!rename($tmp, $target)) {
    @unlink($tmp);
    bail(500, "FAIL: cannot move '$rel' into place.");
}

echo date('[Y-m-d H:i:s] ') . 'OK: ' . $rel . ' (' . strlen($content) . " bytes)\n";
