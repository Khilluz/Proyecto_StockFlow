<?php

use StockFlow\Core\DatabaseConnection;

require_once __DIR__ . '/environment.php';
require_once __DIR__ . '/autoload.php';

function db(): PDO
{
    return DatabaseConnection::getInstance();
}