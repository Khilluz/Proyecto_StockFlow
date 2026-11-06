# Manual de instalación y mantenimiento

## Requisitos

- Una computadora con Windows. Para que varias personas usen StockFlow, la computadora donde se instala debe permanecer encendida mientras trabajan.
- Conexión a Internet para descargar el programa de instalación del servidor local. No es necesaria para usar StockFlow una vez instalado en la computadora.
- Laragon con Apache, PHP 8.1 o superior y MySQL 8.0.16 o superior (o una versión compatible de MariaDB). PHP debe incluir las extensiones `pdo_mysql` y `mbstring`.
- La carpeta del proyecto StockFlow y los datos de conexión a la base de datos, entregados por la persona que instala el sistema.
- Ayuda de un técnico para la primera instalación. Esta preparación incluye crear la base de datos y la primera cuenta administradora; no es necesario repetirla en el uso diario.

## Instalación

1. Pida al técnico que instale Laragon y StockFlow en la computadora que alojará el sistema. No descargue archivos de instalación desde sitios desconocidos.
2. El técnico debe colocar la carpeta `Proyecto_StockFlow` dentro de la carpeta `www` de Laragon. No cambie el nombre ni mueva esta carpeta después.
3. El técnico debe abrir Laragon y presionar **Iniciar todo**. Los servicios Apache y MySQL deben quedar iniciados.
4. El técnico debe abrir `http://localhost/phpmyadmin` en el navegador, crear una base de datos llamada `stockflow` y seleccionar la opción **Importar** para cargar el archivo `database/database.sql` que está dentro de la carpeta del proyecto.
5. El técnico debe configurar la conexión de StockFlow y crear la primera cuenta administradora. Debe entregar al cliente la dirección para entrar y el usuario y contraseña iniciales por un medio privado.
6. Para comprobar que quedó instalado, abra en el navegador la dirección que entregó el técnico, por ejemplo `http://localhost/Proyecto_StockFlow/`. Inicie sesión y confirme que aparece la pantalla principal.
7. Si la página no abre, aparece un error o Laragon muestra un servicio detenido, no borre archivos ni vuelva a importar la base de datos: comuníquese con el técnico.

## Configuración

1. Para abrir StockFlow en la misma computadora donde se instaló, abra Laragon, presione **Iniciar todo** y luego abra en el navegador la dirección que le entregó el técnico.
2. Si el negocio necesita abrir el sistema desde otras computadoras, pida al técnico que configure el acceso a la red. No comparta la computadora ni publique la dirección en Internet sin esa configuración.
3. Inicie sesión con su usuario. El administrador puede crear accesos para el personal desde **Usuarios** y asignar el rol que corresponda: **Administrador**, **Vendedor** o **Repositor**.
4. Use una contraseña personal que no comparta. Si necesita cambiarla o la olvidó, solicite ayuda al administrador del negocio.
5. No cambie archivos, carpetas ni configuraciones de Laragon o de la base de datos. Para modificar la conexión, actualizar el sistema o recuperar el acceso, contacte al técnico.

## Mantenimiento básico

- **Todos los días:** al terminar de usar el sistema, cierre sesión. No apague la computadora mientras alguien esté usando StockFlow.
- **Una vez por semana:** solicite al administrador o al técnico una copia de seguridad de la base de datos. Guárdela en un lugar distinto de la computadora donde funciona StockFlow, como una memoria USB guardada bajo llave. Compruebe periódicamente que las copias recientes estén ahí.
- **Antes de actualizar o hacer cambios:** haga una copia de seguridad y pida al técnico que realice el trabajo. No vuelva a importar `database/database.sql` para actualizar: podría afectar los datos existentes.
- **Si la computadora se reinicia:** abra Laragon y presione **Iniciar todo** antes de entrar al sistema.
- **Si StockFlow no funciona:** anote el mensaje que aparece, tome una foto si puede y contacte al administrador o al técnico. No borre datos ni intente reparar la base de datos por su cuenta.
- **Mantenga la computadora protegida:** use una contraseña para iniciar Windows, permita las actualizaciones habituales del sistema y no comparta las claves de administrador ni de la base de datos.
