<?php
require_once __DIR__ . '/config/bootstrap.php';
$error = null;
if (current_user()) {
    redirect('dashboard.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Ingresa un email y una contraseña válidos.';
    } else {
        $statement = db()->prepare('SELECT u.id, u.name, u.email, u.password_hash, r.name AS role FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = :email AND u.active = 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'Las credenciales no son correctas.';
        } else {
            session_regenerate_id(true);
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            redirect('dashboard.php');
        }
    }
}
$pageTitle = 'Iniciar sesión';
$isLogin = true;
require __DIR__ . '/includes/header.php';
?>
<section class="login-card" aria-labelledby="login-title">
    <div class="login-brand"><span class="brand-mark">S</span><span>Stock<span>Flow</span></span></div>
    <p class="eyebrow">Gestión operativa</p>
    <h1 id="login-title">Todo tu negocio,<br><em>en movimiento.</em></h1>
    <p class="login-intro">Ingresa a tu espacio de trabajo para controlar inventario, ventas y resultados.</p>
    <form id="loginForm" class="form-grid" method="post" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label for="email">Email<input id="email" name="email" type="email" placeholder="usuario@empresa.com" required></label>
        <label for="password">Contraseña<input id="password" name="password" type="password" placeholder="Ingrese su contraseña" required></label>
        <p id="loginError" class="error-message" aria-live="polite"><?= e($error) ?></p>
        <button class="btn btn-primary btn-block" type="submit">Ingresar <span>→</span></button>
    </form>
</section>
<aside class="login-aside"><div class="aside-orbit"></div><p class="eyebrow">Tu operación, más clara</p><h2>Decisiones rápidas.<br>Control real.</h2><p>Una vista simple para que cada movimiento cuente.</p><div class="aside-stat"><strong>Acceso seguro</strong><span>Sesiones y roles protegidos</span></div></aside>
<?php require __DIR__ . '/includes/footer.php'; ?>