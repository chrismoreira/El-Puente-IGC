<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

auth_check();

$stats = [
    'contacts_total'    => db_get('SELECT COUNT(*) c FROM contacts')['c'],
    'contacts_new'      => db_get("SELECT COUNT(*) c FROM contacts WHERE status='nuevo'")['c'],
    'contacts_today'    => db_get("SELECT COUNT(*) c FROM contacts WHERE DATE(created_at)=CURDATE()")['c'],
    'contacts_week'     => db_get("SELECT COUNT(*) c FROM contacts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['c'],
];

$recent = db_all('SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5');

layout_head('Dashboard');
?>
<div class="page-body">
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-num"><?= $stats['contacts_total'] ?></div>
      <div class="stat-label">Total contactos</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= $stats['contacts_new'] ?></div>
      <div class="stat-label">Sin contactar</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= $stats['contacts_today'] ?></div>
      <div class="stat-label">Hoy</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= $stats['contacts_week'] ?></div>
      <div class="stat-label">Esta semana</div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">Contactos recientes</div>
    <div class="card-body" style="padding:0">
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Nombre</th><th>WhatsApp</th><th>Estado</th><th>Fecha</th><th></th></tr>
          </thead>
          <tbody>
          <?php if (empty($recent)): ?>
            <tr><td colspan="5" style="text-align:center;color:#888;padding:24px">Sin contactos aún</td></tr>
          <?php else: foreach ($recent as $c): ?>
            <tr>
              <td><?= e($c['name']) ?></td>
              <td><?= e($c['whatsapp'] ?? '—') ?></td>
              <td><span class="badge badge-<?= e($c['status']) ?>"><?= e($c['status']) ?></span></td>
              <td><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></td>
              <td><a href="contacts.php?id=<?= $c['id'] ?>" class="btn btn-ghost btn-sm">Ver</a></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <a href="contacts.php" class="btn btn-teal">Ver todos los contactos</a>
</div>
<?php layout_foot(); ?>
