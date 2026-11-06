USE stockflow;

CREATE TABLE expense_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

INSERT IGNORE INTO expense_categories (name)
SELECT DISTINCT COALESCE(NULLIF(TRIM(category), ''), 'Sin categoría')
FROM expenses;

ALTER TABLE expenses
    ADD COLUMN category_id INT UNSIGNED NULL AFTER user_id;

UPDATE expenses e
JOIN expense_categories c
    ON c.name = COALESCE(NULLIF(TRIM(e.category), ''), 'Sin categoría')
SET e.category_id = c.id;

ALTER TABLE expenses
    MODIFY category_id INT UNSIGNED NOT NULL,
    ADD CONSTRAINT fk_expenses_category FOREIGN KEY (category_id)
        REFERENCES expense_categories(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    ADD INDEX idx_expenses_category_date (category_id, expense_date),
    DROP COLUMN category;
