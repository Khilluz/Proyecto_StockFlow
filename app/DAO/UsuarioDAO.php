<?php

declare(strict_types=1);

namespace StockFlow\DAO;

use PDO;
use StockFlow\Models\Usuario;

final class UsuarioDAO
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findActiveByEmail(string $email): ?Usuario
    {
        $statement = $this->pdo->prepare(
            'SELECT u.id, u.name, u.email, u.password_hash, r.name AS role
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email AND u.active = 1'
        );
        $statement->execute(['email' => $email]);
        $row = $statement->fetch();
        return $row ? Usuario::fromRow($row) : null;
    }

    public function isActive(int $id): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM users WHERE id = :id AND active = 1');
        $statement->execute(['id' => $id]);
        return (bool) $statement->fetchColumn();
    }

    public function listRoles(): array
    {
        return $this->pdo->query('SELECT id, name FROM roles ORDER BY id')->fetchAll();
    }

    public function all(): array
    {
        return $this->pdo->query(
            'SELECT u.id, u.name, u.email, u.active, r.name AS role
             FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.name'
        )->fetchAll();
    }

    public function create(int $roleId, string $name, string $email, string $passwordHash): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (role_id, name, email, password_hash)
             VALUES (:role_id, :name, :email, :password_hash)'
        );
        $statement->execute([
            'role_id' => $roleId,
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function toggleActive(int $id): bool
    {
        $statement = $this->pdo->prepare('UPDATE users SET active = NOT active WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }
}
