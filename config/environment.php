<?php

declare(strict_types=1);

$environmentFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
if (is_file($environmentFile) && is_readable($environmentFile)) {
    foreach (file($environmentFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if (!preg_match('/^STOCKFLOW_DB_(HOST|PORT|NAME|USER|PASSWORD)$/', $key) || getenv($key) !== false) {
            continue;
        }
        if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'")
            && $value[0] === $value[strlen($value) - 1]) {
            $value = substr($value, 1, -1);
        }
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}
