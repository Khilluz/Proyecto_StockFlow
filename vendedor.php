<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/data/repository.php';
require_role(['Administrador', 'Vendedor']);
$pdo = db();
$_SESSION['cart'] ??= [];
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $action = $_POST['action'] ?? '';
        if ($action === 'add') {
            $productId = (int) ($_POST['product_id'] ?? 0); $quantity = (float) ($_POST['quantity'] ?? 0);
            $product = product($pdo, $productId);
            if (!$product || $quantity <= 0) throw new InvalidArgumentException('Selecciona un producto y una cantidad válida.');
            $current = (float) ($_SESSION['cart'][$productId] ?? 0);
            if ($current + $quantity > (float) $product['stock']) throw new InvalidArgumentException('La cantidad supera el stock disponible.');
            $_SESSION['cart'][$productId] = $current + $quantity; flash('success', 'Producto agregado a la venta.'); redirect('vendedor.php');
        }
        if ($action === 'remove') { unset($_SESSION['cart'][(int) $_POST['product_id']]); flash('success', 'Producto quitado de la venta.'); redirect('vendedor.php'); }
        if ($action === 'confirm') {
            $channel = $_POST['channel'] ?? 'Presencial';
            if (!in_array($channel, ['Presencial', 'Delivery', 'Online'], true) || !$_SESSION['cart']) throw new InvalidArgumentException('Agrega al menos un producto antes de confirmar.');
            $pdo->beginTransaction(); $total = 0; $items = [];
            foreach ($_SESSION['cart'] as $productId => $quantity) {
                $statement = $pdo->prepare('SELECT id, price, stock FROM products WHERE id = :id AND active = 1 FOR UPDATE'); $statement->execute(['id' => $productId]); $product = $statement->fetch();
                if (!$product || $quantity > (float) $product['stock']) throw new InvalidArgumentException('Uno de los productos ya no tiene stock suficiente.');
                $subtotal = $quantity * (float) $product['price']; $total += $subtotal; $items[] = [$product, $quantity, $subtotal];
            }
            $statement = $pdo->prepare('INSERT INTO sales (user_id, channel, total) VALUES (:user_id, :channel, :total)'); $statement->execute(['user_id' => current_user()['id'], 'channel' => $channel, 'total' => $total]); $saleId = (int) $pdo->lastInsertId();
            foreach ($items as [$product, $quantity, $subtotal]) { $statement = $pdo->prepare('INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) VALUES (:sale_id, :product_id, :quantity, :unit_price, :subtotal)'); $statement->execute(['sale_id' => $saleId, 'product_id' => $product['id'], 'quantity' => $quantity, 'unit_price' => $product['price'], 'subtotal' => $subtotal]); $statement = $pdo->prepare('UPDATE products SET stock = stock - :quantity WHERE id = :id'); $statement->execute(['quantity' => $quantity, 'id' => $product['id']]); $statement = $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, note) VALUES (:product_id, :user_id, 'sale', :quantity, :note)"); $statement->execute(['product_id' => $product['id'], 'user_id' => current_user()['id'], 'quantity' => -$quantity, 'note' => 'Venta #' . $saleId]); }
            $pdo->commit(); $_SESSION['cart'] = []; flash('success', 'Venta #' . $saleId . ' registrada por ' . money($total) . '.'); redirect('vendedor.php');
        }
    } catch (Throwable $exception) { if ($pdo->inTransaction()) $pdo->rollBack(); $error = $exception->getMessage(); }
}
$products = products($pdo); $cart = $_SESSION['cart']; $cartRows = []; $total = 0;
foreach ($cart as $id => $quantity) { $item = product($pdo, (int) $id); if ($item) { $subtotal = $quantity * (float) $item['price']; $total += $subtotal; $cartRows[] = ['product' => $item, 'quantity' => $quantity, 'subtotal' => $subtotal]; } }
$pageTitle = 'Ventas'; $activePage = 'vendedor'; require __DIR__ . '/includes/header.php';
?>
<section class="page-heading"><div><p class="eyebrow">Punto de venta</p><h1>Nueva venta</h1><p class="muted">Arma el pedido y confirma la operación.</p></div><span class="live-badge"><i></i> Caja activa</span></section>
<section class="content-grid sale-layout"><section class="panel form-panel"><div class="panel-heading"><div><p class="eyebrow">Paso 01</p><h2>Agregar productos</h2></div></div><form id="saleForm" class="form-grid" method="POST"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="add"><label for="product_id">Producto<select id="product_id" name="product_id" required><option value="">Selecciona un producto</option><?php foreach ($products as $product): ?><option value="<?= e($product['id']) ?>"><?= e($product['name']) ?> · <?= money($product['price']) ?> · <?= e($product['stock']) ?> disponibles</option><?php endforeach; ?></select></label><label for="quantity">Cantidad<input id="quantity" name="quantity" type="number" min="1" step="1" value="1" required></label><?php if ($error): ?><p class="error-message"><?= e($error) ?></p><?php endif; ?><button class="btn btn-secondary" type="submit">Agregar a la venta <span>→</span></button></form></section><section class="panel cart-panel"><div class="panel-heading"><div><p class="eyebrow">Paso 02</p><h2>Detalle de venta</h2></div><span class="count-badge"><?= count($cartRows) ?> items</span></div><div class="table-wrap"><table><thead><tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th><th></th></tr></thead><tbody id="itemsBody"><?php if (!$cartRows): ?><tr class="empty-row"><td colspan="5">Selecciona un producto para comenzar</td></tr><?php endif; ?><?php foreach ($cartRows as $row): ?><tr><td><strong><?= e($row['product']['name']) ?></strong></td><td><?= e($row['quantity']) ?></td><td><?= money($row['product']['price']) ?></td><td><strong><?= money($row['subtotal']) ?></strong></td><td><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type("hidden", name, value) ?>"><input type("hidden", name, value) ?>"><button class("table-action danger", type, value) ?>">Quitar</button></form></td></tr><?php endforeach; ?></tbody></table></div><div id("totalDisplay", class("total-box")) ?>"><span>Total de la venta</span><strong><?= money($total) ?></strong></div><form method("post", class("confirm-sale-form")) ?>"><input type("hidden", name, value) ?>"><input type("hidden", name, value) ?>"><label for("channel") ?>">Canal<select id("channel", name("channel")) ?>"><option>Presencial</option><option>Delivery</option><option>Online</option></select></label><button id("confirmSaleBtn", class("btn btn-primary btn-block"), type("submit"), <?= !$cartRows ? 'disabled' : '' ?>>Confirmar venta <span>→</span></button></form></section></section>
<?php require __DIR__ . '/includes/footer.php'; ?>