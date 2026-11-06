<?php

use StockFlow\DAO\MovimientoStockDAO;
use StockFlow\DAO\ProductoDAO;

require_once __DIR__ . '/config/bootstrap.php';
require_role(['Administrador', 'Repositor']);
$pdo = db();
$products = (new ProductoDAO($pdo))->all();
$movements = (new MovimientoStockDAO($pdo))->all();
$pageTitle = 'Reposición';
$activePage = 'repositor';
require __DIR__ . '/includes/header.php';
?>
<section class="page-heading">
    <div><p class="eyebrow">Movimientos de almacén</p><h1>Reposición</h1><p class="muted">Registra la mercadería recibida y actualiza existencias.</p></div>
</section>
<section class="panel form-panel narrow-panel">
    <div class="panel-heading"><div><p class="eyebrow">Nueva entrada</p><h2>Ingreso de mercadería</h2></div></div>
    <form id="repositorForm" class="form-grid" method="post">
        <label for="product_id">Producto
            <select id="product_id" name="product_id" required>
                <option value="">Selecciona un producto</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= e($product['id']) ?>" data-name="<?= e($product['name']) ?>" data-stock="<?= e($product['stock']) ?>"><?= e($product['name']) ?> · <?= quantity($product['stock']) ?> actuales</option>
                <?php endforeach; ?>
            </select>
        </label>
        <label for="quantity">Unidades recibidas<input id="quantity" name="quantity" type="number" min="1" max="999999999" step="1" value="1" required></label>
        <label for="note">Nota<input id="note" name="note" type="text" maxlength="255" placeholder="Proveedor o referencia"></label>
        <button class="btn btn-primary" type="submit">Actualizar existencias <span>→</span></button>
    </form>
</section>
<section class="panel info-panel"><span class="info-symbol">i</span><div><strong>Movimiento persistente</strong><p>Cada entrada actualiza el stock y queda registrada en el historial de movimientos.</p></div></section>
<section class="panel table-panel">
    <div class="panel-heading"><div><p class="eyebrow">Trazabilidad</p><h2>Movimientos recientes</h2></div></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Producto</th><th>Tipo</th><th>Cantidad</th><th>Usuario</th><th>Nota</th><th>Fecha</th></tr></thead>
            <tbody id="stockMovementsBody">
                <?php foreach ($movements as $movement): ?>
                    <tr><td><?= e($movement['product_name']) ?></td><td><?= e($movement['type']) ?></td><td><?= precise_quantity($movement['quantity']) ?></td><td><?= e($movement['user_name']) ?></td><td><?= e($movement['note'] ?? '') ?></td><td><?= e($movement['created_at']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$movements): ?><tr data-empty-row><td colspan="6" class="empty-state">Todavía no hay movimientos registrados.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
