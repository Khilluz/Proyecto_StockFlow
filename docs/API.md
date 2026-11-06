# API REST

## Contrato de respuesta

Todas las rutas de `api/` responden `Content-Type: application/json; charset=utf-8`, usando `JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES`.

```json
{"status":true,"message":"Operación realizada correctamente","data":{}}
```

Los errores usan la misma estructura, con `status: false` y `data: null`. Códigos: 200 consulta/actualización/borrado, 201 creación, 400 formato o validación, 401 sin sesión, 403 permisos/CSRF, 404 recurso ausente, 405 método no permitido y 500 error inesperado. Los errores técnicos se registran con `error_log()`; la respuesta externa es genérica.

## Endpoints

| Método | Ruta | Datos / respuesta | Roles |
|---|---|---|---|
| GET | `/api/products` | Lista de productos activos; `?q=` filtra nombre/categoría | Admin, vendedor, repositor |
| GET | `/api/products/{id}` | Producto por ID | Admin, vendedor, repositor |
| POST | `/api/products` | JSON: `name`, `category_id`, `unit`, `cost`, `price`, `stock`, `minimum_stock` | Admin, repositor |
| PUT | `/api/products/{id}` | Mismos campos que POST | Admin, repositor |
| DELETE | `/api/products/{id}` | Retiro lógico; conserva historial | Admin, repositor |
| GET | `/api/expenses` | Gastos con nombre de categoría y usuario | Admin |
| GET | `/api/expenses/{id}` | Gasto por ID | Admin |
| POST | `/api/expenses` | JSON: `category`, `description`, `amount`, `expense_date` (`YYYY-MM-DD`) | Admin |
| PUT | `/api/expenses/{id}` | Mismos campos que POST | Admin |
| DELETE | `/api/expenses/{id}` | Elimina el gasto | Admin |
| GET | `/api/sales` | Últimas 100 ventas | Admin, vendedor |
| GET | `/api/sales/{id}` | Venta y sus líneas | Admin, vendedor |
| POST | `/api/sales` | JSON: `channel` (`Presencial`, `Delivery`, `Online`) e `items: [{product_id, quantity}]` | Admin, vendedor |
| GET | `/api/stock-movements` | Últimos 100 movimientos | Admin, repositor |
| POST | `/api/stock-movements` | JSON: `product_id`, `quantity` positiva, `note` opcional | Admin, repositor |

Las rutas usan sustantivos plurales y el método HTTP determina la operación. No hay endpoint para borrar ventas o movimientos; son historia financiera/de inventario. El borrado de producto es lógico.

## Validación y consistencia

- Producto: nombre y unidad no vacíos y dentro de longitud; categoría activa; valores numéricos finitos, precio superior al costo, stock no negativo y límites compatibles con `DECIMAL`.
- Gasto: categoría y descripción obligatorias, importe positivo dentro de `DECIMAL(12,2)` y fecha calendario válida.
- Venta: canal permitido, 1–50 líneas, IDs positivos, cantidades positivas, producto activo y stock suficiente. El servicio bloquea productos en orden de ID con `SELECT ... FOR UPDATE`, guarda líneas, descuenta stock y registra movimientos dentro de una transacción.
- Reposición: cantidad positiva, producto activo, bloqueo de fila y actualización de saldo/movimiento atómicos.

Los endpoints de catálogo, gastos, ventas y movimientos requieren una sesión autenticada. El servidor autoriza cada rol; la interfaz no es una frontera de seguridad.

## CSRF y Fetch

Las páginas autenticadas y el login exponen el token de sesión en `<meta name="csrf-token">`. `assets/js/app.js` envía `X-CSRF-Token`, JSON serializado y `credentials: 'same-origin'` para mutaciones. El API verifica el token antes de la escritura.

## Configuración y autenticación

La aplicación lee primero las variables del proceso y luego `.env` (si existe). Crea el primer usuario solo desde CLI:

```powershell
$secure = Read-Host "Contraseña inicial (12+ caracteres)" -AsSecureString
$pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
try {
    $env:STOCKFLOW_INITIAL_PASSWORD = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer)
    php tools\create_user.php "admin@negocio.local" "Administrador" "Administrador"
} finally {
    Remove-Item Env:\STOCKFLOW_INITIAL_PASSWORD -ErrorAction SilentlyContinue
    [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer)
}
```

La contraseña se convierte a hash mediante `password_hash()` y la herramienta no está disponible por HTTP. La cuenta y contraseña no se almacenan en el repositorio. Usa las credenciales creadas por el operador; no hay credenciales iniciales publicadas.
