<?php

declare(strict_types=1);

namespace StockFlow\DAO;

use PDO;

final class DashboardDAO
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function metrics(): array
    {
        return [
            [
                'label' => 'Ventas del mes',
                'value' => (float) $this->pdo->query(
                    "SELECT COALESCE(SUM(total), 0) FROM sales
                     WHERE created_at >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')"
                )->fetchColumn(),
                'tone' => 'teal',
            ],
            [
                'label' => 'Productos activos',
                'value' => (int) $this->pdo->query(
                    'SELECT COUNT(*) FROM products WHERE active = 1'
                )->fetchColumn(),
                'tone' => 'blue',
            ],
            [
                'label' => 'Gastos del mes',
                'value' => (float) $this->pdo->query(
                    "SELECT COALESCE(SUM(amount), 0) FROM expenses
                     WHERE expense_date >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')"
                )->fetchColumn(),
                'tone' => 'amber',
            ],
            [
                'label' => 'Margen promedio',
                'value' => (float) $this->pdo->query(
                    'SELECT COALESCE(AVG((price - cost) / NULLIF(price, 0) * 100), 0)
                     FROM products WHERE active = 1'
                )->fetchColumn(),
                'tone' => 'coral',
            ],
        ];
    }
}
