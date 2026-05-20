<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

auth_check();
csrf_verify();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $existing = db_get('SELECT id FROM form_settings LIMIT 1');
    $data = [
        (int)isset($_POST['recaptcha_enabled']),
        trim($_POST['recaptcha_site_key'] ?? ''),
        trim($_POST['recaptcha_secret_key'] ?? ''),
        trim($_POST['notify_email'] ?? ''),
        trim($_POST['notify_whatsapp'] ?? ''),
    ];
    if ($existing) {
        db_run('UPDATE form_settings SET recaptcha_enabled=?, recaptcha_site_key=?, recaptcha_secret_key=?, notify_email=?, notify_whatsapp=? WHERE id=?',
            array_merge($data, [$existing['id']]));
    } else {
        db_run('INSERT INTO form_settings (recaptcha_enabled, recaptcha_site_key, recaptcha_secret_key, notify_email, notify_whatsapp) VALUES (?,?,?,?,?)', $data);
    }
    flash('Configuración de formularios guardada.');
    redirect(ADMIN_URL . '/forms.php');
}

$cfg = db_get('SELECT * FROM form_settings LIMIT 1') ?: [];

layout_head('Formularios y reCAPTCHA');
?>
<div class="page-body">
  <form method="post">
    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

    <div class="card">
      <div class="card-header">Notificaciones</div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group">
            <label>Email de notificación</label>
            <input type="email" name="notify_email" value="<?= e($cfg['notify_email'] ?? '') ?>" placeholder="pastor@iglesia.com">
            <p class="hint">Recibirá un email cada vez que alguien llene el formulario de contacto.</p>
          </div>
          <div class="form-group">
            <label>WhatsApp de contacto (número de la iglesia)</label>
            <input type="text" name="notify_whatsapp" value="<?= e($cfg['notify_whatsapp'] ?? '') ?>" placeholder="+50312345678">
            <p class="hint">Incluir código de país. Se usará en el botón "Hablar con nosotros".</p>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">reCAPTCHA v2</div>
      <div class="card-body">
        <div class="form-group">
          <label>
            <input type="checkbox" name="recaptcha_enabled" value="1" <?= !empty($cfg['recaptcha_enabled']) ? 'checked' : '' ?>>
            Activar reCAPTCHA en el formulario de contacto
          </label>
          <p class="hint">Requiere crear un sitio en <a href="https://www.google.com/recaptcha/admin" target="_blank">google.com/recaptcha</a> con el dominio <strong>puente.igcsansalvador.com</strong></p>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Site Key (pública)</label>
            <input type="text" name="recaptcha_site_key" value="<?= e($cfg['recaptcha_site_key'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Secret Key (privada)</label>
            <input type="password" name="recaptcha_secret_key" placeholder="<?= !empty($cfg['recaptcha_secret_key']) ? '••••••••' : '' ?>">
          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
  </form>
</div>
<?php layout_foot(); ?>
