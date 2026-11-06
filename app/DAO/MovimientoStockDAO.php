<?php

declare(strict_types=1);

namespace StockFlow\DAO;

use PDO;

final class MovimientoStockDAO
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(int $productId, int $userId, string $type, float $quantity, ?string $note): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO stock_movements (product_id, user_id, type, quantity, note)
             VALUES (:product_id, :user_id, :type, :quantity, :note)'
        );
        $statement->execute([
            'product_id' => $productId,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => $quantity,
            'note' => $note,
        ]);
    }

    public function all(): array
    {
        return $this->pdo->query(
            'SELECT m.id, m.product_id, p.name AS product_name, m.user_id,
                    u.name AS user_name, m.type, m.quantity, m.note, m.created_at
             FROM stock_movements m
             JOIN products p ON p.id = m.product_id
             JOIN users u ON u.id = m.user_id
             ORDER BY m.created_at DESC, m.id DESC LIMIT 100'
        )->fetchAll();
    }
}
