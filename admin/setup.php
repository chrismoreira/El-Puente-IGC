<?php
/**
 * Setup — crea el primer usuario superadmin.
 * ELIMINAR este archivo después de usarlo.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$done  = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!$name || !$email || !$password) {
        $error = 'Todos los campos son obligatorios.';
    } elseif ($password !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } else {
        $exists = db_get('SELECT id FROM users WHERE role = ?', ['superadmin']);
        if ($exists) {
            $error = 'Ya existe un superadmin. Elimina este archivo del servidor.';
        } else {
            db_run('INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)',
                [$name, $email, password_hash($password, PASSWORD_DEFAULT), 'superadmin']);
            $done = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Setup — El Puente Admin</title>
<style>
  body { font-family: sans-serif; background: #16403d; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
  .box { background: #fff; padding: 36px; border-radius: 10px; width: 100%; max-width: 380px; }
  h2 { margin-bottom: 20px; color: #16403d; }
  label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px; }
  input { width: 100%; padding: 9px 12px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 14px; font-size: 14px; box-sizing: border-box; }
  button { width: 100%; padding: 10px; background: #fd7d42; color: #fff; border: none; border-radius: 6px; font-size: 15px; font-weight: 700; cursor: pointer; }
  .error { color: #c0392b; margin-bottom: 14px; font-size: 14px; }
  .success { color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 12px; border-radius: 6px; font-size: 14px; }
  .warn { background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 10px 12px; border-radius: 6px; font-size: 13px; margin-bottom: 18px; }
</style>
</head>
<body>
<div class="box">
  <h2>Configuración inicial</h2>
  <?php if ($done): ?>
    <div class="success">
      <strong>¡Superadmin creado!</strong><br><br>
      Ahora <strong>elimina este archivo</strong> del servidor:<br>
      <code>rm ~/public_html/admin/setup.php</code><br><br>
      <a href="<?= ADMIN_URL ?>/login.php">Ir al panel →</a>
    </div>
  <?php else: ?>
    <div class="warn">⚠️ Elimina este archivo después de crear el usuario.</div>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post">
      <label>Nombre</label>
      <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      <label>Correo electrónico</label>
      <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      <label>Contraseña (mín. 8 caracteres)</label>
      <input type="password" name="password" required minlength="8">
      <label>Confirmar contraseña</label>
      <input type="password" name="confirm" required minlength="8">
      <button type="submit">Crear superadmin</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
