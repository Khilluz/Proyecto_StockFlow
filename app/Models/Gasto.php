<?php

declare(strict_types=1);

namespace StockFlow\Models;

final class Gasto
{
    public function __construct(
        private readonly string $category,
        private readonly string $description,
        private readonly float $amount,
        private readonly string $date
    ) {
    }

    public function values(): array
    {
        return [
            'category' => $this->category,
            'description' => $this->description,
            'amount' => $this->amount,
            'expense_date' => $this->date,
        ];
    }
}
