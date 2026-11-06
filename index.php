<?php
use StockFlow\DAO\UsuarioDAO;
use StockFlow\Services\AuthService;

require_once __DIR__ . '/config/bootstrap.php';
$error = null;
if (current_user()) {
    redirect('dashboard.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $emailInput = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    if (!is_string($emailInput) || !is_string($password)
        || !filter_var(trim($emailInput), FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Ingresa un email y una contraseña válidos.';
    } else {
        $email = trim($emailInput);
        try {
            $user = (new AuthService(new UsuarioDAO(db())))->authenticate($email, $password);
        } catch (RuntimeException $exception) {
            error_log($exception->getMessage());
            $user = null;
            $error = 'No se pudo completar el inicio de sesión. Inténtalo nuevamente.';
        }
        if ($error === null) {
            if (!$user) {
                $error = 'Las credenciales no son correctas.';
            } else {
                $_SESSION['user'] = $user;
                redirect('dashboard.php');
            }
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
<aside class="login-aside"><div class="aside-orbit"></div><div class="login-brand login-aside-brand"><span class="brand-mark">S</span><span>Stock<span>Flow</span></span></div><p class="eyebrow">Tu operación, más clara</p><h2>Decisiones rápidas.<br>Control real.</h2><p>Una vista simple para que cada movimiento cuente.</p><div class="aside-stat"><strong>Acceso seguro</strong><span>Sesiones y roles protegidos</span></div></aside>
<?php require __DIR__ . '/includes/footer.php'; ?>