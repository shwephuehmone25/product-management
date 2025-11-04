## Product Management API (Laravel 12)

A secure REST API for managing products and orders with authentication and clean architecture.

- Laravel Sanctum token authentication
- Repository + Service layers
- Custom Form Requests (validation)
- Eloquent Resources (consistent JSON)
- Soft deletes for Products and Orders
- Transactional Order creation (auto totals)
- Event + Listener + Mailable for order confirmation emails

---

## Setup

Prerequisites: PHP 8.2+, Composer, a database (PostgreSQL/MySQL/SQLite), and SMTP if you want real email delivery.

1) Install dependencies
- `composer install`

2) Configure environment
- Copy `.env.example` → `.env`, set DB and mailer values
- Generate key: `php artisan key:generate`

Example DB section
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

3) Migrate + seed
- `php artisan migrate --seed`

Seeded accounts
- Admin: `admin@example.com` / `password`

4) Run the app
- `php artisan serve`

Emails (queued)
- Start a worker: `php artisan queue:work --queue=notifications,default`
- Dev shortcut: set `QUEUE_CONNECTION=sync` then `php artisan optimize:clear`

---

## Authentication

All protected endpoints require a Bearer token from Sanctum.

- Register: `POST /api/auth/register`
- Login: `POST /api/auth/login`
- Me: `GET /api/auth/me` (auth:sanctum)
- Logout: `POST /api/auth/logout` (auth:sanctum)

Example (register)
```
POST /api/auth/register
{
  "name": "Admin",
  "email": "admin@example.com",
  "password": "password",
  "password_confirmation": "password",
  "role": "admin"
}
```

Response 201
```
{
  "message": "Registered successfully",
  "data": {
    "user": { "id": 1, "name": "Admin", "email": "admin@example.com", "role": "admin" },
    "token": "<personal-access-token>"
  }
}
```

---

## Users

- List (paginated): `GET /api/users` (auth:sanctum)

---

## Products

- List (filters + pagination): `GET /api/products`
  - Filters: `q`, `name`, `sku`, `min_price`, `max_price`, `min_stock`, `max_stock`, `date_from`, `date_to`
  - Sorting: `sort` in `id|name|price|stock|created_at`, `direction` in `asc|desc`
  - Admin-only: `with_trashed`, `only_trashed`
- Create: `POST /api/products` (admin)
- Update: `PUT /api/products/{product}` (admin)

Example (list)
```
GET /api/products?q=shirt&min_price=10&max_price=50&sort=price&direction=asc&per_page=10
```

Response 200
```
{
  "data": [
    {"id": 3, "name": "Blue Shirt", "sku": "SKU-1234", "price": "19.99", "stock": 50},
    {"id": 5, "name": "Green Shirt", "sku": "SKU-7777", "price": "25.00", "stock": 34}
  ],
  "meta": { "current_page": 1, "per_page": 10, "total": 24, "last_page": 3 }
}
```

Example (create)
```
POST /api/products
{
  "name": "T-Shirt",
  "sku": "TS-001",
  "price": 19.99,
  "stock": 100
}
```

Response 201
```
{
  "message": "Product created successfully",
  "data": {"id": 21, "name": "T-Shirt", "sku": "TS-001", "price": "19.99", "stock": 100}
}
```

---

## Orders

- List: `GET /api/orders` (auth:sanctum)
  - Admin sees all; customers see their own
- Create: `POST /api/orders` (auth:sanctum)
  - Body: `{ "status": "pending|confirmed|cancelled", "items": [{"product_id":1, "quantity":2}, ...] }`
  - Each item subtotal = product price × quantity; order `total_amount` auto-calculated
- Show: `GET /api/orders/{id}` (auth:sanctum)
- Update status: `PUT /api/orders/{id}/status` (admin)

Example (create)
```
POST /api/orders
{
  "status": "pending",
  "items": [
    {"product_id": 1, "quantity": 2},
    {"product_id": 3, "quantity": 1}
  ]
}
```

Response 201
```
{
  "message": "Order created successfully",
  "data": {
    "id": 10,
    "user_id": 5,
    "status": "pending",
    "total_amount": "149.97",
    "items": [
      {"id": 31, "product_id": 1, "quantity": 2, "price": "49.99", "subtotal": "99.98", "product": {"id": 1, "name": "Blue Shirt", "sku": "SKU-1234", "price": "49.99"}},
      {"id": 32, "product_id": 3, "quantity": 1, "price": "49.99", "subtotal": "49.99", "product": {"id": 3, "name": "Pants", "sku": "SKU-5678", "price": "49.99"}}
    ]
  }
}
```

Example (update status — admin)
```
PUT /api/orders/10/status
{
  "status": "confirmed"
}
```

Response 200
```
{
  "message": "Order status updated successfully",
  "data": {"id": 10, "status": "confirmed", "total_amount": "149.97"}
}
```

Emails
- On status change to `confirmed`, an email is queued and rendered with `resources/views/mail/order/confirmed.blade.php`.
- Configure SMTP in `.env` and run a queue worker to deliver.

---

## Error Handling

- All `/api/*` routes respond with JSON, including validation errors.
- Common statuses: `401` unauthenticated, `403` forbidden, `404` not found, `409` database error, `422` validation.

---

## Assumptions

- Roles: `admin` and `customer`; only admins can create/update products and confirm order status.
- Tokens are issued via Sanctum personal access tokens.
- Money values are serialized as strings to avoid FP rounding errors.
- Soft deletes for Products and Orders; OrderItems keep links to soft-deleted Products for history.
- Stock reservation/deduction is not implemented.
- Email is via SMTP; no database notifications.

---

## Quick Start

- `php artisan migrate --seed`
- `php artisan serve`

Optional (email via queues): `php artisan queue:work --queue=notifications,default`

