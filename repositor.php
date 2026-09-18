<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/data/repository.php';
$user = require_role(['Administrador', 'Repositor']);
$pdo = db(); $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $productId = (int) ($_POST['product_id'] ?? 0); $quantity = (float) ($_POST['quantity'] ?? 0); $note = trim($_POST['note'] ?? '');
        if (!$productId || $quantity <= 0) throw new InvalidArgumentException('Selecciona un producto y una cantidad válida.');
        $pdo->beginTransaction(); $statement = $pdo->prepare('SELECT id FROM products WHERE id = :id AND active = 1 FOR UPDATE'); $statement->execute(['id' => $productId]); if (!$statement->fetch()) throw new InvalidArgumentException('El producto seleccionado no existe.');
        $statement = $pdo->prepare('UPDATE products SET stock = stock + :quantity WHERE id = :id'); $statement->execute(['quantity' => $quantity, 'id' => $productId]); $statement = $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, note) VALUES (:product_id, :user_id, 'purchase', :quantity, :note)"); $statement->execute(['product_id' => $productId, 'user_id' => $user['id'], 'quantity' => $quantity, 'note' => $note ?: 'Ingreso de mercadería']); $pdo->commit(); flash('success', 'Existencias actualizadas correctamente.'); redirect('repositor.php');
    } catch (Throwable $exception) { if ($pdo->inTransaction()) $pdo->rollBack(); $error = $exception->getMessage(); }
}
$products = products($pdo); $pageTitle = 'Reposición'; $activePage = 'repositor'; require __DIR__ . '/includes/header.php';
?>
<section class="page-heading"><div><p class="eyebrow">Movimientos de almacén</p><h1>Reposición</h1><p class="muted">Registra la mercadería recibida y actualiza existencias.</p></div></section><section class="panel form-panel narrow-panel"><div class="panel-heading"><div><p class="eyebrow">Nueva entrada</p><h2>Ingreso de mercadería</h2></div></div><form id="repositorForm" class="form-grid" method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label for="product_id">Producto<select id="product_id" name="product_id" required><option value="">Selecciona un producto</option><?php foreach ($products as $product): ?><option value="<?= e($product['id']) ?>"><?= e($product['name']) ?> · <?= e($product['stock']) ?> actuales</option><?php endforeach; ?></select></label><label for="quantity">Unidades recibidas<input id="quantity" name="quantity" type="number" min="0.001" step="0.001" value="1" required></label><label for="note">Nota<input id="note" name="note" type="text" placeholder="Proveedor o referencia"></label><?php if ($error): ?><p class="error-message"><?= e($error) ?></p><?php endif; ?><button class="btn btn-primary" type="submit">Actualizar existencias <span>→</span></button></form></section><section class="panel info-panel"><span class="info-symbol">i</span><div><strong>Movimiento persistente</strong><p>Cada entrada actualiza el stock y queda registrada en el historial de movimientos.</p></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
