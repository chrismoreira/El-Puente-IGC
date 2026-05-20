<?php
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function setting(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        $rows  = db_all('SELECT `key`, value FROM settings');
        $cache = array_column($rows, 'value', 'key');
    }
    return $cache[$key] ?? $default;
}

function settings_all(): array {
    return db_all('SELECT * FROM settings ORDER BY `group`, label');
}

function setting_save(string $key, string $value, string $user_name = ''): void {
    $current = db_get('SELECT value, label FROM settings WHERE `key` = ?', [$key]);
    if (!$current || $current['value'] === $value) return;
    db_run('UPDATE settings SET value = ? WHERE `key` = ?', [$value, $key]);
    db_run('INSERT INTO settings_log (user_name, setting_key, setting_label, old_value, new_value) VALUES (?,?,?,?,?)',
        [$user_name, $key, $current['label'] ?? $key, $current['value'], $value]);
}

function encrypt(string $plain): string {
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($plain, 'AES-256-CBC', ENCRYPT_KEY, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decrypt(string $enc): string {
    $data = base64_decode($enc);
    $iv   = substr($data, 0, 16);
    return openssl_decrypt(substr($data, 16), 'AES-256-CBC', ENCRYPT_KEY, 0, $iv) ?: '';
}

function flash(string $msg, string $type = 'success'): void {
    session_start_safe();
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function flash_get(): array {
    session_start_safe();
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function paginate(int $total, int $page, int $per_page = 20): array {
    $pages = max(1, (int) ceil($total / $per_page));
    $page  = max(1, min($page, $pages));
    return ['total' => $total, 'page' => $page, 'pages' => $pages, 'offset' => ($page - 1) * $per_page, 'per_page' => $per_page];
}
