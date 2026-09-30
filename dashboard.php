<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/data/repository.php';
$user = require_auth();
$pdo = db();
$metrics = dashboard_metrics($pdo);
$lowStock = low_stock($pdo);
$recentSales = $pdo->query('SELECT s.id, s.total, s.channel, s.created_at, u.name AS user_name FROM sales s JOIN users u ON u.id = s.user_id ORDER BY s.created_at DESC LIMIT 3')->fetchAll();
$pageTitle = 'Dashboard'; $activePage = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<section class="welcome-row"><div><p class="eyebrow">Resumen del <?= e(date('d/m/Y')) ?></p><h1>Buen día, <?= e(explode(' ', $user['name'])[0]) ?> <span>✦</span></h1><p class="muted">Este es el resumen real de tu operación.</p></div><a class="btn btn-primary" href="vendedor.php">+ Nueva venta</a></section>
<section class="metrics-grid" aria-label="Indicadores principales">
<?php foreach ($metrics as $metric): ?><article class="metric-card <?= e($metric['tone']) ?>"><div class="metric-label"><?= e($metric['label']) ?><span class="metric-dot"></span></div><strong><?= $metric['label'] === 'Margen promedio' ? e(number_format($metric['value'], 1, ',', '.') . '%') : ($metric['label'] === 'Productos activos' ? e(number_format($metric['value'], 0, ',', '.')) : e(money($metric['value']))) ?></strong><small>Actualizado desde MySQL</small></article><?php endforeach; ?>
</section>
<div class="content-grid"><section class="panel activity-panel"><div class="panel-heading"><div><p class="eyebrow">En tiempo real</p><h2>Ventas recientes</h2></div><a class="text-link" href="reportes.php">Ver todo →</a></div><ul class="activity-list"><?php if (!$recentSales): ?><li class="empty-state">Todavía no hay ventas registradas.</li><?php endif; ?><?php foreach ($recentSales as $sale): ?><li><span class="activity-icon sale">$</span><div><strong>Venta #<?= e($sale['id']) ?></strong><small><?= e($sale['channel']) ?> · <?= e($sale['user_name']) ?></small></div><b>+<?= money($sale['total']) ?></b></li><?php endforeach; ?></ul></section><section class="panel alert-panel"><div><p class="eyebrow">Atención necesaria</p><h2>Alertas de inventario</h2></div><div class="alert-items"><span><b><?= count($lowStock) ?></b> productos bajo mínimo</span><a class="btn btn-outline" href="inventario.php">Revisar inventario →</a></div></section></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
