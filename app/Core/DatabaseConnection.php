<?php

declare(strict_types=1);

namespace StockFlow\Core;

use PDO;
use PDOException;
use RuntimeException;

final class DatabaseConnection
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function getInstance(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = getenv('STOCKFLOW_DB_HOST') ?: '127.0.0.1';
        $port = filter_var(getenv('STOCKFLOW_DB_PORT') ?: '3306', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 65535],
        ]);
        $name = getenv('STOCKFLOW_DB_NAME') ?: 'stockflow';
        $user = getenv('STOCKFLOW_DB_USER') ?: 'root';
        $password = getenv('STOCKFLOW_DB_PASSWORD') ?: '';

        if (!is_int($port) || !preg_match('/^[A-Za-z0-9_]+$/', $name)) {
            throw new RuntimeException('La configuración de base de datos no es válida.');
        }

        try {
            self::$connection = new PDO(
                "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            return self::$connection;
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            throw new RuntimeException('No se pudo conectar con la base de datos.');
        }
    }
}
