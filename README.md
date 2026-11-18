# Café Management System

A web application for running a café's daily operations: recording each shift's sales, tracking products and their purchase costs, and producing monthly revenue and profit reports for the owner.

It is built as a **PHP REST API** (FlightPHP, MySQL, JWT authentication) with a **single-page jQuery frontend**. The interface is in Serbian/Bosnian, and amounts are shown in RSD.

## Features

- **Role-based access:** *admins* manage products, shifts and employees and see all reports. *Employees* can only see their own data.
- **Shift tracking:** each working day has a first and second shift (*smena*), assigned to an employee and a venue.
- **Sales and profit calculation:** for every product sold in a shift, the API calculates revenue (`price × quantity`) and profit (`revenue − purchase cost`). It stores the purchase price at the time of sale, so later price changes don't rewrite history.
- **Monthly reports:** revenue, product costs, venue costs, gross and net profit, best-selling products, sales per employee, daily sales, product sales per venue, and first vs. second shift comparison.
- **Secure authentication:** passwords are hashed with bcrypt and sessions use signed JWTs that expire after 24 hours. Public registration always creates employee accounts, and password hashes are never returned by the API.
- **API documentation:** an interactive Swagger UI generated from OpenAPI annotations.

## Architecture

```
frontend/  (HTML + jQuery single-page app)
   │   REST calls with a JWT in the Authorization header
   ▼
backend/index.php ── AuthMiddleware (JWT check, role check)
   │
   ├── rest/routes/     HTTP endpoints (FlightPHP)
   ├── rest/services/   business rules and validation
   └── rest/dao/        SQL queries (PDO prepared statements)
                              │
                              ▼
                           MySQL
```

### Database

| Table | Purpose |
|---|---|
| `users` | Employees and admins (`role`: `admin` / `user`) |
| `products` | Menu items with selling `price` and purchase `entry_price` |
| `venues` | Locations and their fixed `daily_cost` |
| `shifts` | One employee's shift (`date`, `smena`, venue) |
| `shiftproducts` | Products sold during a shift, with calculated `total` (revenue) and `true_total` (profit) |

The full schema is in [`database/schema.sql`](database/schema.sql).

### API overview

| Area | Endpoints |
|---|---|
| Auth | `POST /auth/register`, `POST /auth/login` |
| Users | `GET /users`, `GET /users/{id}`, `GET /users/{id}/shifts`, `POST /users`, `PUT /users/{id}`, `DELETE /users/{id}`, `GET /users/search/{name}` |
| Products | `GET /products`, `GET /products/{id}`, `GET /products/sales`, `POST /products`, `PUT /products/{id}/price`, `DELETE /products/{id}` |
| Shifts | `GET /shifts`, `GET /shifts/{id}`, `GET /shifts/latest`, `GET /shifts/{id}/summary`, `POST /shifts`, `PUT /shifts/{id}`, `DELETE /shifts/{id}` |
| Shift sales | `GET /shift-products`, `GET /shift-products/{shiftId}`, `POST /shift-products`, `POST /shift-products/latest`, `PUT /shift-products/{id}`, `DELETE /shift-products/{id}` |
| Reports | `GET /reports/monthly`, `GET /reports/daily`, `GET /reports/products-by-venue`, `GET /reports/shift-distribution` |

The full request and response details are in the Swagger UI at `backend/public/v1/docs/`.

## Running locally

Requirements: PHP 8.2+ with the `pdo_mysql` extension, and MySQL or MariaDB. XAMPP works.

1. **Create the database:**
   ```bash
   mysql -u root -p -e "CREATE DATABASE cafe"
   mysql -u root -p cafe < database/schema.sql
   ```
2. **Install the PHP dependencies:**
   ```bash
   cd backend
   php ../composer.phar install
   ```
3. **Configure it.** Copy `backend/config.local.example.php` to `backend/config.local.php` and fill in your database details and a random `JWT_SECRET`. You can also set the same names as environment variables instead. `config.local.php` is git-ignored.
4. **Serve the project** with Apache, e.g. by putting the folder in XAMPP's `htdocs`. Then set `PROJECT_BASE_URL` in `frontend/utils/constants.js` to your backend URL, for example `http://localhost/cafe/backend/`.
5. **Create the first admin.** Register through the app, then promote your account:
   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'you@example.com';
   ```

## Tech stack

**Backend:** PHP 8.2, FlightPHP, PDO/MySQL, firebase/php-jwt, swagger-php
**Frontend:** HTML, CSS/SCSS, Bootstrap, jQuery, jQuery SPApp, DataTables, jQuery Validate, Toastr
**Hosting:** Apache (previously deployed on InfinityFree)

## License

Code: [MIT](LICENSE). The site layout is based on a free template by HTML Codex, licensed under CC BY 4.0 (see [LICENSE.txt](LICENSE.txt)).
