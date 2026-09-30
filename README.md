# Proyecto StockFlow

StockFlow es una solución web orientada a la gestión operativa de inventario, ventas y gastos para pequeños y medianos negocios. El sistema permite monitorear el estado del almacén, registrar movimientos comerciales y visualizar indicadores clave para una toma de decisiones más rápida y ordenada.

## Objetivo

Centralizar la información del negocio en una interfaz sencilla, accesible y funcional, para facilitar el control de stock, la trazabilidad de operaciones y la revisión de métricas diarias.

## Funcionalidades principales

- Gestión de inventario y productos.
- Registro de ventas y movimientos comerciales.
- Control de gastos y egresos.
- Panel de indicadores con resumen operativo.
- Vistas organizadas por módulos: inventario, ventas, gastos y reportes.

## Estructura actual

```text
├── index.php, dashboard.php, inventario.php, vendedor.php
├── gastos.php, reportes.php, repositor.php
├── includes/          # layout, sidebar y footer compartidos
├── config/            # PDO, sesión, CSRF y autorización
├── data/              # consultas reutilizables
├── database/          # esquema y datos iniciales MySQL
└── assets/css, assets/js
```

Las pantallas se renderizan con PHP y reutilizan un único layout. Los datos proceden de MySQL mediante PDO y las consultas reutilizables están aisladas en `data/repository.php`. Administrador gestiona inventario y gastos; vendedor gestiona ventas; repositor gestiona inventario y entradas.

## Ejecución local

Con Laragon o PHP instalado, ejecuta desde la raíz:

```bash
php -S localhost:8000
```

Importa `database/database.sql` desde HeidiSQL o ejecútalo desde Laragon. Luego abre `http://localhost:8000/index.php`. La conexión está centralizada en `config/database.php` y acepta `STOCKFLOW_DB_HOST`, `STOCKFLOW_DB_PORT`, `STOCKFLOW_DB_NAME`, `STOCKFLOW_DB_USER` y `STOCKFLOW_DB_PASSWORD`.

Usuarios iniciales:

- `admin@stockflow.local` / `Admin123!`
- `vendedor@stockflow.local` / `Vendedor123!`
- `repositor@stockflow.local` / `Repositor123!`

