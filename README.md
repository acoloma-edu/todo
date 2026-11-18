# Laravel 13 To-Do Application

Requirements: PHP >= 8.3, Composer, and a MySQL/MariaDB database (or SQLite).

## Quick deployment

```sh
git clone https://github.com/acoloma-edu/todo.git todo
cd todo
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Edit the `.env` file with the right values, including an existing database and a user with DDL and DML permissions.
For production, set `APP_ENV=production` and `APP_DEBUG=false`.

```sh
php artisan migrate --force
php artisan optimize
```

Create a virtual host on your nginx/apache service that points to the `public/` directory.
The web server user needs write access to `storage/` and `bootstrap/cache/`.

The compiled front-end assets (`public/build`) are committed, so the server does not need Node.js.

## Local development

```sh
npm install
npm run dev      # Vite dev server with hot reload
php artisan serve
```

Before you deploy front-end changes, run `npm run build` and commit `public/build`.

## Tests

```sh
composer test
```
