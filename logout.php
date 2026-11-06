<?php
use StockFlow\Core\SessionManager;

require_once __DIR__ . '/config/bootstrap.php';
SessionManager::destroy();
redirect('index.php');