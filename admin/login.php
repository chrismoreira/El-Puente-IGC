<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

session_start_safe();
if (!empty($_SESSION['user_id'])) {
    redirect(ADMIN_URL . '/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (auth_login($email, $password)) {
        redirect(ADMIN_URL . '/index.php');
    }
    $error = 'Correo o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Iniciar sesión — El Puente Admin</title>
<link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-box">
    <div class="login-logo">
      <img src="<?= APP_URL ?>/icon.svg" alt="El Puente">
      <h2>El Puente Admin</h2>
    </div>
    <?php if ($error): ?>
    <div class="alert alert-error" style="margin:0 0 16px"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['timeout'])): ?>
    <div class="alert alert-info" style="margin:0 0 16px">Tu sesión expiró. Inicia sesión nuevamente.</div>
    <?php endif; ?>
    <form method="post">
      <div class="form-group">
        <label>Correo electrónico</label>
        <input type="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Contraseña</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%">Ingresar</button>
    </form>
  </div>
</div>
</body>
</html>
