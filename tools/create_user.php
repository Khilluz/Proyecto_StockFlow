<?php

declare(strict_types=1);

use StockFlow\DAO\UsuarioDAO;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if ($argc !== 4) {
    fwrite(STDERR, "Uso: php tools/create_user.php <email> <nombre> <Administrador|Vendedor|Repositor>\n");
    exit(2);
}

[$script, $email, $name, $roleName] = $argv;
$password = getenv('STOCKFLOW_INITIAL_PASSWORD');
if (!is_string($password) || strlen($password) < 12) {
    fwrite(STDERR, "Define STOCKFLOW_INITIAL_PASSWORD con una contraseña de al menos 12 caracteres.\n");
    exit(2);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || trim($name) === '' || strlen($name) > 100) {
    fwrite(STDERR, "El email o nombre no es válido.\n");
    exit(2);
}

require_once dirname(__DIR__) . '/config/database.php';
$pdo = \StockFlow\Core\DatabaseConnection::getInstance();
$users = new UsuarioDAO($pdo);
$roles = $users->listRoles();
$roleId = null;
foreach ($roles as $role) {
    if ($role['name'] === $roleName) {
        $roleId = (int) $role['id'];
        break;
    }
}
if ($roleId === null) {
    fwrite(STDERR, "El rol indicado no existe. Importa primero database/database.sql.\n");
    exit(2);
}

try {
    $users->create($roleId, trim($name), strtolower(trim($email)), password_hash($password, PASSWORD_DEFAULT));
    fwrite(STDOUT, "Usuario creado correctamente.\n");
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    fwrite(STDERR, "No se pudo crear el usuario. Verifica si el email ya existe.\n");
    exit(1);
} finally {
    unset($password);
}
