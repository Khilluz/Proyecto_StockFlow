# Diagrama entidad-relación de StockFlow

Este diagrama describe el esquema final creado por `database/database.sql`. Las cardinalidades se expresan del lado de la entidad que participa en la relación; `supplier_id` es opcional en productos.

```mermaid
erDiagram
    ROLES ||--o{ USERS : asigna
    CATEGORIES ||--o{ PRODUCTS : clasifica
    SUPPLIERS o|--o{ PRODUCTS : abastece
    USERS ||--o{ SALES : registra
    SALES ||--|{ SALE_ITEMS : contiene
    PRODUCTS ||--o{ SALE_ITEMS : vendido_en
    USERS ||--o{ EXPENSES : registra
    EXPENSE_CATEGORIES ||--o{ EXPENSES : clasifica
    USERS ||--o{ STOCK_MOVEMENTS : registra
    PRODUCTS ||--o{ STOCK_MOVEMENTS : mueve

    ROLES {
        tinyint id PK
        varchar name UK
    }
    USERS {
        int id PK
        tinyint role_id FK
        varchar name
        varchar email UK
        varchar password_hash
        boolean active
        timestamp created_at
    }
    CATEGORIES {
        int id PK
        varchar name UK
        boolean active
    }
    EXPENSE_CATEGORIES {
        int id PK
        varchar name UK
        boolean active
    }
    SUPPLIERS {
        int id PK
        varchar name
        varchar email
        varchar phone
        boolean active
    }
    PRODUCTS {
        int id PK
        int category_id FK
        int supplier_id FK
        varchar name
        varchar unit
        decimal cost
        decimal price
        decimal stock
        decimal minimum_stock
        boolean active
        timestamp created_at
        timestamp updated_at
    }
    SALES {
        bigint id PK
        int user_id FK
        enum channel
        decimal total
        timestamp created_at
    }
    SALE_ITEMS {
        bigint id PK
        bigint sale_id FK
        int product_id FK
        decimal quantity
        decimal unit_price
        decimal subtotal "columna generada"
    }
    EXPENSES {
        bigint id PK
        int user_id FK
        int category_id FK
        varchar description
        decimal amount
        date expense_date
        timestamp created_at
    }
    STOCK_MOVEMENTS {
        bigint id PK
        int product_id FK
        int user_id FK
        enum type
        decimal quantity
        varchar note
        timestamp created_at
    }
```

`sale_items.subtotal` es una columna generada por `ROUND(quantity * unit_price, 2)`; se conserva como dato consultable sin permitir que se desincronice del precio y la cantidad de la transacción.
