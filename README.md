# StockFlow

StockFlow es una aplicación web para administrar inventario, ventas, reposiciones, gastos y reportes de pequeños y medianos negocios. Fue desarrollada por Nexosoft con PHP, PDO, MySQL/MariaDB, HTML, CSS y JavaScript.

## Requisitos

- PHP 8.1 o superior con `pdo_mysql` y `mbstring`.
- MySQL 8.0.16 o superior, o una versión de MariaDB compatible con `CHECK` y columnas generadas.
- Apache con `mod_rewrite` para URLs REST en Laragon, o el servidor integrado de PHP.
- No requiere Composer, Node ni Bootstrap en tiempo de ejecución. La interfaz utiliza CSS propio.

## Configuración

1. Copia `.env.example` a `.env` y define `STOCKFLOW_DB_HOST`, `STOCKFLOW_DB_PORT`, `STOCKFLOW_DB_NAME`, `STOCKFLOW_DB_USER` y `STOCKFLOW_DB_PASSWORD`. Las variables del proceso tienen precedencia sobre `.env`.
2. Usa una cuenta MySQL dedicada para la aplicación; no mantengas `root` ni contraseñas de desarrollo en un entorno compartido.
3. Para una base vacía, importa `database/database.sql`.
4. Crea una cuenta inicial mediante el procedimiento seguro descrito en [Seguridad y configuración](docs/API.md#configuración-y-autenticación).
5. Si necesitas datos de demostración en una instalación vacía, importa `database/seed.sql` después de crear al menos un usuario. El script no publica contraseñas y no debe repetirse sobre datos operativos.

No importes `database/database.sql` sobre una base existente para actualizarla. En instalaciones previas ejecuta una sola vez, en orden, todas las migraciones de `database/migrations/`; consulta [Base de datos y normalización](docs/BASE-DE-DATOS.md).

## Ejecución

### Laragon / Apache

Coloca el proyecto en el directorio web de Laragon, configura el host local y abre `index.php`. Apache debe permitir `.htaccess` y tener `mod_rewrite` activo para las rutas `/api/...`.

### Servidor integrado de PHP

Desde la raíz del proyecto:

```powershell
php -S 127.0.0.1:8000 router.php
```

Luego abre `http://127.0.0.1:8000`. El enrutador `router.php` reenvía `/api/...` a la API sin depender de Apache.

## Módulos y permisos

| Rol | Acceso |
|---|---|
| Administrador | Dashboard, inventario, ventas, gastos, reportes, reposición y usuarios |
| Vendedor | Dashboard, ventas (consulta de catálogo) y reportes |
| Repositor | Dashboard, inventario, reportes y reposición |

El servidor vuelve a validar los roles; ocultar enlaces no es el control de autorización.

## API

La API devuelve JSON con `status`, `message` y `data`. Los recursos principales son:

```text
GET/POST       /api/products
GET/PUT/DELETE /api/products/{id}
GET/POST       /api/expenses
GET/PUT/DELETE /api/expenses/{id}
GET/POST       /api/sales
GET            /api/sales/{id}
GET/POST       /api/stock-movements
```

Las operaciones de escritura requieren sesión, rol autorizado y el token CSRF del encabezado `X-CSRF-Token`. Los formularios de productos, gastos, ventas y reposición usan Fetch y actualizan sus tablas sin recargar la página. Los detalles de roles, campos, códigos HTTP y respuestas están en [API REST](docs/API.md).

## Arquitectura

El código está dividido en presentación (páginas e `assets/`), lógica de negocio (`app/Services/`) y persistencia (`app/DAO/`). `DatabaseConnection` centraliza PDO y `api/controllers/` adapta HTTP a servicios. El patrón DAO y las clases principales se describen en [Arquitectura y POO](docs/ARQUITECTURA.md).

```text
api/                 Endpoints JSON y controladores
app/Core/            PDO, sesión, CSRF y respuesta API
app/Models/          Usuario, Producto, Venta y Gasto
app/DAO/             Consultas SQL y persistencia
app/Services/        Validaciones y reglas del negocio
assets/              CSS responsive y JavaScript Fetch
config/              Autoload, entorno, bootstrap y PDO
database/            DDL, semilla y migraciones
docs/                Informe técnico, modelo, API y pruebas
includes/            Layout y navegación compartidos
```

## Documentación

- [Informe técnico de entrega](docs/ENTREGA-TECNICA.md)
- [Manual de usuario](docs/MANUAL-USUARIO.md)
- [Arquitectura y POO](docs/ARQUITECTURA.md)
- [API REST](docs/API.md)
- [Base de datos, migraciones y normalización](docs/BASE-DE-DATOS.md)
- [Diagrama entidad-relación](docs/DIAGRAMA-ER.md)
- [Álgebra relacional](docs/ALGEBRA-RELACIONAL.md)
- [Contribuciones y flujo Git](docs/CONTRIBUCIONES.md)
- [Checklist de entrega](docs/CHECKLIST.md)

## Credenciales anteriores

La versión previa del README publicó contraseñas de demostración. Aunque se retiraron del contenido actual, eliminar texto no borra el historial Git. Rota esas contraseñas si se utilizaron en cualquier entorno y considera las cuentas antiguas comprometidas.
