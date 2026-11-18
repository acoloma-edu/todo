# Aplicación To-Do con Laravel 13

Aplicación sencilla de lista de tareas (crear, editar, marcar como hecha/pendiente y borrar) hecha con **Laravel 13**, **Vite** y **Tailwind CSS 4**. Es un proyecto de prueba pensado para practicar despliegues.

## Requisitos

| Requisito | Para qué se usa |
|-----------|-----------------|
| **PHP 8.3 o superior** | Laravel 13 no funciona con versiones anteriores. Compruébalo con `php -v`. |
| **Composer** | Instala las dependencias de PHP (la carpeta `vendor/`). |
| **MySQL / MariaDB** (o SQLite) | Guarda las tareas, las sesiones y la caché. |
| **Servidor web** (nginx o Apache) | Sirve la aplicación en producción. |
| Node.js y npm (*solo en desarrollo*) | Compila el CSS y el JS. El servidor **no** lo necesita. |

Extensiones de PHP habituales que deben estar activas: `pdo_mysql` (o `pdo_sqlite`), `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo` y `curl`.

## Despliegue en un servidor

### 1. Descargar el código

```sh
git clone https://github.com/acoloma-edu/todo.git todo
cd todo
```

### 2. Instalar las dependencias de PHP

```sh
composer install --no-dev --optimize-autoloader
```

- `--no-dev` no instala las herramientas de desarrollo (tests, depuración…), que en producción no se necesitan.
- `--optimize-autoloader` genera un mapa de clases para que la aplicación cargue más rápido.

### 3. Crear el fichero de configuración `.env`

```sh
cp .env.example .env
php artisan key:generate
```

El fichero `.env` contiene la configuración propia de cada entorno (contraseñas, base de datos…). **No se sube nunca a git.** `key:generate` crea la clave `APP_KEY`, que Laravel usa para cifrar las sesiones y las cookies.

Después edita `.env` y ajusta al menos estos valores:

```ini
APP_ENV=production        # entorno de producción
APP_DEBUG=false           # no mostrar errores detallados a los usuarios
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_de_la_bd
DB_USERNAME=usuario
DB_PASSWORD=contraseña
```

La base de datos tiene que existir ya, y el usuario necesita permisos **DDL** (crear y modificar tablas) y **DML** (leer y escribir datos).

> Por defecto, las sesiones (`SESSION_DRIVER=database`), la caché (`CACHE_STORE=database`) y las colas (`QUEUE_CONNECTION=database`) se guardan en la base de datos. Las tablas necesarias las crean las migraciones del paso siguiente.

### 4. Crear las tablas en la base de datos

```sh
php artisan migrate --force
```

Ejecuta los ficheros de `database/migrations/`, que crean las tablas (`todos`, `users`, `sessions`, `cache`, `jobs`…). La opción `--force` hace falta porque en producción Laravel pide confirmación antes de tocar la base de datos.

Si el servidor ya tenía desplegada la versión anterior (Laravel 9), este comando solo crea las tablas que faltan. **No se pierde ninguna tarea.**

### 5. Optimizar para producción

```sh
php artisan optimize
```

Guarda en caché la configuración, las rutas y las vistas para que la aplicación responda más rápido.

**Importante:** si después cambias algo del `.env`, vuelve a ejecutar `php artisan optimize` (o `php artisan optimize:clear`) para que se apliquen los cambios.

### 6. Permisos de las carpetas

El usuario del servidor web (normalmente `www-data`) tiene que poder escribir en `storage/` (logs, ficheros, vistas compiladas) y en `bootstrap/cache/`:

```sh
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 7. Configurar el servidor web

El *virtual host* tiene que apuntar a la carpeta **`public/`** del proyecto, **no** a la raíz. Así el resto del código (y el `.env`) no se puede descargar desde el navegador.

Ejemplo para **nginx** con PHP-FPM:

```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /var/www/todo/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Con **Apache**, apunta el `DocumentRoot` a `public/` y activa `mod_rewrite`. El fichero `public/.htaccess` ya incluye las reglas necesarias.

Para comprobar que la aplicación funciona, abre `https://tu-dominio.com/up`. Laravel responde con una página de estado si todo va bien.

## Actualizar un despliegue existente

```sh
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

## Desarrollo en local

```sh
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Para trabajar en local lo más cómodo es SQLite, porque no necesita servidor de base de datos. En el `.env` pon `DB_CONNECTION=sqlite` y comenta las demás líneas `DB_*`. Después:

```sh
touch database/database.sqlite
php artisan migrate
```

Arranca la aplicación con dos terminales:

```sh
npm run dev          # Vite: compila CSS/JS y recarga el navegador al guardar
php artisan serve    # servidor de PHP en http://localhost:8000
```

### Assets compilados (CSS y JS)

El CSS y el JS compilados se guardan en `public/build` y **se suben al repositorio**. Así el servidor no necesita Node.js para desplegar.

Por eso, cada vez que cambies las vistas, el CSS o el JS:

```sh
npm run build
git add public/build
```

Si no lo haces, en producción se verá el CSS antiguo, o faltarán estilos de clases nuevas de Tailwind.

## Tests

```sh
composer test
```

Los tests están en `tests/Feature/TodoTest.php`. Comprueban que se pueden listar, crear, validar, editar, marcar y borrar tareas. Usan una base de datos SQLite en memoria, así que no tocan tus datos.

## Estructura principal

| Ruta | Contenido |
|------|-----------|
| `routes/web.php` | Rutas de la aplicación (URL → controlador). |
| `app/Http/Controllers/` | `TodoController` (CRUD) y `ChangeIsDoneController` (marcar como hecha/pendiente). |
| `app/Http/Requests/TodoRequest.php` | Validación del formulario (título y descripción de 10 caracteres como mínimo). |
| `app/Models/Todo.php` | Modelo Eloquent de la tabla `todos`. |
| `resources/views/` | Vistas Blade y componentes (`x-card`, `x-button`, `x-todo`…). |
| `database/migrations/` | Definición de las tablas de la base de datos. |
| `bootstrap/app.php` | Configuración de rutas, middleware y excepciones (Laravel 11+). |
