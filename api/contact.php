<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok' => false, 'error' => 'Method not allowed']); exit; }

require_once __DIR__ . '/../admin/includes/config.php';
require_once __DIR__ . '/../admin/includes/db.php';
require_once __DIR__ . '/../admin/includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$name     = trim($input['name'] ?? '');
$whatsapp = trim($input['whatsapp'] ?? '');
$email    = trim($input['email'] ?? '');

if (!$name) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'El nombre es obligatorio.']);
    exit;
}

// reCAPTCHA validation
$form_cfg = db_get('SELECT * FROM form_settings LIMIT 1');
if ($form_cfg && $form_cfg['recaptcha_enabled'] && $form_cfg['recaptcha_secret_key']) {
    $token = $input['recaptcha_token'] ?? '';
    $resp  = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' .
        urlencode($form_cfg['recaptcha_secret_key']) . '&response=' . urlencode($token));
    $rc = json_decode($resp, true);
    if (!($rc['success'] ?? false)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Verificación reCAPTCHA fallida.']);
        exit;
    }
}

// Save contact
$new_id = db_run('INSERT INTO contacts (name, whatsapp, email) VALUES (?,?,?)', [$name, $whatsapp, $email]);

// Notify by email
if ($form_cfg && $form_cfg['notify_email']) {
    require_once __DIR__ . '/../admin/includes/mailer.php';
    $subject = '¡Nueva oración de salvación! — El Puente';
    $body    = "<h2>Nuevo contacto en El Puente</h2>
                <p><strong>Nombre:</strong> {$name}</p>
                <p><strong>WhatsApp:</strong> " . ($whatsapp ?: '—') . "</p>
                <p><strong>Email:</strong> " . ($email ?: '—') . "</p>
                <p><a href='" . ADMIN_URL . "/contacts.php?id={$new_id}'>Ver en el panel</a></p>";
    mailer_send($form_cfg['notify_email'], 'El Puente Admin', $subject, $body);
}

echo json_encode(['ok' => true, 'whatsapp' => $form_cfg['notify_whatsapp'] ?? '']);
