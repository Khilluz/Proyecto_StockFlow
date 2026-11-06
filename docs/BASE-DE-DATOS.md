# Base de datos, migraciones y normalización

## Scripts

- `database/database.sql` crea desde cero la base `stockflow`, sus diez tablas, claves, índices, restricciones y relaciones. No crea cuentas con claves conocidas.
- `database/seed.sql` agrega datos operativos de demostración. Debe ejecutarse después de crear al menos una cuenta de usuario con la herramienta protegida por CLI.
- `database/migrations/001_normalize_expense_categories.sql` transforma la categoría histórica de gastos en una relación y copia cada valor existente antes de retirar la columna anterior.
- `database/migrations/002_generated_sale_subtotals.sql` reemplaza el subtotal almacenado por una columna generada equivalente; el valor se recalcula de `quantity` y `unit_price`.
- `database/migrations/003_constraints_and_indexes.sql` alinea las políticas FK, restricciones e índices adicionales del esquema final. Las restricciones pueden fallar si datos históricos incumplen las reglas; ante ese caso, detén la migración y corrige/justifica los datos sin borrarlos.

Las migraciones se ejecutan una sola vez y en orden sobre la instalación existente; hacen cambios DDL que MySQL/MariaDB puede confirmar implícitamente. Haz una copia de seguridad antes de aplicarlas. No borres tablas ni registros. El script de instalación desde cero ya incorpora el esquema final; no ejecutes las migraciones sobre esa instalación.

La versión final usa `expense_categories` y `expenses.category_id`, y una columna generada `sale_items.subtotal`. Se recomienda MySQL 8.0.16+ o MariaDB compatible con restricciones `CHECK` y columnas generadas; la conexión usa PDO sin prepares emulados.

## Normalización hasta 3FN

### Primera forma normal

Cada columna representa un valor atómico. Los productos, usuarios, categorías, ventas, detalles, gastos y movimientos se registran en filas individuales; no se guardan listas de artículos o categorías dentro de una columna.

### Segunda forma normal

Las entidades usan claves primarias simples. Cada atributo no clave depende de la clave completa de su fila. En particular, el detalle de venta es una entidad independiente (`sale_items`) relacionada con una sola venta y un producto; no almacena atributos del usuario o de la categoría del producto.

### Tercera forma normal

- `users`: `id → role_id, name, email, password_hash, active, created_at`; el nombre del rol vive en `roles`, no se duplica por usuario.
- `products`: `id → category_id, supplier_id, name, unit, cost, price, stock, minimum_stock, active, timestamps`; la descripción de categoría y proveedor se conserva en sus entidades relacionadas.
- `sales`: `id → user_id, channel, total, created_at`.
- `sale_items`: `id → sale_id, product_id, quantity, unit_price`; `subtotal` se genera de la cantidad y el precio unitario para evitar valores discordantes.
- `expenses`: `id → user_id, category_id, description, amount, expense_date, created_at`. La categoría se normaliza en `expense_categories`, evitando repetir texto y anomalías al renombrar o agrupar categorías.
- `stock_movements`: `id → product_id, user_id, type, quantity, note, created_at`.
- `roles`, `categories`, `expense_categories` y `suppliers` mantienen sus atributos descriptivos en una sola tabla.

Las claves foráneas tienen `RESTRICT` para impedir borrar usuarios, categorías o productos referenciados por historia. La desactivación de un producto es lógica (`active = 0`). Un proveedor puede eliminarse sin perder productos mediante `ON DELETE SET NULL`. Los detalles se eliminan únicamente junto a una venta, aunque la aplicación no expone un endpoint de borrado de ventas.

`products.stock` representa el saldo operativo actual y se modifica en la misma transacción que registra el movimiento de inventario. `sales.total` es un total monetario de cabecera guardado en la venta, calculado por el servicio a partir de sus líneas dentro de la transacción.

## Integridad

Las tablas usan InnoDB. Montos usan `DECIMAL`, existencias y cantidades usan tres decimales, las relaciones tienen tipos compatibles y existen índices para búsqueda, fechas y claves de relación. Las reglas `CHECK` del DDL complementan —no sustituyen— la validación del servicio; los formularios y servicios validan también longitud, formato, fechas, stock y permisos.

`database/seed.sql` incluye ejemplos de productos, gasto, venta, detalles y movimientos. No inserta hashes ni contraseñas estáticas. `tools/create_user.php` inserta la cuenta que la semilla utiliza y aplica `password_hash()`; la creación de usuarios queda así en una operación PHP y no en un hash SQL estático. Ejecuta la semilla una sola vez en una base de demostración vacía.
