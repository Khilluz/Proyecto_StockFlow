<?php

declare(strict_types=1);

namespace StockFlow\Services;

use PDO;
use StockFlow\Core\ApiException;
use StockFlow\DAO\MovimientoStockDAO;
use StockFlow\DAO\ProductoDAO;
use StockFlow\Models\Producto;
use Throwable;

final class ProductoService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly ProductoDAO $products,
        private readonly MovimientoStockDAO $movements
    ) {
    }

    public function all(string $search = ''): array
    {
        return $this->products->all($search);
    }

    public function find(int $id): array
    {
        $product = $this->products->find($id);
        if (!$product) {
            throw new ApiException('No se encontró el producto solicitado.', 404);
        }
        return $product;
    }

    public function categories(): array
    {
        return $this->products->categories();
    }

    public function create(int $userId, array $input): array
    {
        $product = $this->validatedProduct($input);
        try {
            $this->pdo->beginTransaction();
            $id = $this->products->create($product);
            if ($product->values()['stock'] > 0) {
                $this->movements->create(
                    $id,
                    $userId,
                    'adjustment',
                    $product->values()['stock'],
                    'Stock inicial del producto'
                );
            }
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
        return $this->find($id);
    }

    public function update(int $id, int $userId, array $input): array
    {
        $product = $this->validatedProduct($input);
        try {
            $this->pdo->beginTransaction();
            $current = $this->products->lockForUpdate($id);
            if (!$current) {
                throw new ApiException('No se encontró el producto solicitado.', 404);
            }
            $this->products->update($id, $product);
            $stockDelta = $product->values()['stock'] - (float) $current['stock'];
            if (abs($stockDelta) > 0.0000001) {
                $this->movements->create(
                    $id,
                    $userId,
                    'adjustment',
                    $stockDelta,
                    'Ajuste manual de inventario'
                );
            }
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
        return $this->find($id);
    }

    public function archive(int $id): void
    {
        if (!$this->products->archive($id)) {
            throw new ApiException('No se encontró el producto solicitado.', 404);
        }
    }

    private function validatedProduct(array $input): Producto
    {
        $name = $this->text($input, 'name', 150);
        $unit = $this->text($input, 'unit', 30);
        $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT);
        if (!is_int($categoryId) || $categoryId < 1 || !$this->products->categoryExists($categoryId)) {
            throw new ApiException('Selecciona una categoría válida.', 400);
        }

        $cost = $this->number($input, 'cost', 9999999999.99, 2);
        $price = $this->number($input, 'price', 9999999999.99, 2);
        $stock = $this->number($input, 'stock', 999999999.999, 3);
        $minimum = $this->number($input, 'minimum_stock', 999999999.999, 3);
        if ($cost < 0 || $price <= $cost || $stock < 0 || $minimum < 0) {
            throw new ApiException(
                'El costo, precio y existencias deben ser válidos; el precio debe superar al costo.',
                400
            );
        }

        return new Producto($name, $categoryId, $unit, $cost, $price, $stock, $minimum);
    }

    private function text(array $input, string $field, int $maxLength): string
    {
        $value = $input[$field] ?? null;
        if (!is_string($value)) {
            throw new ApiException('Completa todos los campos obligatorios del producto.', 400);
        }
        $value = trim($value);
        if ($value === '' || mb_strlen($value) > $maxLength) {
            throw new ApiException('Revisa el nombre y la unidad del producto.', 400);
        }
        return $value;
    }

    private function number(array $input, string $field, float $maximum, int $scale): float
    {
        $value = $input[$field] ?? null;
        if (!is_int($value) && !is_float($value) && !is_string($value)) {
            throw new ApiException('Ingresa valores numéricos válidos para el producto.', 400);
        }
        if (!is_numeric($value)) {
            throw new ApiException('Ingresa valores numéricos válidos para el producto.', 400);
        }
        $number = (float) $value;
        if (!is_finite($number) || $number > $maximum
            || abs($number - round($number, $scale)) > 0.0000001) {
            throw new ApiException('Uno de los valores del producto supera el límite permitido.', 400);
        }
        return $number;
    }
}
