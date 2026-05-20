<?php
function layout_head(string $title): void {
    $flash = flash_get();
    $user  = auth_user();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> — El Puente Admin</title>
<link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/css/admin.css">
</head>
<body>
<nav class="sidebar">
  <div class="sidebar-logo">
    <img src="<?= APP_URL ?>/icon.svg" alt="El Puente" width="36">
    <span>El Puente</span>
  </div>
  <ul>
    <li><a href="<?= ADMIN_URL ?>/index.php">Dashboard</a></li>
    <li><a href="<?= ADMIN_URL ?>/contacts.php">Contactos</a></li>
    <li><a href="<?= ADMIN_URL ?>/settings.php">Apariencia</a></li>
    <li><a href="<?= ADMIN_URL ?>/email.php">Email</a></li>
    <li><a href="<?= ADMIN_URL ?>/forms.php">Formularios</a></li>
    <?php if (is_superadmin()): ?>
    <li><a href="<?= ADMIN_URL ?>/users.php">Usuarios</a></li>
    <?php endif; ?>
  </ul>
  <div class="sidebar-user">
    <span><?= e($user['name'] ?? '') ?></span>
    <a href="<?= ADMIN_URL ?>/logout.php">Salir</a>
  </div>
</nav>
<main class="content">
<div class="topbar"><h1><?= e($title) ?></h1></div>
<?php if ($flash): ?>
<div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
<?php endif; ?>
<?php
}

function layout_foot(): void {
?>
</main>
</body>
</html>
<?php
}
