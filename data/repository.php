<?php

function dashboard_metrics(PDO $pdo): array
{
    return [
        ['label' => 'Ventas del mes', 'value' => (float) $pdo->query("SELECT COALESCE(SUM(total), 0) FROM sales WHERE created_at >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')")->fetchColumn(), 'tone' => 'teal'],
        ['label' => 'Productos activos', 'value' => (int) $pdo->query('SELECT COUNT(*) FROM products WHERE active = 1')->fetchColumn(), 'tone' => 'blue'],
        ['label' => 'Gastos del mes', 'value' => (float) $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE expense_date >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')")->fetchColumn(), 'tone' => 'amber'],
        ['label' => 'Margen promedio', 'value' => (float) $pdo->query('SELECT COALESCE(AVG((price - cost) / NULLIF(price, 0) * 100), 0) FROM products WHERE active = 1')->fetchColumn(), 'tone' => 'coral'],
    ];
}

function products(PDO $pdo, string $search = ''): array
{
    $query = 'SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.active = 1';
    $params = [];
    if ($search !== '') {
        $query .= ' AND (p.name LIKE :search OR c.name LIKE :search)';
        $params['search'] = '%' . $search . '%';
    }
    $query .= ' ORDER BY p.name';
    $statement = $pdo->prepare($query);
    $statement->execute($params);
    return $statement->fetchAll();
}

function categories(PDO $pdo): array
{
    return $pdo->query('SELECT id, name FROM categories WHERE active = 1 ORDER BY name')->fetchAll();
}

function product(PDO $pdo, int $id): ?array
{
    $statement = $pdo->prepare('SELECT * FROM products WHERE id = :id AND active = 1');
    $statement->execute(['id' => $id]);
    return $statement->fetch() ?: null;
}

function low_stock(PDO $pdo): array
{
    return $pdo->query('SELECT p.name, p.stock, p.minimum_stock FROM products p WHERE p.active = 1 AND p.stock <= p.minimum_stock ORDER BY p.stock')->fetchAll();
}

function expenses(PDO $pdo): array
{
    return $pdo->query('SELECT e.*, u.name AS user_name FROM expenses e JOIN users u ON u.id = e.user_id ORDER BY e.expense_date DESC, e.id DESC')->fetchAll();
}