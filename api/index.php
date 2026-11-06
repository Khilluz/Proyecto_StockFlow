<?php

declare(strict_types=1);

use StockFlow\Api\Controllers\GastoController;
use StockFlow\Api\Controllers\MovimientoStockController;
use StockFlow\Api\Controllers\ProductoController;
use StockFlow\Api\Controllers\VentaController;
use StockFlow\Core\ApiException;
use StockFlow\Core\ApiResponse;
use StockFlow\Core\CsrfManager;
use StockFlow\Core\SessionManager;
use StockFlow\DAO\GastoDAO;
use StockFlow\DAO\MovimientoStockDAO;
use StockFlow\DAO\ProductoDAO;
use StockFlow\DAO\VentaDAO;
use StockFlow\Services\GastoService;
use StockFlow\Services\ProductoService;
use StockFlow\Services\ReposicionService;
use StockFlow\Services\VentaService;
use StockFlow\Services\AuthService;

ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);
require_once dirname(__DIR__) . '/config/database.php';
SessionManager::start();

try {
    $path = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '');
    if (!preg_match('~(?:^|/)api(?:/(.*))?$~', $path, $matches)) {
        throw new ApiException('No se encontró el recurso solicitado.', 404);
    }
    $segments = array_values(array_filter(explode('/', $matches[1] ?? ''), static fn (string $part): bool => $part !== ''));
    if (count($segments) < 1 || count($segments) > 2) {
        throw new ApiException('No se encontró el recurso solicitado.', 404);
    }

    $resource = $segments[0];
    $id = null;
    if (isset($segments[1])) {
        if (!preg_match('/^[1-9][0-9]*$/', $segments[1]) || filter_var($segments[1], FILTER_VALIDATE_INT) === false) {
            throw new ApiException('El identificador del recurso no es válido.', 400);
        }
        $id = (int) $segments[1];
    }

    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    AuthService::requireUser();
    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)
        && !CsrfManager::isValid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
        throw new ApiException('El token de seguridad expiró. Actualiza la página e inténtalo nuevamente.', 403);
    }

    $body = [];
    if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
        $rawBody = file_get_contents('php://input');
        $decoded = json_decode($rawBody === false ? '' : $rawBody, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw new ApiException('El cuerpo de la solicitud debe ser un objeto JSON válido.', 400);
        }
        $body = $decoded;
    }

    $pdo = \StockFlow\Core\DatabaseConnection::getInstance();
    $products = new ProductoDAO($pdo);
    $expenses = new GastoDAO($pdo);
    $sales = new VentaDAO($pdo);
    $movements = new MovimientoStockDAO($pdo);

    switch ($resource) {
        case 'products':
            (new ProductoController(new ProductoService($pdo, $products, $movements)))->dispatch($method, $id, $body);
        case 'expenses':
            (new GastoController(new GastoService($expenses)))->dispatch($method, $id, $body);
        case 'sales':
            (new VentaController(new VentaService($pdo, $products, $sales, $movements)))->dispatch($method, $id, $body);
        case 'stock-movements':
            (new MovimientoStockController(new ReposicionService($pdo, $products, $movements)))->dispatch($method, $id, $body);
        default:
            throw new ApiException('No se encontró el recurso solicitado.', 404);
    }
} catch (ApiException $exception) {
    ApiResponse::error($exception->getMessage(), $exception->httpStatus);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    ApiResponse::error('No se pudo completar la operación. Inténtalo nuevamente.', 500);
}
