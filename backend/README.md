# PHP Backend

This backend is built to match the existing Vue frontend for the sheet music store.

## Features

- Plain PHP REST API with a small MVC-style structure
- MySQL database via PDO
- JWT authentication with login, register, and current-user endpoints
- Composer verification requests with admin approval or decline
- Middleware for CORS and protected routes
- Seeded catalog, composers, demo user, and purchase history

## Folder Structure

- `public/` entry point
- `src/Config/` environment loading
- `src/Core/` request, response, router, database
- `src/Controllers/` API endpoints
- `src/Middleware/` CORS and JWT auth
- `src/Models/` database access
- `src/Services/` reusable JWT service
- `database/schema.sql` schema and seed data

## Setup

1. Copy `.env.example` to `.env`.
2. Update the MySQL, JWT, and mail sender settings.
3. Import `database/schema.sql` into MySQL.
4. Serve the backend from the `backend/public` directory.

Example using PHP's built-in server:

```bash
php -S localhost:8000 -t backend/public
```

## API Endpoints

- `GET /api/health`
- `POST /api/auth/register`
- `POST /api/auth/login`
- `GET /api/auth/me` (Bearer token required)
- `GET /api/scores`
- `GET /api/composers`
- `GET /api/composer-requests/me` (Bearer token required)
- `POST /api/composer-requests` (Bearer token required)
- `GET /api/admin/composer-requests` (Admin token required)
- `POST /api/admin/composer-requests/approve` (Admin token required)
- `POST /api/admin/composer-requests/decline` (Admin token required)
- `GET /api/purchases` (Bearer token required)

## Demo Login

- Email: `john.doe@email.com`
- Password: `password123`
- Admin email: `admin@theresonanz.com`
- Admin password: `password123`
