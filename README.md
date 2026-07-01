# Order & Payment Management API

A Laravel 13 REST API for managing orders and payments, built with clean, layered architecture and an extensible **strategy-based** payment gateway system. Authentication is handled via JWT.

## Tech Stack

- **PHP** 8.4
- **Laravel** 13 + **Octane** 2 (FrankenPHP)
- **MySQL** 8
- **JWT** auth (`tymon/jwt-auth`)
- **Pest** 4 (tests) · **Larastan** 3 (static analysis) · **Pint** (formatting)

## Architecture

The code follows a layered design. A request flows top to bottom:

```
Route
  → FormRequest        (validation)
  → Controller         (thin; authorizes, delegates)
  → DTO                (typed input, built via ::fromRequest())
  → Service            (business rules, transactions)
  → Model              (persistence, domain behavior)
  → Result             (typed success/failure outcome)
  → API Resource       (response shaping)
  → ApiResponse        (consistent JSON envelope)
```

### Layer responsibilities

| Layer | Location | Role |
|-------|----------|------|
| Controllers | `app/Http/Controllers/Api/V1` | One action per endpoint; authorize + delegate to a service. Single-action controllers are invokable. |
| Form Requests | `app/Http/Requests` | Input validation rules. |
| DTOs | `app/DTOs` | Immutable, typed carriers built from requests via `fromRequest()`. |
| Services | `app/Services` | Business logic and DB transactions. Return `Result` objects. |
| Results | `app/Results` | Typed outcomes (`succeeded()` / `failed()` + `failureReason`) so controllers never guess. |
| Models | `app/Models` | Eloquent persistence + domain methods (e.g. `Order::place()`, `transitionTo()`). |
| Resources | `app/Http/Resources` | Shape models into JSON. |
| Enums | `app/Enums` | `OrderStatus`, `PaymentStatus`, `PaymentMethod`, `PaymentGateway`. |
| Value Objects | `app/ValueObjects` | `Money` (stored as minor units / integers), `Token`. |
| Util | `app/Util/ApiResponse` | Uniform response envelope (`status`, `message`, `meta`, `data`). |

### Payment gateway strategy

Payment processing is decoupled behind a strategy so new gateways require **minimal** changes:

```
app/Services/Payment/
├── PaymentProcessor.php           # interface: process(Order): Payment
├── PaymentProcessorFactory.php    # resolves a processor by PaymentMethod
├── CreditCardPaymentProcessor.php # concrete strategy
└── PaymentService.php             # payment listing (read) use-cases
```

Gateway credentials are read from `config/payment.php`, which is driven by `.env` (e.g. `CREDIT_CARD_API_KEY`, `CREDIT_CARD_SECRET`). A missing configuration throws `MissingGatewayConfigurationException`.

#### Adding a new gateway

1. Add a case to `App\Enums\PaymentMethod` (and `PaymentGateway` if it needs its own credentials).
2. Add its credentials block to `config/payment.php` and the matching `.env` keys.
3. Implement `App\Services\Payment\PaymentProcessor` in a new class.
4. Register it in `PaymentProcessorFactory::make()` with a new `match` arm.

No controller, route, or service changes are needed.

## API Endpoints

Base path: `/api/v1`. All routes except auth require an `Authorization: Bearer <token>` header.

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/register` | Register a user, returns a token. |
| POST | `/auth/login` | Log in, returns a token. |

### Orders
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orders` | List the user's orders (paginated; `?status=`, `?per_page=`). |
| POST | `/orders` | Create an order with items; total is calculated. |
| PUT/PATCH | `/orders/{order}` | Update a pending order. |
| DELETE | `/orders/{order}` | Delete an order (blocked if it has payments). |
| POST | `/orders/{order}/confirm` | Confirm a pending order and record a payment. |
| POST | `/orders/{order}/cancel` | Cancel an order. |

### Payments
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orders/{order}/payments` | List payments for a specific order (paginated; `?status=`, `?per_page=`). |
| GET | `/payments` | List all payments belonging to the authenticated user. |

**Query params for list endpoints:** `status` (`pending`, `successful`, `failed` for payments) and `per_page` (1–100, default 15).

## Running Locally (Composer + Octane)

**Requirements:** PHP 8.4, Composer, MySQL 8.

1. **Install & bootstrap** (installs deps, copies `.env`, generates the app key, migrates, builds assets):
   ```bash
   composer setup
   ```

2. **Configure the database** in `.env` (defaults expect a local MySQL database named `assessment`):
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=assessment
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. **Generate the JWT secret** (if not already set):
   ```bash
   php artisan jwt:secret
   ```

4. **Serve with Octane.** Install the Octane runtime once, then start it:
   ```bash
   php artisan octane:install --server=frankenphp
   php artisan octane:start --host=0.0.0.0 --port=8000
   ```
   The API is now available at `http://localhost:8000/api/v1`.

> For a plain (non-Octane) dev loop with logs, queue and Vite, use `composer dev` instead.

## Running with Docker

A production-style image (FrankenPHP + Octane) and a MySQL service are provided. Container credentials live in `.env.docker`.

1. **Build and start:**
   ```bash
   docker compose up --build
   ```
   The entrypoint caches config/routes/views, waits for MySQL, runs migrations, then boots Octane (FrankenPHP).

   - API: `http://localhost:8000/api/v1`
   - MySQL is exposed on host port **3307** (container `mysql:3306`).

2. **Stop:**
   ```bash
   docker compose down          # keep data
   docker compose down -v       # also remove the database volume
   ```

## Testing

```bash
php artisan test --compact          # run the suite
php artisan test --filter=Payment   # payments only
composer test                       # tests + PHPStan
```

Feature tests live in `tests/Feature` (Auth, Order, Payment), using Pest with `RefreshDatabase` and model factories.
