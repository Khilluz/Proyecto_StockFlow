USE stockflow;

ALTER TABLE users
    DROP FOREIGN KEY fk_users_role;

ALTER TABLE users
    ADD CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    ADD INDEX idx_users_role_active (role_id, active);

ALTER TABLE suppliers
    ADD INDEX idx_suppliers_name (name);

ALTER TABLE products
    DROP FOREIGN KEY fk_products_category,
    DROP FOREIGN KEY fk_products_supplier;

ALTER TABLE products
    DROP INDEX idx_products_stock,
    ADD CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    ADD CONSTRAINT fk_products_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    ADD CONSTRAINT chk_products_cost CHECK (cost >= 0),
    ADD CONSTRAINT chk_products_price CHECK (price >= 0),
    ADD CONSTRAINT chk_products_margin CHECK (price > cost),
    ADD CONSTRAINT chk_products_stock CHECK (stock >= 0),
    ADD CONSTRAINT chk_products_minimum_stock CHECK (minimum_stock >= 0),
    ADD INDEX idx_products_category (category_id),
    ADD INDEX idx_products_stock (active, stock, minimum_stock);

ALTER TABLE stock_movements
    DROP FOREIGN KEY fk_stock_product,
    DROP FOREIGN KEY fk_stock_user;

ALTER TABLE stock_movements
    ADD CONSTRAINT fk_stock_product FOREIGN KEY (product_id) REFERENCES products(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    ADD CONSTRAINT fk_stock_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    ADD CONSTRAINT chk_stock_movement_quantity CHECK (quantity <> 0),
    ADD CONSTRAINT chk_stock_movement_direction CHECK (
        (type = 'sale' AND quantity < 0)
        OR (type = 'purchase' AND quantity > 0)
        OR type = 'adjustment'
    ),
    ADD INDEX idx_stock_user_date (user_id, created_at);

ALTER TABLE sales
    DROP FOREIGN KEY fk_sales_user;

ALTER TABLE sales
    ADD CONSTRAINT fk_sales_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    ADD CONSTRAINT chk_sales_total CHECK (total >= 0),
    ADD INDEX idx_sales_user_date (user_id, created_at);

ALTER TABLE sale_items
    DROP FOREIGN KEY fk_sale_items_sale,
    DROP FOREIGN KEY fk_sale_items_product;

ALTER TABLE sale_items
    ADD CONSTRAINT fk_sale_items_sale FOREIGN KEY (sale_id) REFERENCES sales(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    ADD CONSTRAINT fk_sale_items_product FOREIGN KEY (product_id) REFERENCES products(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    ADD CONSTRAINT chk_sale_items_quantity CHECK (quantity > 0),
    ADD CONSTRAINT chk_sale_items_price CHECK (unit_price >= 0),
    ADD INDEX idx_sale_items_product (product_id);

ALTER TABLE expenses
    DROP FOREIGN KEY fk_expenses_user;

ALTER TABLE expenses
    ADD CONSTRAINT fk_expenses_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    ADD CONSTRAINT chk_expenses_amount CHECK (amount > 0),
    ADD INDEX idx_expenses_user_date (user_id, expense_date);
