<?php

use StockFlow\Core\CsrfManager;
use StockFlow\Core\SessionManager;
use StockFlow\Services\AuthService;
use StockFlow\DAO\UsuarioDAO;

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/autoload.php';

SessionManager::start();

function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money(float|int $value): string
{
    $amount = (float) $value;
    $decimals = $amount === round($amount) ? 0 : 2;
    return '$' . number_format($amount, $decimals, ',', '.');
}

function quantity(float|int|string|null $value): string
{
    return number_format((int) round((float) $value), 0, ',', '.');
}

function precise_quantity(float|int|string|null $value): string
{
    $formatted = number_format((float) $value, 3, ',', '.');
    return rtrim(rtrim($formatted, '0'), ',');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    return CsrfManager::token();
}

function verify_csrf(): void
{
    if (!CsrfManager::isValid($_POST['csrf'] ?? null)) {
        http_response_code(419);
        exit('La sesión del formulario expiró. Vuelve a intentarlo.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = compact('type', 'message');
}

function pull_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}

function current_user(): ?array
{
    return AuthService::currentUser();
}

function require_auth(): array
{
    $user = current_user();
    if (!$user) {
        redirect('index.php');
    }
    $userId = filter_var($user['id'] ?? null, FILTER_VALIDATE_INT);
    if (!is_int($userId) || $userId < 1) {
        SessionManager::destroy();
        redirect('index.php');
    }
    try {
        if (!(new UsuarioDAO(db()))->isActive($userId)) {
            SessionManager::destroy();
            redirect('index.php');
        }
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        http_response_code(503);
        exit('No se pudo validar la sesión. Inténtalo nuevamente.');
    }
    return $user;
}

function require_role(array $roles): array
{
    $user = require_auth();
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('No tienes permisos para acceder a esta sección.');
    }
    return $user;
}