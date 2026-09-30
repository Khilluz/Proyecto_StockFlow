CREATE DATABASE IF NOT EXISTS stockflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stockflow;
SET NAMES utf8mb4;

CREATE TABLE roles (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(40) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id TINYINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE suppliers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(40) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    supplier_id INT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    unit VARCHAR(30) NOT NULL DEFAULT 'unidad',
    cost DECIMAL(12,2) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    stock DECIMAL(12,3) NOT NULL DEFAULT 0,
    minimum_stock DECIMAL(12,3) NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id),
    CONSTRAINT fk_products_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    INDEX idx_products_name (name),
    INDEX idx_products_stock (stock, minimum_stock)
) ENGINE=InnoDB;

CREATE TABLE stock_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    type ENUM('purchase','sale','adjustment') NOT NULL,
    quantity DECIMAL(12,3) NOT NULL,
    note VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_stock_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_stock_user FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_stock_product_date (product_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE sales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    channel ENUM('Presencial','Delivery','Online') NOT NULL DEFAULT 'Presencial',
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sales_user FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_sales_date (created_at)
) ENGINE=InnoDB;

CREATE TABLE sale_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_id BIGINT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity DECIMAL(12,3) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_sale_items_sale FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    CONSTRAINT fk_sale_items_product FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_sale_items_sale (sale_id)
) ENGINE=InnoDB;

CREATE TABLE expenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    category VARCHAR(80) NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    expense_date DATE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_expenses_user FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_expenses_date (expense_date)
) ENGINE=InnoDB;

INSERT INTO roles (name) VALUES ('Administrador'), ('Vendedor'), ('Repositor');
INSERT INTO users (role_id, name, email, password_hash) VALUES
    (1, 'Admin StockFlow', 'admin@stockflow.local', '$2y$10$hwvqL8iaYqWbNngAN6MiZuxlAwW0v6Cc/p7MMB1rcRRC3ohdngNA.'),
    (2, 'Vendedor Demo', 'vendedor@stockflow.local', '$2y$10$XXoK5wCOpz08E5iwPv6rqO9a3iY38aZERnraqWtv7lXxuAYz0VTUC'),
    (3, 'Repositor Demo', 'repositor@stockflow.local', '$2y$10$n3YmOvI74A8QIi0xdZb0ae0qCFVvNi/Rgy5uBk7wwdInCT4BlYdn2');
INSERT INTO categories (name) VALUES ('Bebidas'), ('Almacén');
INSERT INTO suppliers (name, email, phone) VALUES ('Distribuidora Central', 'compras@distribuidora.local', '555-0100');
INSERT INTO products (category_id, supplier_id, name, unit, cost, price, stock, minimum_stock) VALUES
    (1, 1, 'Café Premium', 'unidad', 8.50, 12.50, 4, 5),
    (2, 1, 'Yerba Mate 1 kg', 'unidad', 4.20, 6.90, 28, 10),
    (2, 1, 'Galletas de avena', 'paquete', 2.10, 5.00, 3, 8);
INSERT INTO expenses (user_id, category, description, amount, expense_date) VALUES
    (1, 'Servicios', 'Internet del local', 4200.00, CURRENT_DATE),
    (1, 'Insumos', 'Bolsas compostables', 1850.00, CURRENT_DATE);