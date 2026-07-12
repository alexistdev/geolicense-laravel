# GeoLicense — Laravel + Blade

A faithful port of **GeoLicense** (online software-license management) from the
original **Spring Boot + Vue** stack (`BE/geolicense` + `FE`) to a single
server-rendered **Laravel 13 + Blade** monolith.

It keeps the same domain, database naming (`glo_*` tables), business rules and
the client-facing license API contract, while replacing the split REST-backend /
Vue-SPA with idiomatic Laravel: session auth for the UI, Blade + Tailwind for the
views, and JWT only where the original also used it — the license activation token.

## Stack

- Laravel 13 / PHP 8.4, MySQL (`geolicense_laravel`)
- Blade + Tailwind 4 (Vite) — the exact dark theme ported from the Vue app
- Alpine.js for modals / collapsible menus
- `firebase/php-jwt` for HMAC-SHA256 license tokens

## Features

**Admin** — dashboard, users, products (CRUD), license types (CRUD), billing &
invoice verification (approve → issue license / reject).
**User** — dashboard, marketplace (browse products & plans), order a plan,
invoices (pay via bank transfer), licenses (keys, seats, machine activations).
**License API** (public, stateless) — `POST /api/v1/licenses/activate` and
`/verify`, returning the same `{status, messages, payload}` envelope as the
Spring server, so existing clients (incl. `CLIENT/LARAVEL`) stay compatible.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
# create the MySQL schema, then set DB_* in .env
php artisan migrate --seed
npm install && npm run build
php artisan serve            # http://127.0.0.1:8000
```

The `.env` ships pointed at MySQL `geolicense_laravel`. To run without a DB
server, switch `DB_CONNECTION=sqlite` and `touch database/database.sqlite`.

## Demo accounts (password `1234`)

| Role  | Email                   | Notes |
|-------|-------------------------|-------|
| ADMIN | `alexistdev@gmail.com`  | full admin console |
| ADMIN | `admin@gmail.com`       | secondary admin |
| USER  | `user@gmail.com`        | has 1 active license + 1 unpaid invoice to demo the payment loop |

## License API quick test

```bash
# activate (use a seeded license key from the user's License page)
curl -X POST http://127.0.0.1:8000/api/v1/licenses/activate \
  -H 'Content-Type: application/json' \
  -d '{"licenseKey":"GEOLIC-XXXX-YYYY","machineId":"MACHINE-1","osInfo":"macOS"}'

# verify (token from the activate response)
curl -X POST http://127.0.0.1:8000/api/v1/licenses/verify \
  -H 'Content-Type: application/json' \
  -d '{"token":"<token>","machineId":"MACHINE-1"}'
```

HTTP status mapping (as in the original): seat limit → **429**, expired → **402**,
invalid/mismatched token → **403**.

## Tests

```bash
php artisan test
```

Feature tests cover auth + role gating, the license activate/verify contract
(incl. 429/402/403), and the order → payment → validate → license lifecycle.

## Notable deviations from the original (intentional)

- Session auth (web guard) replaces the Redis + `SID` cookie + JWT scheme for the
  UI; JWT is retained only for license tokens.
- Native `deleted_at` soft deletes replace the `is_deleted` boolean idiom.
- Seed data adds an explicit `user@gmail.com` USER and an unpaid invoice so both
  role dashboards and the full payment loop are demoable out of the box.
