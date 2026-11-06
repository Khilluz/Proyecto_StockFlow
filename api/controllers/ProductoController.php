<?php

declare(strict_types=1);

namespace StockFlow\Api\Controllers;

use StockFlow\Core\ApiResponse;
use StockFlow\Services\AuthService;
use StockFlow\Services\ProductoService;

final class ProductoController
{
    public function __construct(private readonly ProductoService $products)
    {
    }

    public function dispatch(string $method, ?int $id, array $body): never
    {
        $roles = ['Administrador', 'Vendedor', 'Repositor'];
        AuthService::requireRole($roles);

        if ($method === 'GET') {
            $search = $_GET['q'] ?? '';
            if (!is_string($search)) {
                \StockFlow\Core\ApiResponse::error('El filtro de búsqueda no es válido.', 400);
            }
            ApiResponse::success(
                'Productos consultados correctamente.',
                $id === null ? $this->products->all($search) : $this->products->find($id)
            );
        }

        AuthService::requireRole(['Administrador', 'Repositor']);
        $user = AuthService::requireUser();
        if ($method === 'POST' && $id === null) {
            ApiResponse::success('Producto creado correctamente.', $this->products->create((int) $user['id'], $body), 201);
        }
        if ($method === 'PUT' && $id !== null) {
            ApiResponse::success('Producto actualizado correctamente.', $this->products->update($id, (int) $user['id'], $body));
        }
        if ($method === 'DELETE' && $id !== null) {
            $this->products->archive($id);
            ApiResponse::success('Producto retirado del inventario.', ['id' => $id]);
        }

        ApiResponse::error('Método no permitido para este recurso.', 405);
    }
}
