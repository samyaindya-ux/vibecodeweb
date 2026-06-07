<?php
/**
 * Session bootstrap + CSRF helpers.
 *
 * Include this at the very top of any page that needs a session. It does NOT
 * redirect — login.php includes it too. Pages that require a logged-in user add
 * the guard line themselves right after the require:
 *
 *   require __DIR__ . '/auth.php';
 *   if (empty($_SESSION['uid'])) { header('Location: login.php'); exit; }
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

/** Current CSRF token (generated once per session). */
function vcw_csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

/** Hidden input markup for forms. */
function vcw_csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(vcw_csrf_token()) . '">';
}

/** Abort with 403 if a POST request carries a missing/incorrect CSRF token. */
function vcw_csrf_check(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('CSRF check failed. Reload the page and try again.');
    }
}
