# Salon Belinda — Backend

Laravel API + admin dashboard shared by the `frontend` (main site) and `shop` apps.

## Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate

# Easiest local option: SQLite
touch database/database.sqlite
# (.env already defaults to DB_CONNECTION=sqlite — edit it if you'd rather
# point at the shared MySQL container instead)

php artisan migrate --seed
php artisan storage:link
php artisan serve
```

The API will be at `http://localhost:8000`. Point `frontend/.env` and
`shop/.env` at it:

```
# frontend/.env and shop/.env
VITE_API_URL=http://localhost:8000/api
```

## Admin Dashboard

Visit `http://localhost:8000/admin`.

Seeded login:
- **Email:** admin@salonbelinda.com
- **Password:** password

Change this password (or create your own admin user and delete the seeded
one) before deploying anywhere public.

From the dashboard you can manage:
- **Appointments** — view bookings from the site, update status, delete
- **Reviews** — approve/reject testimonials before they show publicly
- **Gallery** — add/remove portfolio photos by category
- **Services** — manage categories and the service menu/pricing
- **Products** — full CRUD for the shop catalog, stock counts, badges
- **Orders** — view orders, change status, mark COD/Bank orders as paid
- **Messages** — the contact form inbox

## Public API

All endpoints are unauthenticated (read the appropriate controller in
`app/Http/Controllers/Api` for validation rules).

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/services` | Service menu by category |
| GET | `/api/gallery?category=` | Gallery items, optional category filter |
| GET | `/api/testimonials` | Approved reviews + average rating |
| POST | `/api/testimonials` | Submit a review (goes to "pending" for approval) |
| POST | `/api/appointments` | Book an appointment |
| POST | `/api/contact` | Contact form |
| GET | `/api/products?category=&q=&sort=&inStock=` | Shop catalog with filters |
| GET | `/api/products/{slug}` | Single product |
| POST | `/api/orders` | Place an order (see below) |
| GET | `/api/orders/{orderNumber}` | Look up an order |

### Placing an order

```json
POST /api/orders
{
  "lines": [{ "productId": 1, "quantity": 2 }],
  "fulfilment": "delivery",
  "payment": "card",
  "customer": {
    "fullName": "Nimasha Perera",
    "phone": "0771234567",
    "email": "nimasha@example.com",
    "address": "12 Lake Road",
    "city": "Galle",
    "notes": ""
  }
}
```

Prices, stock, and totals are always calculated server-side from the
database — never trust client-sent prices.

## Payments — currently simulated

**No payment gateway is connected yet.** `payment: "card"` orders are
auto-marked `paid` with a mock transaction ID via
`app/Services/PaymentGatewayStub.php`, so you can run the full order cycle
(place order → paid → fulfil → complete) end to end before wiring up a real
gateway. `cod` and `bank` orders stay `payment_status: pending` until you
mark them paid from the admin dashboard.

To go live later:
1. Add real credentials to `.env` (see `PAYMENT_GATEWAY_KEY` /
   `PAYMENT_GATEWAY_SECRET` in `config/services.php`).
2. Replace the body of `PaymentGatewayStub::charge()` with a real API call.
3. Handle the gateway's webhook to confirm payment asynchronously instead of
   assuming success inline.

Nothing else needs to change — `OrderController` only talks to that one
class.

## Notes

- CORS origins for the frontend/shop dev servers are set in `.env` via
  `CORS_ALLOWED_ORIGINS` (see `config/cors.php`).
- Gallery/product images are stored as plain URLs for now (upload to your
  own storage/CDN and paste the link into the admin forms). Swapping to
  direct file uploads later just means adding a file input + `Storage::disk
  ('public')` calls in `GalleryController`/`ProductController`.
- `php artisan migrate:fresh --seed` resets and reseeds everything from
  scratch if you want to start over.
