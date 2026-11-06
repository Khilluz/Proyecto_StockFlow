<?php

declare(strict_types=1);

namespace StockFlow\Services;

use PDO;
use StockFlow\Core\ApiException;
use StockFlow\DAO\MovimientoStockDAO;
use StockFlow\DAO\ProductoDAO;
use StockFlow\DAO\VentaDAO;
use StockFlow\Models\Venta;
use Throwable;

final class VentaService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly ProductoDAO $products,
        private readonly VentaDAO $sales,
        private readonly MovimientoStockDAO $movements
    ) {
    }

    public function all(): array
    {
        return $this->sales->all();
    }

    public function find(int $id): array
    {
        $sale = $this->sales->find($id);
        if (!$sale) {
            throw new ApiException('No se encontró la venta solicitada.', 404);
        }
        return $sale;
    }

    public function register(int $userId, array $input): array
    {
        $channel = $input['channel'] ?? 'Presencial';
        $rawItems = $input['items'] ?? null;
        if (!is_string($channel)
            || !in_array($channel, ['Presencial', 'Delivery', 'Online'], true)
            || !is_array($rawItems)
            || count($rawItems) === 0
            || count($rawItems) > 50) {
            throw new ApiException('Indica un canal válido y al menos un producto para la venta.', 400);
        }

        $itemsByProduct = [];
        foreach ($rawItems as $item) {
            if (!is_array($item)) {
                throw new ApiException('El detalle de la venta no es válido.', 400);
            }
            $id = filter_var($item['product_id'] ?? null, FILTER_VALIDATE_INT);
            $quantityValue = $item['quantity'] ?? null;
            if (!is_int($id) || $id < 1
                || (!is_int($quantityValue) && !is_float($quantityValue) && !is_string($quantityValue))
                || !is_numeric($quantityValue)) {
                throw new ApiException('Revisa los productos y cantidades de la venta.', 400);
            }
            $quantity = (float) $quantityValue;
            if (!is_finite($quantity) || $quantity <= 0 || $quantity > 999999999.999
                || abs($quantity - round($quantity, 3)) > 0.0000001) {
                throw new ApiException('Las cantidades deben ser mayores que cero y válidas.', 400);
            }
            $itemsByProduct[$id] = ($itemsByProduct[$id] ?? 0) + $quantity;
            if (!is_finite($itemsByProduct[$id]) || $itemsByProduct[$id] > 999999999.999) {
                throw new ApiException('La cantidad total de un producto supera el límite permitido.', 400);
            }
        }

        ksort($itemsByProduct, SORT_NUMERIC);
        $sale = new Venta($userId, $channel, $itemsByProduct);

        try {
            $this->pdo->beginTransaction();
            $lockedProducts = [];
            foreach ($sale->items() as $productId => $quantity) {
                $product = $this->products->lockActive((int) $productId);
                if (!$product || $quantity > (float) $product['stock']) {
                    throw new ApiException(
                        'Un producto no existe o no tiene stock suficiente para completar la venta.',
                        400
                    );
                }
                $lockedProducts[$productId] = $product;
            }

            $items = [];
            $total = 0.0;
            foreach ($sale->items() as $productId => $quantity) {
                $product = $lockedProducts[$productId];
                $subtotal = round($quantity * (float) $product['price'], 2);
                if (!is_finite($subtotal) || $subtotal > 9999999999.99
                    || $total + $subtotal > 9999999999.99) {
                    throw new ApiException('El total de la venta supera el límite permitido.', 400);
                }
                $total += $subtotal;
                $items[] = [
                    'product_id' => (int) $productId,
                    'quantity' => $quantity,
                    'unit_price' => (float) $product['price'],
                    'subtotal' => $subtotal,
                ];
            }
            $total = round($total, 2);
            $saleId = $this->sales->create($sale, $items, $total);

            foreach ($items as $item) {
                if (!$this->products->decreaseStock($item['product_id'], $item['quantity'])) {
                    throw new ApiException('No se pudo actualizar el stock del producto.', 409);
                }
                $this->movements->create(
                    $item['product_id'],
                    $userId,
                    'sale',
                    -$item['quantity'],
                    'Venta #' . $saleId
                );
            }

            $this->pdo->commit();
            return $this->find($saleId);
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }
}
