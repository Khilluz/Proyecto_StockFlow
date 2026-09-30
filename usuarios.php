<?php
require_once __DIR__ . '/config/bootstrap.php';
$user = require_role(['Administrador']);
$pdo = db();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $roleId = (int) ($_POST['role_id'] ?? 0);
            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || !$roleId) {
                throw new InvalidArgumentException('Completa nombre, email, rol y una contraseña de al menos 8 caracteres.');
            }
            $statement = $pdo->prepare('INSERT INTO users (role_id, name, email, password_hash) VALUES (:role_id, :name, :email, :password_hash)');
            $statement->execute(['role_id' => $roleId, 'name' => $name, 'email' => $email, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
            flash('success', 'Usuario creado correctamente.');
            redirect('usuarios.php');
        }
        if ($action === 'toggle') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id === (int) $user['id']) throw new InvalidArgumentException('No puedes desactivar tu propio usuario.');
            $statement = $pdo->prepare('UPDATE users SET active = NOT active WHERE id = :id');
            $statement->execute(['id' => $id]);
            flash('success', 'Estado del usuario actualizado.');
            redirect('usuarios.php');
        }
    } catch (PDOException $exception) {
        $error = $exception->errorInfo[1] === 1062 ? 'Ese email ya está registrado.' : 'No se pudo guardar el usuario.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$roles = $pdo->query('SELECT id, name FROM roles ORDER BY id')->fetchAll();
$users = $pdo->query('SELECT u.id, u.name, u.email, u.active, r.name AS role FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.name')->fetchAll();
$pageTitle = 'Usuarios';
$activePage = 'usuarios';
require __DIR__ . '/includes/header.php';
?>
<section class="page-heading"><div><p class="eyebrow">Accesos y permisos</p><h1>Usuarios</h1><p class="muted">Administra quién puede operar cada módulo.</p></div></section>
<section class="content-grid users-layout"><section class="panel form-panel"><div class="panel-heading"><div><p class="eyebrow">Nuevo acceso</p><h2>Crear usuario</h2></div></div><form class="form-grid" method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="create"><label for="name">Nombre<input id="name" name="name" type="text" required></label><label for="email">Email<input id="email" name="email" type="email" required></label><label for="password">Contraseña<input id="password" name="password" type="password" minlength="8" required></label><label for="role_id">Rol<select id="role_id" name="role_id" required><option value="">Selecciona</option><?php foreach ($roles as $role): ?><option value="<?= e($role['id']) ?>"><?= e($role['name']) ?></option><?php endforeach; ?></select></label><?php if ($error): ?><p class="error-message"><?= e($error) ?></p><?php endif; ?><button class="btn btn-primary" type="submit">Crear usuario <span>→</span></button></form></section>
<section class="panel table-panel"><div class="panel-heading"><div><p class="eyebrow">Directorio</p><h2>Usuarios registrados <span class="count-badge"><?= count($users) ?></span></h2></div></div><div class="table-wrap"><table><thead><tr><th>Usuario</th><th>Email</th><th>Rol</th><th>Estado</th><th></th></tr></thead><tbody><?php foreach ($users as $item): ?><tr><td><strong><?= e($item['name']) ?></strong></td><td><?= e($item['email']) ?></td><td><?= e($item['role']) ?></td><td><span class="status-pill <?= $item['active'] ? 'status-ok' : 'status-low' ?>"><?= $item['active'] ? 'Activo' : 'Inactivo' ?></span></td><td><?php if ((int) $item['id'] !== (int) $user['id']): ?><form class="inline-form" method="post" data-confirm="¿Cambiar el estado de este usuario?"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= e($item['id']) ?>"><button class="table-action" type="submit"><?= $item['active'] ? 'Desactivar' : 'Activar' ?></button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></section></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
