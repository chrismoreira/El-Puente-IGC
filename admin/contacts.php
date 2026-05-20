<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

auth_check();
csrf_verify();

$id     = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

// Update status / notes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    db_run('UPDATE contacts SET status=?, notes=? WHERE id=?', [
        $_POST['status'] ?? 'nuevo',
        trim($_POST['notes'] ?? ''),
        $id,
    ]);
    flash('Contacto actualizado.');
    redirect(ADMIN_URL . '/contacts.php');
}

// CSV Export
if ($action === 'export') {
    $search = trim($_GET['q'] ?? '');
    $status = $_GET['status'] ?? '';
    $where  = '1=1';
    $params = [];
    if ($search) { $where .= ' AND (name LIKE ? OR whatsapp LIKE ? OR email LIKE ?)'; $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]); }
    if ($status) { $where .= ' AND status = ?'; $params[] = $status; }
    $rows = db_all("SELECT name, whatsapp, email, status, notes, created_at FROM contacts WHERE $where ORDER BY created_at DESC", $params);

    $filename = 'contactos-el-puente-' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    echo "\xEF\xBB\xBF"; // BOM para Excel
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Nombre', 'WhatsApp', 'Email', 'Estado', 'Notas', 'Fecha']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['name'], $r['whatsapp'], $r['email'],
            $r['status'], $r['notes'],
            date('d/m/Y H:i', strtotime($r['created_at']))
        ]);
    }
    fclose($out);
    exit;
}

// Delete
if ($action === 'delete' && $id) {
    db_run('DELETE FROM contacts WHERE id = ?', [$id]);
    flash('Contacto eliminado.');
    redirect(ADMIN_URL . '/contacts.php');
}

// Single contact view
if ($id) {
    $contact = db_get('SELECT * FROM contacts WHERE id = ?', [$id]);
    if (!$contact) redirect(ADMIN_URL . '/contacts.php');
    layout_head('Contacto: ' . $contact['name']);
?>
<div class="page-body">
  <div class="card" style="max-width:520px">
    <div class="card-header">Detalle del contacto</div>
    <div class="card-body">
      <p><strong>Nombre:</strong> <?= e($contact['name']) ?></p>
      <p style="margin-top:8px"><strong>WhatsApp:</strong>
        <?php if ($contact['whatsapp']): ?>
          <a href="https://wa.me/<?= preg_replace('/\D/', '', $contact['whatsapp']) ?>" target="_blank"><?= e($contact['whatsapp']) ?></a>
        <?php else: echo '—'; endif; ?>
      </p>
      <p style="margin-top:8px"><strong>Email:</strong> <?= $contact['email'] ? e($contact['email']) : '—' ?></p>
      <p style="margin-top:8px"><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($contact['created_at'])) ?></p>

      <form method="post" style="margin-top:20px">
        <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
        <div class="form-group">
          <label>Estado</label>
          <select name="status">
            <?php foreach (['nuevo', 'contactado', 'seguimiento', 'completado'] as $s): ?>
            <option value="<?= $s ?>" <?= $contact['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Notas</label>
          <textarea name="notes" rows="4"><?= e($contact['notes'] ?? '') ?></textarea>
        </div>
        <div style="display:flex;gap:10px">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <a href="contacts.php" class="btn btn-ghost">Volver</a>
          <a href="?action=delete&id=<?= $id ?>" class="btn btn-danger"
             onclick="return confirm('¿Eliminar este contacto?')">Eliminar</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php
    layout_foot();
    exit;
}

// List
$search = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));

$where  = '1=1';
$params = [];
if ($search) { $where .= ' AND (name LIKE ? OR whatsapp LIKE ? OR email LIKE ?)'; $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]); }
if ($status) { $where .= ' AND status = ?'; $params[] = $status; }

$total  = db_get("SELECT COUNT(*) c FROM contacts WHERE $where", $params)['c'];
$pag    = paginate($total, $page);
$items  = db_all("SELECT * FROM contacts WHERE $where ORDER BY created_at DESC LIMIT {$pag['per_page']} OFFSET {$pag['offset']}", $params);

layout_head('Contactos');
?>
<div class="page-body">
  <form method="get" style="display:flex;gap:10px;margin-bottom:20px">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Buscar por nombre, WhatsApp..." style="max-width:280px;padding:9px 12px;border:1px solid #ddd;border-radius:6px;font-size:14px">
    <select name="status" style="padding:9px 12px;border:1px solid #ddd;border-radius:6px;font-size:14px">
      <option value="">Todos los estados</option>
      <?php foreach (['nuevo', 'contactado', 'seguimiento', 'completado'] as $s): ?>
      <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-teal">Filtrar</button>
    <?php if ($search || $status): ?><a href="contacts.php" class="btn btn-ghost">Limpiar</a><?php endif; ?>
    <a href="?action=export&q=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>" class="btn btn-ghost">Exportar CSV</a>
  </form>

  <div class="card">
    <div class="card-header">
      <?= $total ?> contacto<?= $total !== 1 ? 's' : '' ?>
    </div>
    <div class="card-body" style="padding:0">
      <div class="table-wrap">
        <table>
          <thead><tr><th>Nombre</th><th>WhatsApp</th><th>Email</th><th>Estado</th><th>Fecha</th><th></th></tr></thead>
          <tbody>
          <?php if (empty($items)): ?>
            <tr><td colspan="6" style="text-align:center;color:#888;padding:24px">Sin resultados</td></tr>
          <?php else: foreach ($items as $c): ?>
          <tr>
            <td><?= e($c['name']) ?></td>
            <td>
              <?php if ($c['whatsapp']): ?>
              <a href="https://wa.me/<?= preg_replace('/\D/', '', $c['whatsapp']) ?>" target="_blank"><?= e($c['whatsapp']) ?></a>
              <?php else: echo '—'; endif; ?>
            </td>
            <td><?= $c['email'] ? e($c['email']) : '—' ?></td>
            <td><span class="badge badge-<?= e($c['status']) ?>"><?= e($c['status']) ?></span></td>
            <td><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></td>
            <td><a href="?id=<?= $c['id'] ?>" class="btn btn-ghost btn-sm">Ver</a></td>
          </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php if ($pag['pages'] > 1): ?>
  <div class="pagination">
    <?php for ($p = 1; $p <= $pag['pages']; $p++): ?>
      <?php $params_page = array_filter(['q' => $search, 'status' => $status, 'page' => $p]); ?>
      <a href="?<?= http_build_query($params_page) ?>" class="<?= $p === $pag['page'] ? 'current' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php layout_foot(); ?>
