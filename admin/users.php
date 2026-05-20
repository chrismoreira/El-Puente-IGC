<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

auth_check();
if (!is_superadmin()) {
    flash('Solo el superadmin puede gestionar usuarios.', 'error');
    redirect(ADMIN_URL . '/index.php');
}
csrf_verify();

$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

// Toggle active
if ($action === 'toggle' && $id) {
    $u = db_get('SELECT id, active, role FROM users WHERE id = ?', [$id]);
    if ($u && $u['role'] !== 'superadmin') {
        db_run('UPDATE users SET active = ? WHERE id = ?', [$u['active'] ? 0 : 1, $id]);
    }
    redirect(ADMIN_URL . '/users.php');
}

// Delete
if ($action === 'delete' && $id) {
    $u = db_get('SELECT role FROM users WHERE id = ?', [$id]);
    if ($u && $u['role'] !== 'superadmin') {
        db_run('DELETE FROM users WHERE id = ?', [$id]);
        flash('Usuario eliminado.');
    }
    redirect(ADMIN_URL . '/users.php');
}

// Save (create / edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id  = (int)($_POST['id'] ?? 0);
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = in_array($_POST['role'] ?? '', ['admin', 'superadmin']) ? $_POST['role'] : 'admin';
    $password = $_POST['password'] ?? '';

    if (!$name || !$email) {
        flash('Nombre y correo son obligatorios.', 'error');
        redirect(ADMIN_URL . '/users.php');
    }

    if ($edit_id) {
        $params = [$name, $email, $role, $edit_id];
        $sql    = 'UPDATE users SET name=?, email=?, role=? WHERE id=?';
        if ($password) {
            $sql    = 'UPDATE users SET name=?, email=?, role=?, password_hash=? WHERE id=?';
            $params = [$name, $email, $role, password_hash($password, PASSWORD_DEFAULT), $edit_id];
        }
        db_run($sql, $params);
        flash('Usuario actualizado.');
    } else {
        if (!$password) {
            flash('La contraseña es obligatoria para nuevos usuarios.', 'error');
            redirect(ADMIN_URL . '/users.php');
        }
        db_run('INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)',
            [$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        flash('Usuario creado.');
    }
    redirect(ADMIN_URL . '/users.php');
}

$users   = db_all('SELECT * FROM users ORDER BY created_at');
$edit    = $id ? db_get('SELECT * FROM users WHERE id = ?', [$id]) : null;

layout_head('Usuarios');
?>
<div class="page-body">

  <div class="card" style="max-width:480px;margin-bottom:28px">
    <div class="card-header"><?= $edit ? 'Editar usuario' : 'Nuevo usuario' ?></div>
    <div class="card-body">
      <form method="post">
        <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= $edit ? $edit['id'] : '' ?>">
        <div class="form-group">
          <label>Nombre</label>
          <input type="text" name="name" required value="<?= e($edit['name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Correo</label>
          <input type="email" name="email" required value="<?= e($edit['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Contraseña <?= $edit ? '(dejar vacío para no cambiar)' : '' ?></label>
          <input type="password" name="password" <?= $edit ? '' : 'required' ?> minlength="8">
        </div>
        <div class="form-group">
          <label>Rol</label>
          <select name="role">
            <option value="admin" <?= ($edit['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="superadmin" <?= ($edit['role'] ?? '') === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
          </select>
        </div>
        <div style="display:flex;gap:10px">
          <button type="submit" class="btn btn-primary"><?= $edit ? 'Actualizar' : 'Crear usuario' ?></button>
          <?php if ($edit): ?>
          <a href="users.php" class="btn btn-ghost">Cancelar</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">Usuarios registrados</div>
    <div class="card-body" style="padding:0">
      <div class="table-wrap">
        <table>
          <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Último acceso</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td><?= e($u['name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['role']) ?></td>
            <td><span class="badge badge-<?= $u['active'] ? 'active' : 'inactive' ?>"><?= $u['active'] ? 'Activo' : 'Inactivo' ?></span></td>
            <td><?= $u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : '—' ?></td>
            <td>
              <?php if ($u['role'] !== 'superadmin'): ?>
              <a href="?action=toggle&id=<?= $u['id'] ?>" class="btn btn-ghost btn-sm"><?= $u['active'] ? 'Desactivar' : 'Activar' ?></a>
              <a href="?id=<?= $u['id'] ?>" class="btn btn-ghost btn-sm">Editar</a>
              <a href="?action=delete&id=<?= $u['id'] ?>" class="btn btn-danger btn-sm"
                 onclick="return confirm('¿Eliminar este usuario?')">Eliminar</a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php layout_foot(); ?>
