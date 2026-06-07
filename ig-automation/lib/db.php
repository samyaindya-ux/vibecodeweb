<?php
/**
 * PDO connection helper + tiny user-account helpers.
 *
 * Reads DB credentials from the config array (config.php):
 *   db_host, db_name, db_user, db_pass
 *
 * The connection is cached statically so repeated vcw_db() calls in one
 * request/CLI run reuse a single PDO handle.
 */

function vcw_db(array $config): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = $config['db_host'] ?? 'localhost';
    $name = $config['db_name'] ?? '';
    $user = $config['db_user'] ?? '';
    $pass = $config['db_pass'] ?? '';

    if ($name === '' || $user === '') {
        throw new RuntimeException(
            'Database not configured. Add db_host, db_name, db_user, db_pass to config.php.'
        );
    }

    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

// ---- User account helpers ---------------------------------------------------

function vcw_find_user(PDO $db, string $username): ?array
{
    $st = $db->prepare('SELECT * FROM users WHERE username = ?');
    $st->execute([$username]);
    $row = $st->fetch();
    return $row ?: null;
}

function vcw_list_users(PDO $db): array
{
    return $db->query('SELECT id, username, created_at FROM users ORDER BY username')->fetchAll();
}

function vcw_create_user(PDO $db, string $username, string $password): void
{
    $st = $db->prepare('INSERT INTO users (username, password_hash, created_at) VALUES (?, ?, ?)');
    $st->execute([$username, password_hash($password, PASSWORD_BCRYPT), date('c')]);
}

function vcw_delete_user(PDO $db, int $id): void
{
    $st = $db->prepare('DELETE FROM users WHERE id = ?');
    $st->execute([$id]);
}

function vcw_user_count(PDO $db): int
{
    return (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
}
