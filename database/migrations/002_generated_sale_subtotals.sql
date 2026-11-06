USE stockflow;

ALTER TABLE sale_items
    DROP COLUMN subtotal,
    ADD COLUMN subtotal DECIMAL(12,2)
        GENERATED ALWAYS AS (ROUND(quantity * unit_price, 2)) STORED;
