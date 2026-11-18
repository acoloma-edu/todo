# Aplicación To-Do con Laravel 13

Aplicación sencilla de lista de tareas hecha con Laravel 13.

## Requisitos

- PHP 8.3 o superior, con las extensiones que necesita Laravel 13
- Composer
- MySQL o MariaDB
- Servidor web nginx o Apache con PHP-FPM

## Despliegue rápido

```sh
git clone https://github.com/acoloma-edu/todo.git .
composer install
cp .env.example .env
php artisan key:generate
```

Edita el fichero `.env` con los valores adecuados, incluida una base de datos existente y un usuario con permisos DDL y DML.

```sh
php artisan migrate
```

Crea un virtual host en tu servidor nginx/apache.

> El CSS y el JS ya están compilados en `public/build`, así que no hace falta ejecutar `npm`.
