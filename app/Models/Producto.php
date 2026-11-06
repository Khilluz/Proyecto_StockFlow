<?php

declare(strict_types=1);

namespace StockFlow\Models;

final class Producto
{
    public function __construct(
        private readonly string $name,
        private readonly int $categoryId,
        private readonly string $unit,
        private readonly float $cost,
        private readonly float $price,
        private readonly float $stock,
        private readonly float $minimumStock
    ) {
    }

    public function values(): array
    {
        return [
            'name' => $this->name,
            'category_id' => $this->categoryId,
            'unit' => $this->unit,
            'cost' => $this->cost,
            'price' => $this->price,
            'stock' => $this->stock,
            'minimum_stock' => $this->minimumStock,
        ];
    }
}
