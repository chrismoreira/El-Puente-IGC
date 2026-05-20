<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function session_start_safe(): void {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_strict_mode', 1);
        session_start();
    }
}

function auth_check(): void {
    session_start_safe();
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
    // Session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_destroy();
        header('Location: ' . ADMIN_URL . '/login.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

function auth_user(): array {
    return $_SESSION['user'] ?? [];
}

function auth_login(string $email, string $password): bool {
    $user = db_get('SELECT * FROM users WHERE email = ? AND active = 1', [$email]);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }
    session_start_safe();
    session_regenerate_id(true);
    $_SESSION['user_id']      = $user['id'];
    $_SESSION['user']         = ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']];
    $_SESSION['last_activity'] = time();
    db_run('UPDATE users SET last_login = NOW() WHERE id = ?', [$user['id']]);
    return true;
}

function auth_logout(): void {
    session_start_safe();
    session_destroy();
    header('Location: ' . ADMIN_URL . '/login.php');
    exit;
}

function csrf_token(): string {
    session_start_safe();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['_csrf'] ?? '';
        if (!hash_equals(csrf_token(), $token)) {
            http_response_code(403);
            die('Token CSRF inválido.');
        }
    }
}

function is_superadmin(): bool {
    return (auth_user()['role'] ?? '') === 'superadmin';
}
