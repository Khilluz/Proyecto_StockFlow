# Arquitectura y programación orientada a objetos

## Capas

1. **Presentación:** páginas PHP (`dashboard.php`, `inventario.php`, `gastos.php`, `vendedor.php`, `repositor.php`, `reportes.php`, `usuarios.php`), componentes `includes/` y recursos `assets/`. Las páginas generan la vista inicial; `assets/js/app.js` consume la API para las mutaciones y refresca las tablas sin navegación completa.
2. **Lógica de negocio:** clases de `app/Services/` validan campos, fechas, importes, roles, stock, canales y reglas de venta/reposición. `VentaService` coordina bloqueos de producto, detalles, movimientos y total dentro de una transacción.
3. **Datos e infraestructura:** `app/DAO/` contiene las consultas preparadas; `DatabaseConnection` configura PDO con excepciones y prepares nativos. Los DAO reciben PDO por constructor y las transacciones se coordinan desde servicios.

## Patrón DAO

Los controladores de `api/controllers/` interpretan el recurso y el método HTTP, llaman servicios y transforman el resultado a través de `ApiResponse`. No ejecutan SQL. Los servicios no conocen consultas; delegan persistencia en DAO. Las vistas no abren PDO ni contienen consultas.

DAOs implementados:

- `ProductoDAO`: catálogo, categorías, stock y archivo lógico.
- `GastoDAO`: gastos, rangos de fecha y alta controlada de categorías.
- `VentaDAO`: cabecera, detalle, consulta e historial.
- `MovimientoStockDAO`: trazabilidad de entradas/salidas.
- `UsuarioDAO`: autenticación, roles y usuarios.
- `DashboardDAO` y `ReportDAO`: lecturas de indicadores y reportes.

Servicios implementados: `AuthService`, `ProductoService`, `GastoService`, `VentaService` y `ReposicionService`.

## Infraestructura y entidades

- `DatabaseConnection` mantiene una instancia PDO compartida; usa `ERRMODE_EXCEPTION`, `FETCH_ASSOC` y `EMULATE_PREPARES=false`.
- `SessionManager` centraliza las opciones de cookie, regeneración y cierre de sesión.
- `CsrfManager` genera y compara el token de sesión.
- `Usuario`, `Producto`, `Venta` y `Gasto` encapsulan datos de dominio y exponen los valores que necesitan sus servicios/DAO.
- `ApiException` y `ApiResponse` separan fallos HTTP de detalles internos y normalizan el JSON.
- Un autoloader PSR-4 pequeño evita una dependencia de Composer.

## Límites deliberados

Los formularios de gestión de usuarios siguen usando POST de página y su DAO, porque no forman parte del CRUD REST solicitado. Los datos iniciales de lectura aún se renderizan en PHP mediante DAO. Los CRUD de productos y gastos, la venta y la reposición se realizan mediante Fetch.
