<?php

declare(strict_types=1);

namespace StockFlow\Models;

final class Usuario
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $email,
        private readonly string $role,
        private readonly string $passwordHash
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['name'],
            (string) $row['email'],
            (string) $row['role'],
            (string) $row['password_hash']
        );
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function sessionData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];
    }
}
