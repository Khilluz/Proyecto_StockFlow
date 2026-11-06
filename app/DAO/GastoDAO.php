<?php

declare(strict_types=1);

namespace StockFlow\DAO;

use PDO;
use StockFlow\Models\Gasto;

final class GastoDAO
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(): array
    {
        return $this->pdo->query(
            'SELECT e.*, c.name AS category, u.name AS user_name
             FROM expenses e
             JOIN expense_categories c ON c.id = e.category_id
             JOIN users u ON u.id = e.user_id
             ORDER BY e.expense_date DESC, e.id DESC'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT e.*, c.name AS category, u.name AS user_name
             FROM expenses e
             JOIN expense_categories c ON c.id = e.category_id
             JOIN users u ON u.id = e.user_id WHERE e.id = :id'
        );
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function create(int $userId, Gasto $expense): int
    {
        $values = $expense->values();
        $categoryId = $this->categoryId($values['category']);
        $statement = $this->pdo->prepare(
            'INSERT INTO expenses (user_id, category_id, description, amount, expense_date)
             VALUES (:user_id, :category_id, :description, :amount, :expense_date)'
        );
        $statement->execute([
            'user_id' => $userId,
            'category_id' => $categoryId,
            'description' => $values['description'],
            'amount' => $values['amount'],
            'expense_date' => $values['expense_date'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, Gasto $expense): void
    {
        $values = $expense->values();
        $statement = $this->pdo->prepare(
            'UPDATE expenses SET category_id = :category_id, description = :description,
             amount = :amount, expense_date = :expense_date WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'category_id' => $this->categoryId($values['category']),
            'description' => $values['description'],
            'amount' => $values['amount'],
            'expense_date' => $values['expense_date'],
        ]);
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM expenses WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }

    public function byDateRange(string $from, string $to): array
    {
        $statement = $this->pdo->prepare(
            'SELECT c.name AS category, e.description, e.amount, e.expense_date
             FROM expenses e JOIN expense_categories c ON c.id = e.category_id
             WHERE e.expense_date BETWEEN :from_date AND :to_date
             ORDER BY e.expense_date DESC'
        );
        $statement->execute(['from_date' => $from, 'to_date' => $to]);
        return $statement->fetchAll();
    }

    private function categoryId(string $name): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO expense_categories (name) VALUES (:name)
             ON DUPLICATE KEY UPDATE active = 1'
        );
        $statement->execute(['name' => $name]);
        $statement = $this->pdo->prepare(
            'SELECT id FROM expense_categories WHERE name = :name'
        );
        $statement->execute(['name' => $name]);
        return (int) $statement->fetchColumn();
    }
}
