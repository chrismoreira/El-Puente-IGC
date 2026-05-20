<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

auth_check();
csrf_verify();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';

    if ($action === 'test') {
        require_once __DIR__ . '/includes/mailer.php';
        $user = auth_user();
        $ok = mailer_send($user['email'], $user['name'], 'Prueba de email — El Puente', '<p>Si recibes este mensaje, el email está configurado correctamente.</p>');
        flash($ok ? 'Email de prueba enviado a ' . $user['email'] : 'Error al enviar. Revisa la configuración.', $ok ? 'success' : 'error');
        redirect(ADMIN_URL . '/email.php');
    }

    $provider  = $_POST['provider'] ?? 'smtp';
    $smtp_pass = $_POST['smtp_pass'] ?? '';
    $api_key   = $_POST['api_key'] ?? '';

    // Only re-encrypt if new value provided
    $existing = db_get('SELECT * FROM email_settings LIMIT 1');
    if ($smtp_pass === '' && $existing) $smtp_pass_enc = $existing['smtp_pass'];
    else $smtp_pass_enc = $smtp_pass ? encrypt($smtp_pass) : '';
    if ($api_key === '' && $existing) $api_key_enc = $existing['api_key'];
    else $api_key_enc = $api_key ? encrypt($api_key) : '';

    if ($existing) {
        db_run('UPDATE email_settings SET provider=?, smtp_host=?, smtp_port=?, smtp_user=?, smtp_pass=?, api_key=?, from_name=?, from_email=? WHERE id=?', [
            $provider, $_POST['smtp_host'] ?? '', (int)($_POST['smtp_port'] ?? 587),
            $_POST['smtp_user'] ?? '', $smtp_pass_enc, $api_key_enc,
            $_POST['from_name'] ?? '', $_POST['from_email'] ?? '', $existing['id']
        ]);
    } else {
        db_run('INSERT INTO email_settings (provider, smtp_host, smtp_port, smtp_user, smtp_pass, api_key, from_name, from_email) VALUES (?,?,?,?,?,?,?,?)', [
            $provider, $_POST['smtp_host'] ?? '', (int)($_POST['smtp_port'] ?? 587),
            $_POST['smtp_user'] ?? '', $smtp_pass_enc, $api_key_enc,
            $_POST['from_name'] ?? '', $_POST['from_email'] ?? ''
        ]);
    }
    flash('Configuración de email guardada.');
    redirect(ADMIN_URL . '/email.php');
}

$cfg = db_get('SELECT * FROM email_settings LIMIT 1') ?: [];

layout_head('Configuración de Email');
?>
<div class="page-body">
  <form method="post">
    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

    <div class="card">
      <div class="card-header">Proveedor de email</div>
      <div class="card-body">
        <div class="form-group">
          <label>Proveedor</label>
          <select name="provider" id="provider-select" onchange="toggleProvider()">
            <?php foreach (['smtp' => 'SMTP propio', 'mailgun' => 'MailGun', 'sendgrid' => 'SendGrid', 'resend' => 'Resend'] as $v => $l): ?>
            <option value="<?= $v ?>" <?= ($cfg['provider'] ?? 'smtp') === $v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Nombre remitente</label>
            <input type="text" name="from_name" value="<?= e($cfg['from_name'] ?? 'El Puente IGC') ?>">
          </div>
          <div class="form-group">
            <label>Email remitente</label>
            <input type="email" name="from_email" value="<?= e($cfg['from_email'] ?? '') ?>">
          </div>
        </div>
      </div>
    </div>

    <div class="card" id="smtp-fields">
      <div class="card-header">Configuración SMTP</div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group">
            <label>Host SMTP</label>
            <input type="text" name="smtp_host" value="<?= e($cfg['smtp_host'] ?? '') ?>" placeholder="smtp.ejemplo.com">
          </div>
          <div class="form-group">
            <label>Puerto</label>
            <input type="number" name="smtp_port" value="<?= e($cfg['smtp_port'] ?? 587) ?>">
            <p class="hint">587 = TLS · 465 = SSL</p>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Usuario SMTP</label>
            <input type="text" name="smtp_user" value="<?= e($cfg['smtp_user'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Contraseña SMTP</label>
            <input type="password" name="smtp_pass" placeholder="Dejar vacío para no cambiar">
          </div>
        </div>
      </div>
    </div>

    <div class="card" id="api-fields" style="display:none">
      <div class="card-header">API Key</div>
      <div class="card-body">
        <div class="form-group">
          <label>API Key</label>
          <input type="password" name="api_key" placeholder="Dejar vacío para no cambiar">
        </div>
      </div>
    </div>

    <div style="display:flex;gap:10px">
      <button type="submit" name="action" value="save" class="btn btn-primary">Guardar</button>
      <button type="submit" name="action" value="test" class="btn btn-ghost">Enviar email de prueba</button>
    </div>
  </form>
</div>

<script>
function toggleProvider() {
  const p = document.getElementById('provider-select').value;
  const smtp = document.getElementById('smtp-fields');
  const api  = document.getElementById('api-fields');
  if (p === 'smtp') { smtp.style.display = ''; api.style.display = 'none'; }
  else              { smtp.style.display = 'none'; api.style.display = ''; }
}
toggleProvider();
</script>
<?php layout_foot(); ?>
