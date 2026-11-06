# Informe técnico — StockFlow

**Empresa:** Nexosoft  
**Integrantes:** Williams Anderson, Mateo Pastrana, Kevin Ramos
**Versión:** Refactorización para la entrega final

## Objetivo e introducción

StockFlow busca centralizar inventario, ventas, reposiciones y gastos en una aplicación web para pequeños y medianos negocios. Esta entrega introduce capas explícitas de dominio y persistencia, una API REST JSON y operaciones de interfaz con Fetch, conservando las pantallas PHP y el esquema relacional existente como base.

## Tecnologías

PHP 8.1+, PDO, MySQL/MariaDB, HTML5, CSS propio responsive, JavaScript Fetch y sesiones web. El checkout inspeccionado no contiene Bootstrap como dependencia de ejecución; no se presenta como tecnología activa. La API puede ejecutarse con Apache/Laragon o con el servidor PHP integrado y `router.php`.

## Arquitectura y POO

La capa de presentación incluye las páginas, plantillas e `assets/`. La capa de negocio se encuentra en `app/Services/`, que valida y coordina operaciones. La capa de persistencia (`app/DAO/`) contiene SQL preparado y recibe PDO por inyección. `DatabaseConnection`, `SessionManager`, `AuthService`, `CsrfManager` y `ApiResponse` centralizan infraestructura. El patrón DAO separa consultas de servicios/controladores; las clases de dominio son `Usuario`, `Producto`, `Venta` y `Gasto`. El detalle está en [Arquitectura y POO](./ARQUITECTURA.md).

## API REST y Fetch

`api/index.php` resuelve rutas por recurso e ID; los controladores convierten HTTP en llamadas de servicio. `ApiResponse` genera solo JSON con estados HTTP y estructura uniforme. Productos y gastos ofrecen CRUD real; ventas y movimientos de stock tienen lectura/registro sin endpoints destructivos para su historial. `assets/js/app.js` convierte formularios a JSON, envía sesión/CSRF y modifica tablas/totales sin recarga. Ver [API REST](./API.md).

## Persistencia y seguridad

PDO usa excepciones, `utf8mb4` y prepares nativos. Las contraseñas nuevas usan `password_hash()` y login usa `password_verify()`. Las sesiones regeneran el identificador al autenticar y marcan cookies `HttpOnly`, `SameSite=Lax` y `Secure` bajo HTTPS. Mutaciones de API verifican CSRF; los servicios validan datos y roles en servidor. Venta y reposición bloquean productos y agrupan cambios de stock, detalle y movimiento en transacciones con rollback. Los errores técnicos se registran en el servidor y no se devuelven al cliente.

La tabla de categorías de gastos se normaliza y migra preservando los valores históricos. Los subtotales de venta pasan a ser generados para evitar inconsistencias. Los cambios de integridad e índices están incluidos en una tercera migración. Los scripts de base de datos, dependencias y políticas FK están en [Base de datos y normalización](./BASE-DE-DATOS.md); el diagrama editable/renderizable está en [Diagrama ER](./DIAGRAMA-ER.md).

## Diseño y responsive

Se conservó la paleta/capas existentes y se añadió una composición de login de escritorio desde 1024 px en `assets/css/responsive.css`: tarjeta y panel complementario en columnas, con anchuras, espacios y jerarquía tipográfica limitadas. Los breakpoints móviles existentes se mantienen. No se pudo hacer una comprobación visual en navegador en este entorno; el resultado requiere revisión a 375, 576, 768, 1024 y 1440 px.

## Pruebas y estado de validación

Se revisaron las páginas, includes, JS, CSS, configuración, SQL, consultas, roles y estado Git antes de editar. El entorno de esta ejecución no tiene `php`, MySQL/MariaDB ni Laragon disponibles en PATH; por ello no se pudieron ejecutar `php -l`, importar/migrar la base, probar HTTP contra PDO ni medir visualmente la UI. Los resultados ejecutables de validación disponibles se enumeran en el informe de finalización. Estas pruebas pendientes no se declaran como aprobadas.

## Correcciones realizadas respecto a la segunda entrega

| Observación anterior | Corrección incorporada en esta versión |
|---|---|
| Ausencia de POO | Se añadieron clases de dominio, infraestructura, DAO y servicios con responsabilidades separadas. |
| Ausencia de patrón de diseño | DAO implementado y documentado. |
| Sin API REST | Rutas JSON para productos, gastos, ventas y movimientos. |
| Frontend y backend acoplados | Los formularios de productos/gastos/ventas/reposición se conectan mediante Fetch. |
| Ventas incompletas | Servicio transaccional con bloqueo de stock, líneas, total y movimientos. |
| Falta de diagrama ER | Diagrama Mermaid actualizado al DDL final. |
| Falta de normalización | Categorías de gastos se separan; migración conserva valores anteriores. |
| Sin DDL/DML documentado | DDL limpio, seed y migraciones se separan y explican. |
| `.gitignore` vacío | Se excluyen secretos, logs, dependencias y archivos locales del IDE. |
| Credenciales publicadas | Se retiraron del README/DDL; cuentas se crean en CLI. Las credenciales históricas deben rotarse. |
| Mensajes técnicos | La API registra detalles internamente y responde mensajes genéricos. |
| Login de escritorio | Se agregó un layout alineado con las variables y componentes actuales; validación visual pendiente. |

## Álgebra relacional

Se incluyen consultas de stock bajo, ventas por usuario y gastos por categoría con `σ`, `π`, join theta y equivalentes SQL en [Álgebra relacional](./ALGEBRA-RELACIONAL.md).

## Conclusiones

La base de código incorpora una separación de capas y rutas transaccionales más seguras para operaciones relevantes, además de documentación reproducible y migraciones. La validación funcional completa depende de un entorno PHP/MySQL configurado; no debe considerarse aceptada hasta ejecutar las pruebas pendientes descritas en el README y el checklist.

## Referencias (APA 7.ª edición)

- MDN contributors. (s. f.). *Fetch API*. MDN Web Docs. https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API
- MariaDB Foundation. (s. f.). *Generated columns*. MariaDB Server Documentation. https://mariadb.com/kb/en/generated-columns/
- Oracle. (s. f.). *MySQL 8.4 Reference Manual: CHECK constraints*. https://dev.mysql.com/doc/refman/8.4/en/create-table-check-constraints.html
- Oracle. (s. f.). *MySQL 8.4 Reference Manual: InnoDB and foreign keys*. https://dev.mysql.com/doc/refman/8.4/en/innodb-foreign-key-constraints.html
- OWASP Foundation. (s. f.). *Cross-site Request Forgery Prevention Cheat Sheet*. https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html
- PHP Documentation Group. (s. f.). *PDO*. PHP Manual. https://www.php.net/manual/en/book.pdo.php
- PHP Documentation Group. (s. f.). *password_hash*. PHP Manual. https://www.php.net/manual/en/function.password-hash.php
- Sommerville, I. (2015). *Software engineering* (10th ed.). Pearson.
