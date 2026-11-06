# Checklist de aceptación de la entrega

## Estados

- **COMPLETADO**: implementado y verificado en el entorno disponible.
- **PARCIAL**: implementado, pero falta una prueba de runtime, de integración o visual.
- **PENDIENTE**: no implementado o sin evidencia suficiente para darlo por completo.

La ausencia de PHP, MySQL/MariaDB y navegador automatizado en este entorno impide certificar las pruebas de integración descritas al final. No se marcan como verificadas únicamente por existir código o documentación.

## A. Auditoría y compatibilidad

- **COMPLETADO** — Se revisó el estado de Git antes de editar; se conservaron los cambios preexistentes en `assets/css/styles.css` y `.vscode/tasks.json`.
- **COMPLETADO** — Se inspeccionaron las páginas PHP, scripts JavaScript, estilos, plantillas, configuración, repositorio de datos, DDL, relaciones y documentación existentes.
- **COMPLETADO** — Se identificaron flujos que eran parciales o dependían de SQL en páginas y se conectaron a la nueva separación por capas.
- **PARCIAL** — Se documentó la compatibilidad objetivo y configuración; falta ejecutar en Laragon con las versiones declaradas en el README.
- **COMPLETADO** — No se realizaron operaciones destructivas contra una base de datos existente.

## B. POO y arquitectura de tres capas

- **COMPLETADO** — Se añadieron clases de infraestructura para PDO, sesiones, autenticación, CSRF y respuestas de API.
- **COMPLETADO** — Se añadieron modelos de dominio para usuarios, productos, ventas y gastos.
- **COMPLETADO** — Se añadieron DAOs con responsabilidades de persistencia e inyección de PDO.
- **COMPLETADO** — Se añadieron servicios para validar y coordinar productos, gastos, ventas y movimientos de inventario.
- **COMPLETADO** — Se añadieron controladores por recurso; los controladores no ejecutan consultas SQL.
- **COMPLETADO** — Las páginas presentan las vistas y JavaScript consume la API para las mutaciones.
- **COMPLETADO** — Se documentó el patrón DAO y la separación de presentación, negocio y persistencia.
- **PARCIAL** — La arquitectura está implementada, pero falta ejecutar pruebas end-to-end con PHP y MySQL/MariaDB.

## C. API REST

- **COMPLETADO** — La API está separada de las páginas HTML bajo `api/`.
- **COMPLETADO** — Se enrutan recursos con nombres plurales por método HTTP e identificador opcional.
- **COMPLETADO** — Hay rutas para productos, gastos, ventas y movimientos de stock, según las operaciones implementadas.
- **COMPLETADO** — La clase central de respuesta mantiene `status`, `message` y `data`, codificación Unicode y JSON sin escape de barras.
- **COMPLETADO** — Los controladores no escriben respuestas JSON ad hoc ni ejecutan SQL.
- **PARCIAL** — Se implementaron los códigos HTTP 200, 201, 400, 401, 403, 404, 405 y 500; falta comprobarlos mediante solicitudes HTTP.
- **COMPLETADO** — Se configuraron reglas para Apache/Laragon y un router para el servidor integrado de PHP.
- **PARCIAL** — La salida JSON exclusiva y el comportamiento de rutas deben verificarse ejecutando ambos servidores.

## D. Autenticación, autorización y validación

- **COMPLETADO** — Los endpoints privados requieren sesión autenticada.
- **COMPLETADO** — Los permisos se verifican en el servidor según rol y operación.
- **COMPLETADO** — Las mutaciones requieren CSRF mediante `X-CSRF-Token`.
- **COMPLETADO** — Los datos se validan en los servicios/controladores antes de persistirlos.
- **COMPLETADO** — Se utilizan consultas preparadas y PDO con excepciones y prepares no emulados.
- **COMPLETADO** — Los errores técnicos se registran en el servidor y las respuestas externas son genéricas.
- **COMPLETADO** — La herramienta CLI crea usuarios con `password_hash()` sin añadir contraseñas utilizables al repositorio.
- **PARCIAL** — Falta probar en HTTP los casos 401/403, token CSRF ausente/inválido, errores de validación y errores internos.

## E. CRUD de productos y gastos

- **COMPLETADO** — Productos: lectura de lista y detalle, creación, modificación y eliminación lógica.
- **COMPLETADO** — Productos: validación de campos, precios, cantidades, límites y categoría.
- **COMPLETADO** — Productos: eliminación lógica para preservar referencias históricas.
- **COMPLETADO** — Gastos: lectura de lista y detalle, creación, modificación y eliminación.
- **COMPLETADO** — Gastos: validación de descripción, importe, categoría y fecha.
- **COMPLETADO** — Las pantallas de inventario y gastos realizan estas operaciones con Fetch sin recargar la página.
- **PARCIAL** — Falta probar las operaciones y restricciones de claves foráneas con una base de datos real.
- **PARCIAL** — Falta verificar visualmente actualización de tablas, totales, mensajes y confirmación de eliminación.

## F. Ventas y reposición

- **COMPLETADO** — El flujo de venta consulta productos disponibles, permite seleccionar cantidades y calcula precios/subtotales en la interfaz.
- **COMPLETADO** — El servicio de ventas vuelve a validar cantidades y stock en el servidor.
- **COMPLETADO** — El registro de venta guarda encabezado, detalles, saldo y movimientos dentro de una transacción.
- **COMPLETADO** — Se bloquean filas de producto durante la operación y se hace rollback ante excepciones.
- **COMPLETADO** — La pantalla de ventas consume la API y consulta el historial.
- **COMPLETADO** — La reposición y los ajustes de stock persisten los movimientos y el saldo dentro de una transacción.
- **PARCIAL** — Falta comprobar en MySQL/MariaDB el rollback, el bloqueo concurrente y la actualización correcta del stock.
- **PARCIAL** — Falta verificar visualmente los flujos completos de venta y reposición en navegador.

## G. Base de datos y migraciones

- **COMPLETADO** — `database/database.sql` contiene el DDL actualizado para una instalación nueva.
- **COMPLETADO** — Las categorías de gastos están normalizadas y relacionadas mediante clave foránea.
- **COMPLETADO** — Se documentaron las decisiones de normalización y el análisis de 3FN.
- **COMPLETADO** — Se añadieron migraciones para categorías, subtotal generado y restricciones/índices.
- **COMPLETADO** — Las migraciones no eliminan tablas ni registros históricos; se documentó detenerse ante datos que incumplan nuevas restricciones.
- **COMPLETADO** — Se preparó un seed coherente de demostración; las cuentas se crean aparte con la herramienta CLI.
- **COMPLETADO** — El diagrama ER Mermaid refleja el esquema documentado y sus relaciones.
- **PARCIAL** — Falta importar DDL, seed y migraciones en la versión real del motor y validar las restricciones.
- **PARCIAL** — Falta renderizar el diagrama ER en un visor compatible y contrastarlo con el esquema importado.

## H. Frontend y experiencia de uso

- **COMPLETADO** — Las páginas de productos, gastos, ventas y reposición consumen la API mediante Fetch.
- **COMPLETADO** — Los formularios de mutación previenen el envío tradicional, envían JSON y conservan credenciales de sesión.
- **COMPLETADO** — El cliente gestiona respuestas HTTP fallidas, JSON inválido y errores de red.
- **COMPLETADO** — Se comprueba `status` antes de tratar una respuesta como exitosa.
- **PARCIAL** — Las tablas, totales e indicadores se actualizan desde las respuestas; falta validación visual y funcional con el navegador.
- **PARCIAL** — Se ajustó el login para escritorio desde 1024 px y se preservaron reglas móviles, pero no se revisaron capturas a los breakpoints solicitados.
- **PARCIAL** — Los menús y páginas están limitados por rol en el código; falta validar con cuentas reales de cada rol.

## I. Configuración y documentación

- **COMPLETADO** — `.gitignore` excluye configuración privada, logs, dependencias y archivos locales del IDE.
- **COMPLETADO** — `.env.example` describe la configuración sin credenciales reales y se conservan las variables `STOCKFLOW_DB_*`.
- **COMPLETADO** — README explica instalación, configuración, Laragon, servidor integrado, roles, endpoints y creación inicial segura de usuarios.
- **COMPLETADO** — README advierte que quitar credenciales del estado actual no las elimina del historial de Git y recomienda rotarlas.
- **COMPLETADO** — Se documentaron arquitectura, API, 3FN, álgebra relacional, ER, proceso de contribución y resultados.
- **COMPLETADO** — El álgebra relacional incluye tres consultas relacionadas con el sistema y sus equivalentes SQL.
- **PARCIAL** — La bibliografía técnica se incorporó; falta revisar todas las referencias externas y la entrega final contra el formato académico requerido.
- **PARCIAL** — Los manuales describen el diseño implementado, pero deben contrastarse con una ejecución real antes de considerarlos evidencia de funcionamiento.

## J. Verificaciones pendientes de runtime

- **PENDIENTE** — Ejecutar `php -l` sobre todos los archivos PHP con el runtime soportado.
- **PENDIENTE** — Importar DDL y seed en MySQL/MariaDB y ejecutar las migraciones sobre una copia de datos existente.
- **PENDIENTE** — Ejecutar pruebas HTTP de respuestas, autenticación, permisos, CSRF, validación, CRUD y rutas con Apache y servidor integrado.
- **PENDIENTE** — Ejecutar pruebas de venta/reposición, falta de stock, rollback y concurrencia contra la base de datos.
- **PENDIENTE** — Ejecutar pruebas de navegador y comprobar el login/productos/gastos/ventas/reposición en 375, 576, 768, 1024 y 1440 px.
- **PENDIENTE** — Generar capturas y evidencia de la entrega únicamente después de verificar los flujos en un entorno funcional.
