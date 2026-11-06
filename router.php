<?php

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (is_string($path) && preg_match('~(?:^|/)\.[^/]+(?:/|$)~', $path)) {
    http_response_code(404);
    exit;
}
if (is_string($path) && preg_match('~(?:^|/)api(?:/|$)~', $path)) {
    require __DIR__ . '/api/index.php';
    return true;
}

$file = is_string($path) ? __DIR__ . DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '';
if ($file !== '' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
