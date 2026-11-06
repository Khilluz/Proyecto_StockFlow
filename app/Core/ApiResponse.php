<?php

declare(strict_types=1);

namespace StockFlow\Core;

final class ApiResponse
{
    public static function success(string $message, mixed $data = null, int $status = 200): never
    {
        self::send(true, $message, $data, $status);
    }

    public static function error(string $message, int $status, mixed $data = null): never
    {
        self::send(false, $message, $data, $status);
    }

    private static function send(bool $status, string $message, mixed $data, int $httpStatus): never
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code($httpStatus);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            ['status' => $status, 'message' => $message, 'data' => $data],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
        exit;
    }
}
