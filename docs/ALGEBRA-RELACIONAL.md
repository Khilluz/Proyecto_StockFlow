# Álgebra relacional

Las expresiones usan `σ` para selección, `π` para proyección y `⋈` para un join theta. Los nombres de atributos se califican por relación cuando es necesario. Las expresiones corresponden a las tablas del DDL final.

## 1. Productos que requieren reposición

**Descripción:** obtener los productos activos cuyo stock es igual o inferior al mínimo.

**Álgebra relacional:**

```text
π_{id, name, stock, minimum_stock}
  (σ_{active = 1 ∧ stock ≤ minimum_stock}(PRODUCTS))
```

**SQL equivalente:**

```sql
SELECT id, name, stock, minimum_stock
FROM products
WHERE active = 1 AND stock <= minimum_stock
ORDER BY stock;
```

**Operadores:** `σ` limita las filas a productos activos bajo el mínimo; `π` conserva solo los atributos solicitados.

## 2. Ventas realizadas por una persona usuaria

**Descripción:** listar la fecha, total y canal de ventas de un usuario, incluyendo su nombre.

**Álgebra relacional:**

```text
π_{SALES.id, USERS.name, SALES.channel, SALES.total, SALES.created_at}
  (σ_{USERS.id = :user_id}
    (SALES ⋈_{SALES.user_id = USERS.id} USERS))
```

**SQL equivalente:**

```sql
SELECT s.id, u.name, s.channel, s.total, s.created_at
FROM sales AS s
JOIN users AS u ON u.id = s.user_id
WHERE u.id = :user_id
ORDER BY s.created_at DESC;
```

**Operadores:** el join theta `⋈` relaciona cada venta con su usuario por la FK; `σ` aplica el filtro de usuario y `π` proyecta las columnas de salida.

## 3. Gastos por categoría

**Descripción:** obtener los gastos de una categoría con descripción, importe y fecha.

**Álgebra relacional:**

```text
π_{EXPENSES.id, EXPENSE_CATEGORIES.name, EXPENSES.description,
   EXPENSES.amount, EXPENSES.expense_date}
  (σ_{EXPENSE_CATEGORIES.name = :category}
    (EXPENSES ⋈_{EXPENSES.category_id = EXPENSE_CATEGORIES.id}
       EXPENSE_CATEGORIES))
```

**SQL equivalente:**

```sql
SELECT e.id, c.name AS category, e.description, e.amount, e.expense_date
FROM expenses AS e
JOIN expense_categories AS c ON c.id = e.category_id
WHERE c.name = :category
ORDER BY e.expense_date DESC;
```

**Operadores:** el join theta combina cada gasto con una categoría; `σ` selecciona la categoría pedida y `π` entrega los atributos del reporte.
