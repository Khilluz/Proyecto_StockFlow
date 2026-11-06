<?php

declare(strict_types=1);

namespace StockFlow\DAO;

use PDO;
use StockFlow\Models\Producto;

final class ProductoDAO
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(string $search = ''): array
    {
        $sql = 'SELECT p.*, c.name AS category_name FROM products p
                JOIN categories c ON c.id = p.category_id WHERE p.active = 1';
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (p.name LIKE :product_search OR c.name LIKE :category_search)';
            $params['product_search'] = '%' . $search . '%';
            $params['category_search'] = '%' . $search . '%';
        }
        $sql .= ' ORDER BY p.name';
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public function allByStock(): array
    {
        return $this->pdo->query(
            'SELECT p.*, c.name AS category_name FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.active = 1 ORDER BY p.stock ASC'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT p.*, c.name AS category_name FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.id = :id AND p.active = 1'
        );
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function categories(): array
    {
        return $this->pdo->query(
            'SELECT id, name FROM categories WHERE active = 1 ORDER BY name'
        )->fetchAll();
    }

    public function categoryExists(int $id): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM categories WHERE id = :id AND active = 1');
        $statement->execute(['id' => $id]);
        return (bool) $statement->fetchColumn();
    }

    public function lowStock(): array
    {
        return $this->pdo->query(
            'SELECT p.name, p.stock, p.minimum_stock FROM products p
             WHERE p.active = 1 AND p.stock <= p.minimum_stock ORDER BY p.stock'
        )->fetchAll();
    }

    public function create(Producto $product): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO products
             (category_id, name, unit, cost, price, stock, minimum_stock)
             VALUES (:category_id, :name, :unit, :cost, :price, :stock, :minimum_stock)'
        );
        $statement->execute($product->values());
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, Producto $product): void
    {
        $values = $product->values();
        $values['id'] = $id;
        $statement = $this->pdo->prepare(
            'UPDATE products SET category_id = :category_id, name = :name, unit = :unit,
             cost = :cost, price = :price, stock = :stock, minimum_stock = :minimum_stock
             WHERE id = :id AND active = 1'
        );
        $statement->execute($values);
    }

    public function archive(int $id): bool
    {
        $statement = $this->pdo->prepare('UPDATE products SET active = 0 WHERE id = :id AND active = 1');
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }

    public function lockActive(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, price, stock FROM products
             WHERE id = :id AND active = 1 FOR UPDATE'
        );
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function lockForUpdate(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, stock FROM products WHERE id = :id AND active = 1 FOR UPDATE'
        );
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function decreaseStock(int $id, float $quantity): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE products SET stock = stock - :quantity
             WHERE id = :id AND active = 1 AND stock >= :available'
        );
        $statement->execute(['quantity' => $quantity, 'id' => $id, 'available' => $quantity]);
        return $statement->rowCount() > 0;
    }

    public function increaseStock(int $id, float $quantity): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE products SET stock = stock + :quantity WHERE id = :id AND active = 1'
        );
        $statement->execute(['quantity' => $quantity, 'id' => $id]);
        return $statement->rowCount() > 0;
    }
}
