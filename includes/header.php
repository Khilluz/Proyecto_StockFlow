<?php
$pageTitle = $pageTitle ?? 'StockFlow';
$activePage = $activePage ?? '';
$isLogin = $isLogin ?? false;
$user = current_user();
$flash = pull_flash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StockFlow, gestión operativa para tu negocio">
    <title><?= e($pageTitle) ?> | StockFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body class="<?= $isLogin ? 'login-page' : 'app-page' ?>">
<?php if (!$isLogin): ?>
    <button class="menu-toggle" type="button" aria-label="Abrir menú" aria-controls="appSidebar" aria-expanded="false" data-menu-toggle>
        <span></span><span></span><span></span>
    </button>
    <div class="sidebar-backdrop" data-menu-close></div>
    <?php require __DIR__ . '/sidebar.php'; ?>
    <div class="app-shell">
        <header class="topbar">
            <a class="mobile-brand" href="dashboard.php">StockFlow</a>
            <div>
                <p class="eyebrow">Panel de control</p>
                <p class="topbar-title"><?= e($pageTitle) ?></p>
            </div>
            <div class="user-chip"><span class="avatar"><?= e(strtoupper(substr($user['name'] ?? 'U', 0, 2))) ?></span><span><strong><?= e($user['name'] ?? '') ?></strong><small><?= e($user['role'] ?? '') ?></small></span></div>
        </header>
        <main class="main-content">
    <?php if ($flash): ?><div class="flash flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div><?php endif; ?>
<?php else: ?>
    <main class="login-shell">
<?php endif; ?>