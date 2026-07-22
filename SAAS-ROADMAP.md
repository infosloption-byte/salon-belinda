# Salon Belinda → Multi-Tenant Salon SaaS — Conversion Roadmap

**Repo analyzed:** `salon-belinda` (frontend + shop + backend)
**Current state:** single-brand salon platform, hardcoded to "Salon Belinda"
**Target state:** white-label, multi-tenant SaaS — any salon can register and get their own branded frontend, shop, and admin

---

## Part 1 — Gap Analysis (current platform vs. SaaS requirement)

### 1.1 What exists today

| Layer | Stack | Role |
|---|---|---|
| `frontend` | React + Vite + TS | Public marketing site (About, Gallery, Reviews, Contact, booking) |
| `shop` | React + Vite + TS | E-commerce storefront (products, cart, checkout, orders) |
| `backend` | Laravel 11 | Does **three jobs at once**: (1) serves a small public JSON API for frontend+shop, (2) renders a full **Blade** admin panel (session-auth, server-rendered), (3) owns the DB |

Backend detail, confirmed from the code:
- **44 Blade views** under `backend/resources/views/admin/*` (dashboard, customers, appointments, jobs, staff, products, product-categories, services, gallery, gallery-categories, albums, orders, testimonials, contact-messages, reports ×6, activity-log, users, login) — this is the entire admin surface that has to become React + API.
- **17 Admin controllers** (`app/Http/Controllers/Admin/*`) driving those views, using classic `redirect()`/`view()` responses, not JSON.
- **8 public API controllers** (`app/Http/Controllers/Api/*`) — small, already JSON, already the right shape for frontend/shop.
- **Auth**: plain Laravel session/cookie auth (`web` guard), a `role` column (`admin`/`staff`) on `users`, no API tokens, **no `laravel/sanctum` installed at all**.
- **Database**: 25 migrations, **zero tenant concept** — no `tenant_id`/`organization_id` anywhere. Every table (`services`, `products`, `orders`, `staff`, `customers`, `jobs_salon`, etc.) is implicitly "the one salon."
- **CORS**: hardcoded to two localhost origins via `CORS_ALLOWED_ORIGINS`.

### 1.2 Branding — the good news

Branding is centralized more than you'd expect for a single-tenant app:
- `frontend/src/data/site.ts` — one object: name, owner, address, email, phone, hours, socials.
- `shop/src/data/site.ts` — one object: name, tagline, address, contact, currency, delivery fee.
- Both are imported into `Logo.tsx`, `Seal.tsx`, `Footer.tsx`, `Navbar.tsx`/`Header.tsx`, and a handful of pages (`Home`, `About`, `Contact`, `Gallery`, `Reviews`, `Checkout`, `OrderConfirmation`).
- Backend branding lives in `config/app.php` (`APP_NAME`), `.env`, and is duplicated as literal text inside **9 Blade/mail files** (`admin/login.blade.php`, `layouts/admin.blade.php`, `orders/invoice.blade.php`, `orders/show.blade.php`, `jobs/receipt.blade.php`, `emails/layout.blade.php`, 3 Mail classes).

This means de-hardcoding is very tractable — you're not hunting brand strings across hundreds of files, but the config objects need to go from **static TS constants** to **API-driven, per-tenant runtime data**, and the Blade brand strings disappear entirely once Blade admin is retired.

### 1.3 Core gaps to close for SaaS

1. **No tenant model.** Nothing in the DB, auth, or API scopes data by salon. This is the single biggest structural gap — bigger than the admin rewrite.
2. **No signup/onboarding flow.** There's no way for a new salon owner to register and get their own instance.
3. **No subdomain/domain routing.** Nothing resolves "which salon is this request for."
4. **Admin is server-rendered, not an API client.** Blocks a separate React admin and blocks any non-Blade client.
5. **No token-based auth.** Sanctum isn't installed; can't cleanly serve a decoupled React admin (or a future mobile app) yet.
6. **No billing/subscription layer.** Nothing tracks what plan a tenant is on, usage limits, or payment status for the *platform itself* (note: `PaymentGatewayStub` exists but that's for the salon's own shop checkout, a different concern).
7. **No platform-level (super-admin) control plane.** Today "admin" only means "admin of the one salon." A SaaS needs a second, higher tier: the platform operator who can see/manage all tenants.
8. **Static branding config.** `site.ts` files are compile-time constants, not runtime/per-tenant data.

---

## Part 2 — Target Architecture

```
                         ┌─────────────────────────────┐
                         │   marketing/landing site     │  (new, optional)
                         │   → salon owners sign up here│
                         └───────────────┬──────────────┘
                                          │ POST /api/register-tenant
                                          ▼
┌───────────────┐   ┌───────────────┐   ┌────────────────┐   ┌──────────────────────┐
│   frontend     │   │     shop      │   │     admin       │   │      backend         │
│  React (per-   │   │  React (per-  │   │  React (SPA)    │   │  Laravel 11 — API-   │
│  tenant site)  │   │  tenant shop) │   │  tenant admin +  │   │  only, Sanctum auth, │
│  {tenant}.     │   │  shop.{tenant}│   │  super-admin     │   │  tenant-scoped        │
│  platform.com  │   │  .platform.com│   │  panel           │   │  everything           │
└───────┬───────┘   └───────┬───────┘   └────────┬─────────┘   └──────────┬────────────┘
        │  GET /api/tenant/config, /api/services, /api/gallery...          │
        └──────────────────────────────┬────────────────────────────────┘
                                        ▼
                              tenant_id-scoped MySQL/Postgres
```

Key architectural decisions:

- **Tenant resolution:** subdomain-based (`{slug}.platform.com` for the site, `shop.{slug}.platform.com` or `{slug}.platform.com/shop` for the shop). Custom domains (salon brings their own `.com`) can be layered on later via a `tenants.custom_domain` column + a lookup middleware — don't build this on day one, but don't paint yourself into a corner either (see Phase 3).
- **Multi-tenancy strategy:** **single database, shared schema, `tenant_id` on every table** (not database-per-tenant). Reasoning: salon-SaaS tenants are small (one shop's worth of data), you want simple backups/migrations/cross-tenant reporting for yourself as the platform operator, and shared-schema is dramatically less operational overhead. If a future enterprise customer demands hard isolation, you can move *that one tenant* to its own DB later — don't over-engineer now.
- **Auth:** `laravel/sanctum`, **token-based** (not Sanctum's cookie/SPA mode) because frontend/shop/admin will live on different subdomains and token auth avoids first-party-cookie/CORS headaches across subdomains. Two token scopes: tenant-level (`tenant-admin`, `staff`) and platform-level (`platform-admin`) with an `is_platform_admin` flag, kept separate from the tenant role system.
- **Branding:** move `site.ts` from a static export to a `useTenantConfig()` hook that fetches `GET /api/tenant/config` once at boot (logo URL, name, colors, contact info, hours, socials) and provides it via React context. Same pattern in admin and shop. Backend serves this from a new `tenants` + `tenant_settings` table.

---

## Part 3 — Phased Roadmap

> Ordered to match what you asked for first (de-brand → separate admin), then builds out real multi-tenancy on top. Each phase is independently shippable — you can run this in production after every phase.

### Phase 1 — De-hardcode branding (frontend + shop) — *your first ask*

Goal: stop the apps from being compiled with "Salon Belinda" baked in, without yet building full multi-tenancy.

- [ ] `frontend/src/data/site.ts` and `shop/src/data/site.ts`: replace literal values with generic placeholders / `import.meta.env.VITE_*` variables (`VITE_SALON_NAME`, `VITE_SALON_ADDRESS`, `VITE_SALON_EMAIL`, `VITE_SALON_PHONE`, etc.), each with a sane generic default (e.g. `"Your Salon"`).
- [ ] Sweep the ~15 components/pages that import `site` (`Logo.tsx`, `Seal.tsx`, `Footer.tsx`, `Navbar.tsx`/`Header.tsx`, `Home`, `About`, `Contact`, `Gallery`, `Reviews`, `Checkout`, `OrderConfirmation`, `CartContext.tsx`, `gallery.ts`) — confirm nothing else has a second hardcoded "Belinda" string outside `site.ts`.
- [ ] Replace the actual logo image assets in `frontend/public` / `shop/public` with a generic placeholder mark.
- [ ] Backend: change `APP_NAME` default in `.env.example` / `config/app.php`, and update the ~9 Blade/mail files with hardcoded brand text to pull from `config('app.name')` instead of literal strings.
- [ ] Update `MAIL_FROM_NAME`, `SALON_NOTIFY_EMAIL`, seeders (`AdminUserSeeder`, `StaffSeeder`, `AlbumSeeder`) so a fresh install doesn't seed Belinda-specific data.

**Outcome:** the codebase is brand-neutral and configurable per-deployment via env vars. This is *not yet* multi-tenant (still one salon per deployment) — that's Phase 4+. But it unblocks you immediately and is a prerequisite for the dynamic-config work later.

### Phase 2 — Separate Laravel into a pure API + scaffold the React admin — *your second ask*

Goal: Blade admin retired, Laravel becomes API-only, new `admin` React app takes over.

> **Per-module status now lives in `ADMIN-MIGRATION-TASKS.md`** — check that file for what's actually done vs. pending; this checklist only tracks phase-level milestones.

- [x] `composer require laravel/sanctum`, publish config, add token auth (`personal_access_tokens` table).
- [~] Build `Api/Admin/*` controllers mirroring the 17 existing `Admin/*` Blade controllers 1:1, but returning JSON instead of views. **4 of 17 done** (Auth, Dashboard, Services, Products) — see `ADMIN-MIGRATION-TASKS.md` for the rest: `CustomerController`, `AppointmentController`, `StaffController`, `ProductCategoryController`(✅ done alongside Products), `GalleryController`, `GalleryCategoryController`, `AlbumController`, `OrderController`, `TestimonialController`, `ContactMessageController`, `ReportController` (all 6 report types), `ActivityLogController`, `UserController`, `SalonJobController`.
- [x] Re-implement the `staff_or_admin` / `EnsureUserIsAdmin` middleware as reusable route middleware under `/api/admin/*` (kept the same aliases, applied to the API group instead of session middleware).
- [ ] Move `routes/web.php` admin routes into `routes/api.php` under `/api/admin/*` for good, and empty out `routes/web.php` — **not yet**, both route files still run in parallel by design until every module is ported (see Outcome note below).
- [x] Scaffold `admin/` as a new Vite + React + TS app: routing (React Router), auth context (Sanctum token), API client (fetch wrapper with auth header + 401 handling), and a layout shell.
- [~] Rebuild each admin screen as a React page/module. **Done: Login, Dashboard, Services, Products.** Remaining 11 (Gallery, Wedding Albums, Appointments, Jobs, Staff, Customers, Orders, Testimonials, Contact Messages, Reports, Activity Log, Users) are `ComingSoon` placeholders in the sidebar — note Jobs and Wedding Albums weren't even wired into the sidebar until this pass. Old Blade views still running in parallel as designed.
- [ ] `barryvdh/laravel-dompdf` usage (invoices/receipts) — decision made (keep server-side), not yet re-implemented in the API controllers for Orders/Jobs since those modules aren't ported yet.
- [x] Update `docker-compose.yml` / Dockerfiles to add the `admin` service; CORS already includes the admin origin.

**Outcome (current, not yet final):** `frontend`, `shop`, `admin` are independently deployable; `backend` still serves both the legacy Blade admin (`routes/web.php`, 17 controllers/44 views, untouched) *and* the new JSON API (`routes/api.php`, growing) side by side on purpose — that's the parallel-run strategy so nothing breaks mid-migration. It becomes fully "API-only" only once every row in `ADMIN-MIGRATION-TASKS.md` is checked and the Blade files are deleted.

### Phase 3 — Multi-tenancy foundation (data layer)

- [ ] New `tenants` table: `id, slug (unique), name, custom_domain (nullable, unique), status (active/suspended/trial), plan_id, trial_ends_at, timestamps`.
- [ ] New `tenant_settings` table (or a JSON column on `tenants`): logo_url, primary_color, address, email, phone, hours, social links, shop currency/delivery-fee — this replaces `site.ts` as the source of truth.
- [ ] Add `tenant_id` (FK, indexed) to every tenant-scoped table: `service_categories, services, appointments, testimonials, gallery_items, gallery_categories, albums, album_photos, products, product_categories, orders, order_items, contact_messages, staff, customers, jobs_salon, job_items, job_payments, admin_activity_logs, users`.
- [ ] Laravel **global scope** (`TenantScope`) applied via a `BelongsToTenant` trait on all the above models, auto-filtering every query by the current tenant. Resolve "current tenant" from: (a) subdomain on public API requests, (b) authenticated user's `tenant_id` on admin API requests.
- [ ] `ResolveTenant` middleware: parses subdomain (or `X-Tenant-Slug` header for local dev), loads the tenant, aborts 404 if unknown/suspended, binds it into the container for the request lifecycle.
- [ ] Data migration script: assign the existing Belinda data to a `tenant_id = 1` row seeded as your first real tenant, so the current install becomes "tenant zero" rather than being thrown away.
- [ ] Write tenant-isolation tests (a request under tenant A can never read/write tenant B's rows) — this is the single most important security property of the whole system; treat it as a hard release gate, not a nice-to-have.

### Phase 4 — Dynamic branding across all three frontends

- [ ] `GET /api/tenant/config` (public, subdomain-scoped) returning the `tenant_settings` payload.
- [ ] `frontend`, `shop`, `admin`: replace the Phase-1 static env-var `site` object with a `TenantConfigProvider` that fetches this endpoint on boot, caches it, and exposes it via context/hook. Loading and error (unknown-tenant) states need real UI (not blank screens).
- [ ] Theming: expose `primary_color` etc. as CSS custom properties set at runtime (`document.documentElement.style.setProperty`) so each tenant's site actually looks different, not just says a different name.
- [ ] Logo: tenant-uploaded logo served from storage (S3/Spaces) with a fallback default mark.
- [ ] Admin panel gets a "Branding" settings screen (logo upload, colors, contact info, hours) writing to `tenant_settings` — this is what a salon owner uses to configure their own look, replacing what used to be a code change.

### Phase 5 — Tenant registration / onboarding

- [ ] Public "Sign up" flow (can live on a new small marketing site, or as a route in `admin`): salon name → slug availability check (`GET /api/tenants/check-slug`) → owner email/password → creates `tenants` row + first `users` row (`role=admin`, `tenant_id=new`) + seeds sensible defaults (default service categories, empty product catalog, trial plan).
- [ ] Slug → subdomain provisioning: if using wildcard DNS (`*.platform.com`) this is automatic; document the DNS/SSL wildcard cert requirement now so infra isn't a surprise later (Phase 9).
- [ ] Welcome email + "getting started" checklist in the admin dashboard (add your first service, upload a logo, add staff...).
- [ ] Guard rails: reserved slugs (`www`, `api`, `admin`, `shop`, `app`), slug format validation, rate-limit registration endpoint.

### Phase 6 — Subscription & billing (platform-level, separate from the salon's own shop checkout)

- [ ] `plans` table (name, price, billing interval, limits — e.g. max staff, max products, max monthly appointments).
- [ ] `tenants.plan_id`, `subscription_status`, integrate a real payment processor (Stripe is the standard choice for SaaS billing — separate integration from the existing `PaymentGatewayStub` used for salon customers' shop orders, don't conflate the two).
- [ ] Middleware/service to enforce plan limits (e.g. block creating an 11th staff member on a plan capped at 10) with clear upgrade prompts in the admin UI.
- [ ] Billing history + invoice screen in admin for the tenant owner; webhook handling for payment success/failure/cancellation.

### Phase 7 — Platform Super-Admin panel

- [ ] `users.is_platform_admin` (separate from tenant `role`) or a distinct `platform_admins` table — keep this identity space clearly separate from any tenant's own `admin` role, since a tenant admin must never be able to see other tenants' data by accident.
- [ ] New section in `admin` app (or a separate `platform-admin` route tree) gated by platform-admin tokens: tenant list, tenant detail (usage, plan, status), suspend/reactivate tenant, impersonate-tenant-admin (for support, with audit logging), platform-wide revenue/usage reporting.
- [ ] Support tooling: ability to view a tenant's activity log, reset a tenant admin's password, extend a trial.

### Phase 8 — Hardening & cutover

- [ ] Security review pass focused on tenant isolation (re-run/expand the Phase 3 isolation tests against every new endpoint added in Phases 4–7).
- [ ] Rate limiting per-tenant (not just globally) on public endpoints (`appointments`, `contact`, `orders`, `testimonials`) to stop one tenant's traffic/abuse from affecting others.
- [ ] Load-test subdomain resolution + tenant scope query performance (composite indexes on `(tenant_id, ...)` for hot tables like `orders`, `appointments`, `products`).
- [ ] Delete the last of the Blade admin views/controllers once every module is confirmed working in React admin.
- [ ] Update `README.md`s (root, `backend/README.md`, `frontend/README.md`) to reflect the new 4-app architecture and multi-tenant setup instructions.

### Phase 9 — Infrastructure

- [ ] Wildcard DNS (`*.platform.com`) + wildcard TLS certificate.
- [ ] `docker-compose.yml` updated for 4 services (frontend, shop, admin, backend) + shared DB; confirm nginx configs (`frontend/nginx.conf`, `shop/nginx.conf`) pattern extends cleanly to `admin/nginx.conf`.
- [ ] CI/CD: build/deploy pipeline per app, DB migrations run safely against the shared multi-tenant schema (never destructive without a maintenance window).
- [ ] Backups: since it's shared-schema, back up the whole DB; document a per-tenant data-export capability for GDPR-style "give me my data" / offboarding requests.

---

## Suggested execution order (condensed)

1. **Phase 1** — de-brand frontend/shop (quick, low-risk, unblocks everything else) ✅ *matches your first ask*
2. **Phase 2** — Laravel → API-only, build React admin module-by-module ✅ *matches your second ask*
3. **Phase 3** — tenant_id + global scopes (the real architectural pivot)
4. **Phase 4** — dynamic per-tenant branding (this is where Phase 1's env-var placeholders graduate to true per-tenant runtime config)
5. **Phase 5** — signup/onboarding
6. **Phase 6** — billing
7. **Phase 7** — super-admin panel
8. **Phase 8–9** — hardening, cutover, infra

---

## Open decisions worth pinning down before Phase 3

- [ ] Final subdomain scheme: `{slug}.platform.com` + `shop.{slug}.platform.com`, or `{slug}.platform.com` + `{slug}.platform.com/shop`? (affects CORS, cookies, and the admin app's own routing)
- [ ] Custom domains for tenants — in scope for v1 or explicitly deferred?
- [ ] Shared-schema (recommended above) vs. database-per-tenant — confirm you're comfortable with shared-schema before Phase 3, since reversing this later is expensive.
- [ ] Payment processor for platform billing (Stripe strongly recommended for SaaS subscriptions; keep it separate from whatever the salons themselves use for their shop checkout).

---

*This document is meant to be followed top-to-bottom. Each checkbox is a concrete, shippable unit of work. When you're ready, tell me which phase to start on and I'll break it into actual code changes against the repo.*
