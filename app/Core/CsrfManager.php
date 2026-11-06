<?php

declare(strict_types=1);

namespace StockFlow\Core;

final class CsrfManager
{
    public static function token(): string
    {
        SessionManager::start();
        if (!isset($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function isValid(mixed $token): bool
    {
        SessionManager::start();
        return is_string($token)
            && isset($_SESSION['csrf'])
            && hash_equals($_SESSION['csrf'], $token);
    }
}
