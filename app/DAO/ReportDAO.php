<?php

declare(strict_types=1);

namespace StockFlow\DAO;

final class ReportDAO
{
    public function __construct(
        private readonly ProductoDAO $products,
        private readonly GastoDAO $expenses,
        private readonly VentaDAO $sales
    ) {
    }

    public function run(string $type, string $from, string $to): array
    {
        if ($type === 'stock') {
            return $this->products->allByStock();
        }
        if ($type === 'gastos') {
            return $this->expenses->byDateRange($from, $to);
        }
        return $this->sales->byDateRange($from, $to);
    }
}
