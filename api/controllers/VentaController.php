<?php

declare(strict_types=1);

namespace StockFlow\Api\Controllers;

use StockFlow\Core\ApiResponse;
use StockFlow\Services\AuthService;
use StockFlow\Services\VentaService;

final class VentaController
{
    public function __construct(private readonly VentaService $sales)
    {
    }

    public function dispatch(string $method, ?int $id, array $body): never
    {
        AuthService::requireRole(['Administrador', 'Vendedor']);
        if ($method === 'GET') {
            ApiResponse::success(
                'Ventas consultadas correctamente.',
                $id === null ? $this->sales->all() : $this->sales->find($id)
            );
        }
        if ($method === 'POST' && $id === null) {
            $user = AuthService::requireUser();
            ApiResponse::success(
                'Venta registrada correctamente.',
                $this->sales->register((int) $user['id'], $body),
                201
            );
        }

        ApiResponse::error('Método no permitido para este recurso.', 405);
    }
}
