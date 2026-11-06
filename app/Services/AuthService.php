<?php

declare(strict_types=1);

namespace StockFlow\Services;

use PDOException;
use RuntimeException;
use StockFlow\Core\DatabaseConnection;
use StockFlow\Core\SessionManager;
use StockFlow\DAO\UsuarioDAO;

final class AuthService
{
    public function __construct(private readonly UsuarioDAO $users)
    {
    }

    public function authenticate(string $email, string $password): ?array
    {
        try {
            $user = $this->users->findActiveByEmail($email);
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            throw new RuntimeException('No se pudo completar el inicio de sesión.');
        }

        if (!$user || !password_verify($password, $user->passwordHash())) {
            return null;
        }

        SessionManager::regenerate();
        return $user->sessionData();
    }

    public static function currentUser(): ?array
    {
        SessionManager::start();
        $user = $_SESSION['user'] ?? null;
        return is_array($user) ? $user : null;
    }

    public static function requireUser(): array
    {
        $user = self::currentUser();
        if (!$user) {
            throw new \StockFlow\Core\ApiException('Debes iniciar sesión para continuar.', 401);
        }
        $userId = filter_var($user['id'] ?? null, FILTER_VALIDATE_INT);
        if (!is_int($userId) || $userId < 1
            || !(new UsuarioDAO(DatabaseConnection::getInstance()))->isActive($userId)) {
            SessionManager::destroy();
            throw new \StockFlow\Core\ApiException('Debes iniciar sesión para continuar.', 401);
        }
        return $user;
    }

    public static function requireRole(array $roles): array
    {
        $user = self::currentUser();
        if (!$user) {
            throw new \StockFlow\Core\ApiException('Debes iniciar sesión para continuar.', 401);
        }
        if (!in_array($user['role'] ?? null, $roles, true)) {
            throw new \StockFlow\Core\ApiException('No tienes permisos para realizar esta operación.', 403);
        }
        return $user;
    }
}
