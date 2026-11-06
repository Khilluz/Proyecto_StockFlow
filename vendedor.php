<?php

use StockFlow\DAO\ProductoDAO;
use StockFlow\DAO\VentaDAO;

require_once __DIR__ . '/config/bootstrap.php';
require_role(['Administrador', 'Vendedor']);
$pdo = db();
$products = (new ProductoDAO($pdo))->all();
$sales = (new VentaDAO($pdo))->all();
$pageTitle = 'Ventas';
$activePage = 'vendedor';
require __DIR__ . '/includes/header.php';
?>
<section class="page-heading">
    <div><p class="eyebrow">Punto de venta</p><h1>Nueva venta</h1><p class="muted">Arma el pedido y confirma la operación.</p></div>
    <span class="live-badge"><i></i> Caja activa</span>
</section>
<section class="content-grid sale-layout">
    <section class="panel form-panel">
        <div class="panel-heading"><div><p class="eyebrow">Paso 01</p><h2>Agregar productos</h2></div></div>
        <form id="saleForm" class="form-grid" method="post">
            <label for="product_id">Producto
                <select id="product_id" name="product_id" required>
                    <option value="">Selecciona un producto</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= e($product['id']) ?>" data-name="<?= e($product['name']) ?>" data-price="<?= e($product['price']) ?>" data-stock="<?= e($product['stock']) ?>">
                            <?= e($product['name']) ?> · <?= money($product['price']) ?> · <?= quantity($product['stock']) ?> disponibles
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label for="quantity">Cantidad<input id="quantity" name="quantity" type="number" min="1" step="1" value="1" required></label>
            <button class="btn btn-secondary" type="submit">Agregar a la venta <span>→</span></button>
        </form>
    </section>
    <section class="panel cart-panel">
        <div class="panel-heading"><div><p class="eyebrow">Paso 02</p><h2>Detalle de venta</h2></div><span id="cartCount" class="count-badge">0 items</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th><th></th></tr></thead>
                <tbody id="itemsBody"><tr class="empty-row"><td colspan="5">Selecciona un producto para comenzar</td></tr></tbody>
            </table>
        </div>
        <div id="totalDisplay" class="total-box"><span>Total de la venta</span><strong id="saleTotal">$0,00</strong></div>
        <form id="confirmSaleForm" class="confirm-sale-form" method="post">
            <label for="channel">Canal<select id="channel" name="channel"><option>Presencial</option><option>Delivery</option><option>Online</option></select></label>
            <button id="confirmSaleBtn" class="btn btn-primary btn-block" type="submit" disabled>Confirmar venta <span>→</span></button>
        </form>
    </section>
</section>
<section class="panel table-panel">
    <div class="panel-heading"><div><p class="eyebrow">Historial</p><h2>Ventas recientes</h2></div></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Venta</th><th>Canal</th><th>Vendedor</th><th>Total</th><th>Fecha</th></tr></thead>
            <tbody id="salesHistoryBody">
                <?php foreach ($sales as $sale): ?>
                    <tr><td>#<?= e($sale['id']) ?></td><td><?= e($sale['channel']) ?></td><td><?= e($sale['user_name']) ?></td><td><?= money($sale['total']) ?></td><td><?= e($sale['created_at']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$sales): ?><tr data-empty-row><td colspan="5" class="empty-state">Todavía no hay ventas registradas.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
