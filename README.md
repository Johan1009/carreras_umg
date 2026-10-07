# carreras_umg · Consulta de información para secretaría

Aplicación web en PHP (CodeIgniter 4) para registrar carreras, adjuntar sus tres documentos PDF y consultarlos desde una tableta. Desde la consulta se puede ampliar cada PDF a pantalla completa y enviar la información por correo electrónico.

- **Administración**: registro de carreras con sus tres PDF, mini CRUD de usuarios y configuración del servidor SMTP.
- **Consulta** (secretaría): catálogo de carreras con buscador, visor de PDF y envío por correo.
- **Roles**: `admin` gestiona todo; `secretaria` solo consulta y envía información.

Probado en Windows 11 con XAMPP (PHP 8.2.12, MariaDB 10.4.32). Las instrucciones de Ubuntu siguen la instalación estándar de Apache, PHP y MySQL/MariaDB, pero aún no se han probado en un equipo Ubuntu.

---

## 1. Requisitos

| Componente | Versión mínima | Notas |
|---|---|---|
| PHP | 8.2 | Extensiones: `intl`, `mbstring`, `mysqli`, `fileinfo`, `openssl`, `curl`, `json`, `xml` |
| Servidor web | Apache 2.4 | Con `mod_rewrite` activo |
| Base de datos | MySQL 8 (Ubuntu) o MariaDB 10.4+ (XAMPP) | Se usa el driver `MySQLi` |
| Composer | 2.x | Para instalar las dependencias de `vendor/` |

El proyecto no incluye la carpeta `vendor/` en el repositorio, así que **siempre hay que ejecutar `composer install`**.

---

## 2. Instalación en Windows (XAMPP)

### 2.1 Preparar XAMPP

1. Instale [XAMPP](https://www.apachefriends.org/) con PHP 8.2 o superior.
2. En el panel de XAMPP, inicie **Apache** y **MySQL**.
3. Verifique que `C:\xampp\php\php.exe -m` incluya `intl`, `mbstring`, `mysqli`, `fileinfo` y `openssl`. Si falta alguna, descomente la línea `extension=...` correspondiente en `C:\xampp\php\php.ini`.
4. Instale [Composer](https://getcomposer.org/download/) si no lo tiene.

### 2.2 Copiar el proyecto

Copie la carpeta del proyecto en `C:\xampp\htdocs\carreras_umg`. La estructura debe quedar así:

```
C:\xampp\htdocs\carreras_umg\
├── app\
├── public\        ← carpeta web (index.php)
├── writable\
├── env            ← plantilla de configuración
└── composer.json
```

### 2.3 Instalar dependencias y configurar

Abra PowerShell o CMD en `C:\xampp\htdocs\carreras_umg` y ejecute:

```bat
composer install
copy env .env
C:\xampp\php\php.exe spark key:generate
```

Edite `.env` (sin el `#` al inicio de cada línea que vaya a usar) con estos valores:

```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost/carreras_umg/public/'

database.default.hostname = localhost
database.default.database = carreras_umg
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

> La `encryption.key` la genera `key:generate`. **No la cambie después**: si lo hace, la contraseña SMTP guardada ya no podrá descifrarse y habrá que volver a escribirla en el panel de correo.

### 2.4 Crear la base de datos, migrar y crear el administrador

En phpMyAdmin (http://localhost/phpmyadmin) cree la base de datos `carreras_umg` con cotejamiento `utf8mb4_general_ci`, o ejecute:

```bat
C:\xampp\mysql\bin\mysql.exe -uroot -e "CREATE DATABASE IF NOT EXISTS carreras_umg CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
```

Después, desde `C:\xampp\htdocs\carreras_umg`:

```bat
C:\xampp\php\php.exe spark migrate
C:\xampp\php\php.exe spark db:seed AdminInicialSeeder
```

El seeder crea el usuario **`admin`** con una **contraseña aleatoria que se muestra una sola vez** en la consola. Guárdela antes de cerrar la ventana.

### 2.5 Abrir la aplicación

Entre a **http://localhost/carreras_umg/public/** con el usuario `admin` y la contraseña mostrada en el paso anterior.

---

## 3. Instalación en Ubuntu (22.04 / 24.04)

Los comandos siguientes asumen Apache, PHP y MySQL instalados desde los repositorios oficiales de Ubuntu (MySQL 8.0). Ubuntu 24.04 trae PHP 8.3, que también es compatible (el proyecto exige `^8.2`).

### 3.1 Paquetes del sistema

```bash
sudo apt update
sudo apt install -y apache2 mysql-server unzip git curl \
    php php-cli php-intl php-mbstring php-mysql php-xml php-curl php-zip libapache2-mod-php
sudo a2enmod rewrite
```

Instale Composer:

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 3.2 Copiar el proyecto

```bash
sudo mkdir -p /var/www/carreras-umg
sudo cp -r /ruta/al/proyecto/. /var/www/carreras-umg/
cd /var/www/carreras-umg
sudo composer install --no-interaction --prefer-dist
sudo cp env .env
```

> La ruta `/var/www/carreras-umg` es solo un ejemplo; puede usar cualquier carpeta (incluida una dentro de `/var/www/html`, como `/var/www/html/carreras-umg`). Lo importante es que el `DocumentRoot` del virtual host (siguiente paso) apunte a la subcarpeta `public/` **dentro** de esa carpeta, no a la carpeta del proyecto en sí.

### 3.3 Configurar Apache

Cree `/etc/apache2/sites-available/carreras-umg.conf` (o edite `000-default.conf` si el proyecto será el único sitio del servidor):

```apache
<VirtualHost *:80>
    ServerName carreras-umg.local
    DocumentRoot /var/www/carreras-umg/public

    <Directory /var/www/carreras-umg/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/carreras-umg-error.log
    CustomLog ${APACHE_LOG_DIR}/carreras-umg-access.log combined
</VirtualHost>
```

> **Use guion, no guion bajo, en `ServerName`** (`carreras-umg.local`, no `carreras_umg.local`): los nombres de host no aceptan `_` según el estándar de DNS. Esto no aplica al nombre de la carpeta ni al de la base de datos, que sí admiten guion bajo.
>
> **No agregue ningún prefijo de subcarpeta a la URL.** Como `DocumentRoot` ya apunta directamente a `public/`, el proyecto vive en la **raíz** de este virtual host: se accede como `http://carreras-umg.local/` (o `http://<ip-del-servidor>/`), nunca como `http://carreras-umg.local/carreras-umg/`. Si visita una URL con ese prefijo de más, CodeIgniter no la reconocerá y mostrará `Can't find a route for...`.

Active el sitio y recargue Apache:

```bash
sudo a2dissite 000-default.conf
sudo a2ensite carreras-umg.conf
sudo systemctl reload apache2
```

> Para probar en el mismo equipo, agregue `127.0.0.1 carreras-umg.local` a `/etc/hosts`. Si aún no tiene un dominio, puede entrar directamente por la IP del servidor en vez de `carreras-umg.local`; como es el único sitio del virtual host, Apache lo sirve igual aunque el `Host` de la petición no coincida con `ServerName`.

### 3.4 Permisos

PHP-Apache debe poder escribir en `writable/` (caché, sesiones, logs y PDF subidos):

```bash
sudo chown -R www-data:www-data /var/www/carreras-umg/writable
sudo chmod -R 775 /var/www/carreras-umg/writable
```

### 3.5 Configurar `.env`

Edite `/var/www/carreras-umg/.env`:

```ini
CI_ENVIRONMENT = production

app.baseURL = 'http://carreras-umg.local/'

database.default.hostname = localhost
database.default.database = carreras_umg
database.default.username = carreras_umg
database.default.password = cambie-esta-clave
database.default.DBDriver = MySQLi
database.default.port = 3306
```

> Si todavía no tiene dominio, use la IP pública del servidor en vez de `carreras-umg.local`, por ejemplo `app.baseURL = 'http://203.0.113.10/'` (IP de ejemplo). **`app.baseURL` debe ser únicamente la raíz del sitio**: sin `/carreras-umg` ni ninguna otra subcarpeta al final (el `DocumentRoot` ya apunta a `public/`, así que no hace falta), y sin `index.php` ni la ruta de una página (por ejemplo `/index.php/login`). CodeIgniter usa ese valor como prefijo para generar todos los enlaces, los assets (CSS/JS) y las redirecciones de la aplicación; cualquier ruta de más al final deja esos enlaces rotos.
>
> El nombre de la base de datos sí puede llevar guion bajo (`carreras_umg`); la recomendación de usar guion es solo para `ServerName`/nombres de host, no para la IP, la carpeta del proyecto ni la base de datos.

Genere la clave de cifrado:

```bash
cd /var/www/carreras-umg
sudo -u www-data php spark key:generate
```

> Use `sudo -u www-data` en los comandos `spark` para que los archivos que cree (sesiones, logs, caché) pertenezcan al usuario de Apache.

### 3.6 Base de datos

```bash
sudo mysql
```

```sql
CREATE DATABASE carreras_umg CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'carreras_umg'@'localhost' IDENTIFIED BY 'cambie-esta-clave';
GRANT ALL PRIVILEGES ON carreras_umg.* TO 'carreras_umg'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Use la misma contraseña que puso en `.env`.

### 3.7 Migrar y crear el administrador

```bash
cd /var/www/carreras-umg
sudo -u www-data php spark migrate
sudo -u www-data php spark db:seed AdminInicialSeeder
```

Guarde la contraseña que muestra el seeder; solo aparece una vez.

### 3.8 Límites de subida de PDF

Cada PDF puede pesar hasta 25 MB. Para que Apache y PHP acepten archivos de ese tamaño, edite `/etc/php/8.2/apache2/php.ini` (o la versión instalada) y ajuste:

```ini
upload_max_filesize = 40M
post_max_size = 100M
```

Luego reinicie Apache: `sudo systemctl restart apache2`.

### 3.9 Abrir la aplicación

Entre a **http://carreras-umg.local/** (o la IP/dominio real del servidor, sin ningún prefijo de subcarpeta) con el usuario `admin`.

---

## 4. Primer uso

1. Entre como `admin` y **cambie la contraseña** en **Usuarios → Editar**.
2. Cree los usuarios de secretaría en **Usuarios → Nuevo usuario**, con rol *Secretaría*.
3. Configure el correo en **Correo**:
   - Gmail: servidor `smtp.gmail.com`, puerto `587`, cifrado TLS.
   - Usuario: su dirección de Gmail. Contraseña: una **contraseña de aplicación** de Google (no la contraseña normal de la cuenta; requiere verificación en dos pasos).
   - Pulse **Enviar correo de prueba**.
4. Registre carreras en **Carreras → Nueva carrera**: nombre único y los tres PDF (trifoliar oficial, administrativo 1 y administrativo 2). Los nombres de archivo no importan.
5. La secretaría consulta desde **Consulta**: busca la carrera, cambia entre los PDF, amplía el visor o envía la información por correo.

---

## 5. Configuración de seguridad recomendada

- Use **HTTPS** en producción (por ejemplo, con Let's Encrypt en Ubuntu) y cambie `app.baseURL` a `https://`.
- Ponga `CI_ENVIRONMENT = production` (Ubuntu) para no mostrar detalles de errores al usuario.
- No comparta el archivo `.env` ni lo suba a repositorios: ya está en `.gitignore`.
- Cambie la contraseña del administrador inicial y use contraseñas largas para los demás usuarios.
- Los PDF se guardan en `writable/uploads/carreras/` y solo se sirven a usuarios con sesión iniciada.

---

## 6. Solución de problemas

| Síntoma | Causa probable | Solución |
|---|---|---|
| *"The action you requested is not allowed"* (403) al enviar un formulario | Sesión o cookie caducada, o la página se abrió hace mucho | Recargue la página e intente de nuevo. Revise que el navegador acepte cookies. |
| *"Unable to connect to the database"* | MySQL/MariaDB apagado o datos de `.env` incorrectos | Inicie el servicio y revise `database.default.*` en `.env`. |
| Error 500 al subir un PDF en Ubuntu | Permisos de `writable/` | Repita `chown` y `chmod` del paso 3.4. |
| Un PDF grande no se sube | `upload_max_filesize` o `post_max_size` bajos | Ajuste `php.ini` (paso 3.8). |
| *"No se pudo enviar el correo"* | Credenciales SMTP incorrectas o puerto bloqueado | Use una contraseña de aplicación, revise puerto y cifrado y pruebe el envío desde **Correo**. El detalle queda en `writable/logs/`. |
| El visor no muestra el PDF en Safari de iPad | Limitación del visor integrado del navegador | Use la opción de ampliar o pruebe otro navegador; revise la compatibilidad antes de usarlo en producción. |
| Olvidó la contraseña del administrador | No hay recuperación por correo | Genere un hash y actualícelo en la base de datos (ver abajo). |

Para restablecer la contraseña de un usuario desde la línea de comandos:

```bash
php -r 'echo password_hash("NuevaClave123", PASSWORD_DEFAULT), PHP_EOL;'
```

Copie el hash resultante y ejecute en MySQL:

```sql
UPDATE carreras_umg.usuarios SET password = 'PEGUE_EL_HASH_AQUI' WHERE usuario = 'admin';
```

---

## 7. Estructura del proyecto

```
app/
├── Config/              Rutas, filtros (auth, admin, CSRF), seguridad, correo
├── Controllers/
│   ├── Auth.php         Login, bloqueo por intentos, cierre de sesión
│   ├── Consulta.php     Catálogo, visor de PDF y envío por correo
│   └── Admin/           Carreras, Usuarios y Correo (solo administradores)
├── Database/
│   ├── Migrations/      Tablas: usuarios, carreras, carrera_archivos, correo_config
│   └── Seeds/           AdminInicialSeeder (crea el primer administrador)
├── Filters/             AuthFilter y AdminFilter
├── Libraries/           ArchivosCarrera (guardado de PDF), CorreoService (SMTP)
├── Models/
└── Views/               Plantillas de login, consulta, administración y correo
public/
├── index.php            Punto de entrada web (única carpeta expuesta)
└── assets/              CSS e interacciones (sin dependencias externas)
writable/
├── uploads/carreras/    PDF subidos (no accesibles directamente desde la web)
└── logs/                Registros de errores
```

---

## 8. Comandos útiles

| Acción | Windows (XAMPP) | Ubuntu |
|---|---|---|
| Migrar la base de datos | `C:\xampp\php\php.exe spark migrate` | `sudo -u www-data php spark migrate` |
| Ver estado de migraciones | `C:\xampp\php\php.exe spark migrate:status` | `sudo -u www-data php spark migrate:status` |
| Crear administrador inicial | `C:\xampp\php\php.exe spark db:seed AdminInicialSeeder` | `sudo -u www-data php spark db:seed AdminInicialSeeder` |
| Limpiar caché | `C:\xampp\php\php.exe spark cache:clear` | `sudo -u www-data php spark cache:clear` |

---

## Licencia

Este proyecto se distribuye bajo la licencia MIT. Ver [LICENSE](LICENSE). Está construido sobre [CodeIgniter 4](https://codeigniter.com).
