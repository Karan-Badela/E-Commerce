# Mini E-Commerce REST API

Backend-only Laravel API for catalog browsing, Sanctum authentication, carts, and order placement.

## Requirements

PHP 8.2 or newer, Composer, MySQL 8, and Laravel 12. Docker Compose is optional.

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Set `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`, then run:

```bash
php artisan migrate --seed
php artisan serve
php artisan queue:work
```

The API is available at `http://127.0.0.1:8000/api`. The seed creates 12 categories and 36 products.

## Authentication

Register with `POST /api/register` using `name`, `email`, `password`, and `password_confirmation`; or log in at `POST /api/login`. Both return a bearer token. Send it as `Authorization: Bearer {token}` for `/api/user`, `/api/logout`, cart, and order endpoints. Login and registration share a five requests per minute IP limit.

## Catalog, cart, and orders

Categories and products support standard list, show, create, update, and delete operations. Product listing supports `search`, `page`, and `per_page`; `per_page` is capped at 100. Add items through `POST /api/cart/items` with `product_id` and `quantity`. Place an order with `POST /api/orders`; stock checks, snapshots, decrement, and cart clearing occur in one transaction. Confirmation mail is queued after commit. Configure `MAIL_*` and use a non-sync `QUEUE_CONNECTION` for asynchronous delivery.

## Tests

```bash
php artisan test
```

## Postman

Import `postman/ecommerce-api.json` and `postman/ecommerce-api.environment.json`. Set `base_url`, register or log in, then use the returned token for authenticated calls. The collection covers authentication, categories, products, cart, and orders.

## Docker

Copy `.env.example` to `.env`, set an `APP_KEY`, and configure `DB_CONNECTION=mysql`, `DB_HOST=mysql`, `DB_DATABASE=ecommerce`, `DB_USERNAME=ecommerce`, and `DB_PASSWORD`. Then run:

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

The stack runs PHP-FPM, Nginx, MySQL, and a queue worker. The API port defaults to 8000 and can be changed with `PORT`.

## Deployment

Provide the platform's database connection settings, `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, mail settings, and a persistent queue configuration through environment variables. For a basic Render web service, use `php artisan serve --host 0.0.0.0 --port $PORT` as the start command. Run migrations during release and keep a queue worker running. Keep `.env` out of source control.
