<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

auth_check();
csrf_verify();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = auth_user()['name'] ?? 'Admin';
    foreach ($_POST as $key => $value) {
        if ($key === '_csrf' || substr($key, -5) === '_text') continue;
        setting_save($key, trim($value), $user_name);
    }
    flash('Configuración guardada correctamente.');
    redirect(ADMIN_URL . '/settings.php');
}

$settings = [];
foreach (settings_all() as $s) {
    $settings[$s['key']] = $s;
}

$fonts = ['Poppins', 'Anton', 'Crimson Pro', 'Roboto', 'Open Sans', 'Lato', 'Montserrat'];
$log   = db_all('SELECT * FROM settings_log WHERE changed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY changed_at DESC LIMIT 50');

layout_head('Apariencia');
?>
<div class="page-body">
  <form method="post">
    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

    <div class="card">
      <div class="card-header">Colores</div>
      <div class="card-body">
        <div class="form-row">
          <?php foreach ($settings as $key => $s):
            if ($s['group'] !== 'colors') continue; ?>
          <div class="form-group">
            <label><?= e($s['label']) ?></label>
            <div style="display:flex;align-items:center;gap:10px">
              <input type="color" name="<?= e($key) ?>" value="<?= e($s['value']) ?>">
              <input type="text" name="<?= e($key) ?>_text" value="<?= e($s['value']) ?>"
                     style="width:110px"
                     oninput="this.previousElementSibling.value=this.value"
                     pattern="^#[0-9a-fA-F]{6}$">
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <p class="hint" style="margin-top:8px">Los cambios se aplican en la app después de refrescar.</p>
      </div>
    </div>

    <div class="card">
      <div class="card-header">Fuentes</div>
      <div class="card-body">
        <div class="form-row">
          <?php foreach ($settings as $key => $s):
            if ($s['group'] !== 'fonts') continue; ?>
          <div class="form-group">
            <label><?= e($s['label']) ?></label>
            <select name="<?= e($key) ?>">
              <?php foreach ($fonts as $f): ?>
              <option value="<?= e($f) ?>" <?= $s['value'] === $f ? 'selected' : '' ?>><?= e($f) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar cambios</button>
  </form>

  <?php if (!empty($log)): ?>
  <div class="card" style="margin-top:28px">
    <div class="card-header">Historial de cambios — últimos 30 días</div>
    <div class="card-body" style="padding:0">
      <div class="table-wrap">
        <table>
          <thead><tr><th>Fecha</th><th>Usuario</th><th>Configuración</th><th>Antes</th><th>Después</th></tr></thead>
          <tbody>
          <?php foreach ($log as $l): ?>
          <tr>
            <td><?= date('d/m/Y H:i', strtotime($l['changed_at'])) ?></td>
            <td><?= e($l['user_name']) ?></td>
            <td><?= e($l['setting_label']) ?></td>
            <td>
              <?php if (strpos($l['old_value'], '#') === 0): ?>
              <span class="color-preview" style="background:<?= e($l['old_value']) ?>"></span>
              <?php endif; ?>
              <?= e($l['old_value']) ?>
            </td>
            <td>
              <?php if (strpos($l['new_value'], '#') === 0): ?>
              <span class="color-preview" style="background:<?= e($l['new_value']) ?>"></span>
              <?php endif; ?>
              <?= e($l['new_value']) ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
document.querySelectorAll('input[type="color"]').forEach(picker => {
  const text = picker.nextElementSibling;
  picker.addEventListener('input', () => { text.value = picker.value; });
});
</script>
<?php layout_foot(); ?>
