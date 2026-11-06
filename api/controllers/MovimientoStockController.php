<?php

declare(strict_types=1);

namespace StockFlow\Api\Controllers;

use StockFlow\Core\ApiResponse;
use StockFlow\Services\AuthService;
use StockFlow\Services\ReposicionService;

final class MovimientoStockController
{
    public function __construct(private readonly ReposicionService $stock)
    {
    }

    public function dispatch(string $method, ?int $id, array $body): never
    {
        AuthService::requireRole(['Administrador', 'Repositor']);
        if ($method === 'GET' && $id === null) {
            ApiResponse::success('Movimientos consultados correctamente.', $this->stock->all());
        }
        if ($method === 'POST' && $id === null) {
            $user = AuthService::requireUser();
            ApiResponse::success(
                'Existencias actualizadas correctamente.',
                $this->stock->register((int) $user['id'], $body),
                201
            );
        }

        ApiResponse::error('Método no permitido para este recurso.', 405);
    }
}
