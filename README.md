# Salon Belinda — Monorepo

Three independent apps, each deployable on its own:

```
frontend/   React + TS + Vite — the marketing site (salonbelinda.com)
shop/       React + TS + Vite — the online shop (shop.salonbelinda.com)
backend/    Laravel — shared API (products, orders, etc.) — not yet scaffolded
```

The shop is intentionally a **separate app**, not a route inside `frontend`. This keeps deploys,
scaling, and future backend wiring independent — the marketing site's "Shop" nav link just opens
the shop in a new tab.

## Local development

Run both apps side by side (they use fixed ports so they don't collide):

```bash
cd frontend && npm install && npm run dev   # http://localhost:5173
cd shop      && npm install && npm run dev  # http://localhost:5174
```

Each app has a `.env` (already checked in for local dev) pointing at the other:

- `frontend/.env` → `VITE_SHOP_URL=http://localhost:5174`
- `shop/.env` → `VITE_MAIN_SITE_URL=http://localhost:5173`

Click "Shop" in the frontend nav — it opens the shop app in a new tab. Click "Back to
salonbelinda.com" in the shop header — it links back.

## Production: subdomain routing

Following the same pattern as your other projects (master nginx container routing by `Host`
header to per-app Docker containers on a shared network), add two server blocks:

```nginx
# Main site
server {
    server_name salonbelinda.com www.salonbelinda.com;
    location / { proxy_pass http://frontend_container:PORT; }
}

# Shop, on its own subdomain
server {
    server_name shop.salonbelinda.com;
    location / { proxy_pass http://shop_container:PORT; }
}
```

Then in each app's production `.env` (or build-time env), set:

- `frontend`: `VITE_SHOP_URL=https://shop.salonbelinda.com`
- `shop`: `VITE_MAIN_SITE_URL=https://salonbelinda.com`

Both are static Vite builds (`npm run build` → `dist/`), so they can be served by nginx directly
or from small containers exactly like `ycusriya` and `ubiq` — no app server needed for either.

DNS: add an `A`/`CNAME` record for `shop` pointing at the same EC2 host, then request/attach a
cert for `shop.salonbelinda.com` (e.g. via `certbot --nginx -d shop.salonbelinda.com` or add it to
an existing wildcard cert for `*.salonbelinda.com`).

## Shop → Backend API contract (for when `backend` is built)

The shop currently places orders entirely client-side (see `Checkout.tsx`) and stores the cart in
`localStorage`. To wire it to Laravel later, the shop expects:

- `GET  /api/products` — returns the catalog (replaces `src/data/products.ts`)
- `POST /api/orders` — body: `{ lines: [{productId, quantity}], fulfilment, payment, customer }`,
  returns `{ orderNumber }`
- `GET  /api/orders/{orderNumber}` — for order lookup/status (optional, for later)

Everything in `shop/src/data/products.ts` maps 1:1 to a products table/migration, so switching
from mock data to the real API is a matter of swapping the import for a `fetch` call.
