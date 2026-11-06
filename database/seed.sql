USE stockflow;
SET NAMES utf8mb4;

INSERT INTO roles (name) VALUES ('Administrador'), ('Vendedor'), ('Repositor')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO categories (name) VALUES ('Bebidas'), ('Almacén')
ON DUPLICATE KEY UPDATE active = 1;

INSERT INTO expense_categories (name) VALUES ('Servicios'), ('Insumos')
ON DUPLICATE KEY UPDATE active = 1;

INSERT INTO suppliers (name, email, phone)
SELECT 'Distribuidora Central', 'compras@distribuidora.local', '555-0100'
WHERE NOT EXISTS (
    SELECT 1 FROM suppliers WHERE name = 'Distribuidora Central'
);

INSERT INTO products
    (category_id, supplier_id, name, unit, cost, price, stock, minimum_stock)
SELECT c.id, s.id, 'Café Premium', 'unidad', 8.50, 12.50, 10, 5
FROM categories c
CROSS JOIN suppliers s
WHERE c.name = 'Bebidas'
  AND s.name = 'Distribuidora Central'
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Café Premium');

INSERT INTO products
    (category_id, supplier_id, name, unit, cost, price, stock, minimum_stock)
SELECT c.id, s.id, 'Yerba Mate 1 kg', 'unidad', 4.20, 6.90, 28, 10
FROM categories c
CROSS JOIN suppliers s
WHERE c.name = 'Almacén'
  AND s.name = 'Distribuidora Central'
  AND NOT EXISTS (SELECT 1 FROM products WHERE name = 'Yerba Mate 1 kg');

SET @stockflow_user_id := (
    SELECT id FROM users WHERE active = 1 ORDER BY id LIMIT 1
);
SET @coffee_id := (SELECT id FROM products WHERE name = 'Café Premium' ORDER BY id LIMIT 1);
SET @coffee_price := (SELECT price FROM products WHERE id = @coffee_id);
SET @yerba_id := (SELECT id FROM products WHERE name = 'Yerba Mate 1 kg' ORDER BY id LIMIT 1);

INSERT INTO stock_movements (product_id, user_id, type, quantity, note)
SELECT @coffee_id, @stockflow_user_id, 'adjustment', 10, 'Stock inicial de demostración'
WHERE @stockflow_user_id IS NOT NULL
  AND @coffee_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM stock_movements
      WHERE product_id = @coffee_id AND note = 'Stock inicial de demostración'
  );

INSERT INTO stock_movements (product_id, user_id, type, quantity, note)
SELECT @yerba_id, @stockflow_user_id, 'adjustment', 28, 'Stock inicial de demostración'
WHERE @stockflow_user_id IS NOT NULL
  AND @yerba_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM stock_movements
      WHERE product_id = @yerba_id AND note = 'Stock inicial de demostración'
  );

INSERT INTO sales (user_id, channel, total)
SELECT @stockflow_user_id, 'Presencial', ROUND(@coffee_price * 2, 2)
WHERE @stockflow_user_id IS NOT NULL
  AND @coffee_id IS NOT NULL
  AND @coffee_price IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM sales
      WHERE user_id = @stockflow_user_id
        AND channel = 'Presencial'
        AND total = ROUND(@coffee_price * 2, 2)
        AND DATE(created_at) = CURRENT_DATE
  );
SET @sample_sale_created := ROW_COUNT();
SET @sample_sale_id := IF(@sample_sale_created > 0, LAST_INSERT_ID(), NULL);

INSERT INTO sale_items (sale_id, product_id, quantity, unit_price)
SELECT @sample_sale_id, @coffee_id, 2, @coffee_price
WHERE @sample_sale_id IS NOT NULL AND @coffee_id IS NOT NULL AND @coffee_price IS NOT NULL;

UPDATE products SET stock = stock - 2 WHERE id = @coffee_id AND @sample_sale_id IS NOT NULL;

INSERT INTO stock_movements (product_id, user_id, type, quantity, note)
SELECT @coffee_id, @stockflow_user_id, 'sale', -2, CONCAT('Venta #', @sample_sale_id)
WHERE @sample_sale_id IS NOT NULL AND @coffee_id IS NOT NULL;

INSERT INTO expenses (user_id, category_id, description, amount, expense_date)
SELECT @stockflow_user_id, c.id, 'Servicio de internet de demostración', 4200.00, CURRENT_DATE
FROM expense_categories c
WHERE @stockflow_user_id IS NOT NULL
  AND c.name = 'Servicios'
  AND NOT EXISTS (
      SELECT 1 FROM expenses
      WHERE description = 'Servicio de internet de demostración'
        AND expense_date = CURRENT_DATE
  );
