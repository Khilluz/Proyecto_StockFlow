<?php

use StockFlow\DAO\DashboardDAO;
use StockFlow\DAO\GastoDAO;
use StockFlow\DAO\ProductoDAO;

function dashboard_metrics(PDO $pdo): array
{
    return (new DashboardDAO($pdo))->metrics();
}

function products(PDO $pdo, string $search = ''): array
{
    return (new ProductoDAO($pdo))->all($search);
}

function categories(PDO $pdo): array
{
    return (new ProductoDAO($pdo))->categories();
}

function product(PDO $pdo, int $id): ?array
{
    return (new ProductoDAO($pdo))->find($id);
}

function low_stock(PDO $pdo): array
{
    return (new ProductoDAO($pdo))->lowStock();
}

function expenses(PDO $pdo): array
{
    return (new GastoDAO($pdo))->all();
}

function recent_sales(PDO $pdo): array
{
    return (new \StockFlow\DAO\VentaDAO($pdo))->recent();
}