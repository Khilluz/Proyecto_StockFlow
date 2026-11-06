<?php
use StockFlow\DAO\GastoDAO;
use StockFlow\DAO\ProductoDAO;
use StockFlow\DAO\ReportDAO;
use StockFlow\DAO\VentaDAO;

require_once __DIR__ . '/config/bootstrap.php';
$user = require_auth();
$pdo = db();
$validDate = static function (mixed $value, string $fallback): string {
    $parsed = is_string($value) ? DateTimeImmutable::createFromFormat('!Y-m-d', $value) : false;
    $errors = DateTimeImmutable::getLastErrors();
    if (!$parsed || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
        || $parsed->format('Y-m-d') !== $value) {
        return $fallback;
    }
    return $value;
};
$from = $validDate($_GET['from'] ?? null, date('Y-m-01'));
$to = $validDate($_GET['to'] ?? null, date('Y-m-d'));
$type = $_GET['type'] ?? 'ventas';
if (!in_array($type, ['stock', 'gastos', 'ventas'], true)) $type = 'ventas';
$report = [];
$reportDao = new ReportDAO(new ProductoDAO($pdo), new GastoDAO($pdo), new VentaDAO($pdo));
$report = $reportDao->run($type, $from, $to);
$total = $type === 'stock' ? count($report) : array_sum(array_map(static fn (array $row): float => (float) ($row['total'] ?? $row['amount'] ?? 0), $report));
$pageTitle = 'Reportes'; $activePage = 'reportes'; require __DIR__ . '/includes/header.php';
?>
<section class="page-heading"><div><p class="eyebrow">Información para decidir</p><h1>Reportes</h1><p class="muted">Consulta información real de tu negocio por período.</p></div></section><section class="panel report-panel"><div class="panel-heading"><div><p class="eyebrow">Generador</p><h2>Crear un reporte</h2></div><span class="report-icon">▤</span></div><form id="reportForm" class="form-row" method="get"><label for="from">Fecha inicio<input id="from" name="from" type="date" value="<?= e($from) ?>"></label><label for="to">Fecha fin<input id="to" name="to" type="date" value="<?= e($to) ?>"></label><label for="type">Tipo de reporte<select id="type" name="type"><option value="ventas" <?= $type === 'ventas' ? 'selected' : '' ?>>Ventas</option><option value="stock" <?= $type === 'stock' ? 'selected' : '' ?>>Stock</option><option value="gastos" <?= $type === 'gastos' ? 'selected' : '' ?>>Gastos</option></select></label><button class="btn btn-primary" type="submit">Generar <span>→</span></button></form></section><section class="panel table-panel report-results"><div class="panel-heading"><div><p class="eyebrow">Resultado</p><h2><?= $type === 'stock' ? 'Estado de stock' : ($type === 'gastos' ? 'Gastos del período' : 'Ventas del período') ?></h2></div><strong class="report-total"><?= $type === 'stock' ? e($total) . ' productos' : money($total) ?></strong></div><div class="table-wrap"><table><thead><tr><?php if ($type === 'stock'): ?><th>Producto</th><th>Categoría</th><th>Stock</th><th>Mínimo</th><th>Precio</th><?php elseif ($type === 'gastos'): ?><th>Categoría</th><th>Descripción</th><th>Monto</th><th>Fecha</th><?php else: ?><th>Venta</th><th>Canal</th><th>Usuario</th><th>Total</th><th>Fecha</th><?php endif; ?></tr></thead><tbody><?php foreach ($report as $row): ?><tr><?php if ($type === 'stock'): ?><td><?= e($row['name']) ?></td><td><?= e($row['category_name']) ?></td><td><?= quantity($row['stock']) ?></td><td><?= quantity($row['minimum_stock']) ?></td><td><?= money($row['price']) ?></td><?php elseif ($type === 'gastos'): ?><td><?= e($row['category']) ?></td><td><?= e($row['description']) ?></td><td><?= money($row['amount']) ?></td><td><?= e($row['expense_date']) ?></td><?php else: ?><td>#<?= e($row['id']) ?></td><td><?= e($row['channel']) ?></td><td><?= e($row['user_name']) ?></td><td><?= money($row['total']) ?></td><td><?= e($row['created_at']) ?></td><?php endif; ?></tr><?php endforeach; ?><?php if (!$report): ?><tr><td colspan="5" class="empty-state">No hay datos para el período seleccionado.</td></tr><?php endif; ?></tbody></table></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
