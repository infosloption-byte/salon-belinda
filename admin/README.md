# Salon Belinda — Admin (React)

Scaffold for the Phase 2 admin panel: Vite + React 19 + TypeScript + Tailwind v4,
mirroring the `frontend`/`shop` app conventions in the main repo.

## What's here

- **Auth**: `src/context/AuthContext.tsx` + `src/lib/api.ts` — expects a Laravel
  Sanctum token from `POST /api/admin/login`, stores it in `localStorage`, sends
  it as a `Bearer` header, and logs the user out automatically on a 401.
- **Layout**: `Sidebar` + `Topbar` + `AdminLayout`, with the 12 module links from
  `SAAS-ROADMAP.md`'s suggested Phase 2 order.
- **Pages**: `Login`, `Dashboard` (placeholder stats/activity), and a shared
  `ComingSoon` placeholder for every module not yet ported.
- **Theme**: warm ivory/ plum-wine / gold "vanity mirror" palette — `mirror-card`
  utility gives cards the thin gold top-hairline; `.arch` gives the logo mark its
  mirror-frame curve. All tokens live in `src/index.css`.

## Wiring it up to the backend

This scaffold assumes endpoints that don't exist yet (that's the rest of Phase 2):

- `POST /api/admin/login` → `{ token, user }`
- `POST /api/admin/logout`
- `GET /api/admin/me` → current user

Once `laravel/sanctum` is installed and `Api/Admin/AuthController` exists, this
app should work against it with no changes. Each `ComingSoon` route in
`src/App.tsx` is where a real module page goes — swap the `<ComingSoon />`
element for the real page as you port each Blade view.

## Local dev

```bash
npm install
cp .env.example .env   # point VITE_API_URL at your backend
npm run dev
```

## Docker

Builds the same way as `frontend`/`shop`. Add this service to the root
`docker-compose.yml`:

```yaml
  admin:
    build:
      context: ./admin
      args:
        - VITE_API_URL=${VITE_API_URL:-https://api.yoursalon.example.com/api}
    container_name: salon_admin
    restart: unless-stopped
    ports:
      - "127.0.0.1:9033:80"
```

And extend the backend's `CORS_ALLOWED_ORIGINS` env var to include the admin
app's origin.
