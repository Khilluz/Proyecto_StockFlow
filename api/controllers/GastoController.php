<?php

declare(strict_types=1);

namespace StockFlow\Api\Controllers;

use StockFlow\Core\ApiResponse;
use StockFlow\Services\AuthService;
use StockFlow\Services\GastoService;

final class GastoController
{
    public function __construct(private readonly GastoService $expenses)
    {
    }

    public function dispatch(string $method, ?int $id, array $body): never
    {
        AuthService::requireRole(['Administrador']);
        if ($method === 'GET') {
            ApiResponse::success(
                'Gastos consultados correctamente.',
                $id === null ? $this->expenses->all() : $this->expenses->find($id)
            );
        }
        if ($method === 'POST' && $id === null) {
            $user = AuthService::requireUser();
            ApiResponse::success('Gasto registrado correctamente.', $this->expenses->create((int) $user['id'], $body), 201);
        }
        if ($method === 'PUT' && $id !== null) {
            ApiResponse::success('Gasto actualizado correctamente.', $this->expenses->update($id, $body));
        }
        if ($method === 'DELETE' && $id !== null) {
            $this->expenses->delete($id);
            ApiResponse::success('Gasto eliminado correctamente.', ['id' => $id]);
        }

        ApiResponse::error('Método no permitido para este recurso.', 405);
    }
}
