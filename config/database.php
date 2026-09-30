<?php

function db(): PDO
{
    static $connection;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = getenv('STOCKFLOW_DB_HOST') ?: '127.0.0.1';
    $port = getenv('STOCKFLOW_DB_PORT') ?: '3306';
    $name = getenv('STOCKFLOW_DB_NAME') ?: 'stockflow';
    $user = getenv('STOCKFLOW_DB_USER') ?: 'root';
    $password = getenv('STOCKFLOW_DB_PASSWORD') ?: '';
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    try {
        $connection = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $connection;
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        throw new RuntimeException('No se pudo conectar con la base de datos. Importa database/database.sql y revisa la configuración de Laragon.');
    }
}