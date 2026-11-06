<?php

declare(strict_types=1);

namespace StockFlow\DAO;

use PDO;
use StockFlow\Models\Venta;

final class VentaDAO
{
    private ?bool $generatedSubtotal = null;

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(Venta $sale, array $items, float $total): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO sales (user_id, channel, total)
             VALUES (:user_id, :channel, :total)'
        );
        $statement->execute([
            'user_id' => $sale->userId(),
            'channel' => $sale->channel(),
            'total' => $total,
        ]);
        $saleId = (int) $this->pdo->lastInsertId();
        $hasGeneratedSubtotal = $this->hasGeneratedSubtotal();
        $itemStatement = $this->pdo->prepare($hasGeneratedSubtotal
            ? 'INSERT INTO sale_items (sale_id, product_id, quantity, unit_price)
               VALUES (:sale_id, :product_id, :quantity, :unit_price)'
            : 'INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal)
               VALUES (:sale_id, :product_id, :quantity, :unit_price, :subtotal)'
        );
        foreach ($items as $item) {
            $values = [
                'sale_id' => $saleId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ];
            if (!$hasGeneratedSubtotal) {
                $values['subtotal'] = $item['subtotal'];
            }
            $itemStatement->execute($values);
        }
        return $saleId;
    }

    private function hasGeneratedSubtotal(): bool
    {
        if ($this->generatedSubtotal !== null) {
            return $this->generatedSubtotal;
        }

        $statement = $this->pdo->query(
            "SELECT EXTRA FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'sale_items'
               AND COLUMN_NAME = 'subtotal'"
        );
        $extra = $statement->fetchColumn();
        if ($extra === false) {
            throw new \RuntimeException('No se encontró la columna subtotal en sale_items.');
        }

        $this->generatedSubtotal = str_contains(strtolower((string) $extra), 'generated');
        return $this->generatedSubtotal;
    }

    public function all(): array
    {
        return $this->pdo->query(
            'SELECT s.id, s.user_id, u.name AS user_name, s.channel, s.total, s.created_at
             FROM sales s JOIN users u ON u.id = s.user_id
             ORDER BY s.created_at DESC, s.id DESC LIMIT 100'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT s.id, s.user_id, u.name AS user_name, s.channel, s.total, s.created_at
             FROM sales s JOIN users u ON u.id = s.user_id WHERE s.id = :id'
        );
        $statement->execute(['id' => $id]);
        $sale = $statement->fetch();
        if (!$sale) {
            return null;
        }
        $items = $this->pdo->prepare(
            'SELECT si.product_id, p.name AS product_name, si.quantity,
                    si.unit_price, si.subtotal
             FROM sale_items si JOIN products p ON p.id = si.product_id
             WHERE si.sale_id = :sale_id ORDER BY si.id'
        );
        $items->execute(['sale_id' => $id]);
        $sale['items'] = $items->fetchAll();
        return $sale;
    }

    public function recent(int $limit = 3): array
    {
        $limit = max(1, min($limit, 20));
        return $this->pdo->query(
            'SELECT s.id, s.total, s.channel, s.created_at, u.name AS user_name
             FROM sales s JOIN users u ON u.id = s.user_id
             ORDER BY s.created_at DESC LIMIT ' . $limit
        )->fetchAll();
    }

    public function byDateRange(string $from, string $to): array
    {
        $statement = $this->pdo->prepare(
            'SELECT s.id, s.channel, s.total, s.created_at, u.name AS user_name
             FROM sales s JOIN users u ON u.id = s.user_id
             WHERE DATE(s.created_at) BETWEEN :from_date AND :to_date
             ORDER BY s.created_at DESC'
        );
        $statement->execute(['from_date' => $from, 'to_date' => $to]);
        return $statement->fetchAll();
    }
}
