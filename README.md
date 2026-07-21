# Salon SaaS — Monorepo

Independently deployable apps, converting from a single-brand salon platform into a
white-label, multi-tenant SaaS. See `SAAS-ROADMAP.md` for the full conversion plan.

```
frontend/   React + TS + Vite — the public marketing/booking site
shop/       React + TS + Vite — the online shop (products, cart, checkout)
backend/    Laravel 11 — API + (currently) a server-rendered Blade admin panel,
            being split into a pure API (see roadmap Phase 2)
```

The shop is intentionally a **separate app**, not a route inside `frontend`. This keeps deploys,
scaling, and future backend wiring independent — the marketing site's "Shop" nav link just opens
the shop in a new tab.

## Local development

```bash
cd frontend && cp .env.example .env && npm install && npm run dev   # http://localhost:5173
cd shop      && cp .env.example .env && npm install && npm run dev  # http://localhost:5174
cd backend   && cp .env.example .env && composer install && php artisan migrate --seed && php artisan serve  # http://localhost:8000
```

Each frontend app's `.env` sets its own salon branding (`VITE_SALON_NAME`, `VITE_SALON_ADDRESS`,
etc. — see `.env.example` in each app) plus where to find the other apps:

- `frontend/.env` → `VITE_SHOP_URL=http://localhost:5174`, `VITE_API_URL=http://localhost:8000/api`
- `shop/.env` → `VITE_MAIN_SITE_URL=http://localhost:5173`, `VITE_API_URL=http://localhost:8000/api`

This branding config is a **stop-gap for single-tenant deployments**. Once the platform is
multi-tenant, it's replaced by a runtime fetch to a tenant-config API endpoint, keyed off the
subdomain, so a salon owner can edit their own branding without a rebuild — see
`SAAS-ROADMAP.md` Phase 4.

## Production: subdomain routing

Master nginx (or equivalent) container routing by `Host` header to per-app containers on a shared
network:

```nginx
# Main site
server {
    server_name yoursalon.example.com www.yoursalon.example.com;
    location / { proxy_pass http://frontend_container:PORT; }
}

# Shop, on its own subdomain
server {
    server_name shop.yoursalon.example.com;
    location / { proxy_pass http://shop_container:PORT; }
}

# API
server {
    server_name api.yoursalon.example.com;
    location / { proxy_pass http://backend_container:PORT; }
}
```

Then in each frontend app's production build-time env:

- `frontend`: `VITE_SHOP_URL=https://shop.yoursalon.example.com`, `VITE_API_URL=https://api.yoursalon.example.com/api`
- `shop`: `VITE_MAIN_SITE_URL=https://yoursalon.example.com`, `VITE_API_URL=https://api.yoursalon.example.com/api`

`frontend` and `shop` are static Vite builds (`npm run build` → `dist/`), served by nginx directly
or from small containers — no app server needed for either. `backend` is a Laravel app (PHP-FPM
or `php artisan serve` behind nginx).

See `docker-compose.yml` for a working example wiring all three together, and
`SAAS-ROADMAP.md` for how this evolves into `{tenant}.yoursalon.com` wildcard subdomains once
multi-tenancy lands.

## Backend → frontend/shop API contract

- `GET  /api/services`, `/api/gallery`, `/api/albums`, `/api/testimonials` — public site content
- `POST /api/appointments`, `/api/contact`, `POST /api/testimonials` — public site actions
- `GET  /api/products`, `/api/products/{slug}` — shop catalog
- `POST /api/orders`, `GET /api/orders/{orderNumber}` — shop checkout + order lookup

Admin operations (staff, customers, orders management, reports, etc.) currently live behind
session-authenticated Blade views at `/admin/*` in `backend`. These are being rebuilt as a
token-authenticated (`laravel/sanctum`) JSON API plus a new standalone `admin/` React app — see
`SAAS-ROADMAP.md` Phase 2.
