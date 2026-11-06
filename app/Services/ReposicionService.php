<?php

declare(strict_types=1);

namespace StockFlow\Services;

use PDO;
use StockFlow\Core\ApiException;
use StockFlow\DAO\MovimientoStockDAO;
use StockFlow\DAO\ProductoDAO;
use Throwable;

final class ReposicionService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly ProductoDAO $products,
        private readonly MovimientoStockDAO $movements
    ) {
    }

    public function all(): array
    {
        return $this->movements->all();
    }

    public function register(int $userId, array $input): array
    {
        $productId = filter_var($input['product_id'] ?? null, FILTER_VALIDATE_INT);
        $quantityValue = $input['quantity'] ?? null;
        $note = $input['note'] ?? '';
        if (!is_int($productId) || $productId < 1
            || (!is_int($quantityValue) && !is_float($quantityValue) && !is_string($quantityValue))
            || !is_numeric($quantityValue)
            || !is_string($note)) {
            throw new ApiException('Indica un producto, una cantidad y una nota válidos.', 400);
        }

        $quantity = (float) $quantityValue;
        $note = trim($note);
        if (!is_finite($quantity) || $quantity <= 0 || $quantity > 999999999.999
            || abs($quantity - round($quantity, 3)) > 0.0000001 || mb_strlen($note) > 255) {
            throw new ApiException('La cantidad o nota de reposición no es válida.', 400);
        }

        try {
            $this->pdo->beginTransaction();
            if (!$this->products->lockActive($productId)) {
                throw new ApiException('No se encontró el producto solicitado.', 404);
            }
            if (!$this->products->increaseStock($productId, $quantity)) {
                throw new ApiException('No se pudo actualizar el stock del producto.', 409);
            }
            $this->movements->create(
                $productId,
                $userId,
                'purchase',
                $quantity,
                $note !== '' ? $note : 'Ingreso de mercadería'
            );
            $this->pdo->commit();
            return $this->products->find($productId) ?? [];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }
}
