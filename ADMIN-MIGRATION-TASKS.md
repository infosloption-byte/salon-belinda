# Admin Migration Task Tracker

Living checklist for SAAS-ROADMAP.md Phase 2 ("Separate Laravel into a pure
API + scaffold the React admin"). Update this file in the same commit as any
module you finish — check the box, add the date/commit note. This is the
single source of truth for "what's done vs what's left"; SAAS-ROADMAP.md
Phase 2 only tracks phase-level progress, not per-module state.

Each module needs all three of: **API controller** (JSON, under
`app/Http/Controllers/Api/Admin/`), **routes** (`routes/api.php`, inside the
`admin/` prefix group), and **React page** (`admin/src/pages/`, wired into
`App.tsx` + `Sidebar.tsx`, replacing its `ComingSoon` placeholder). A module
isn't "done" until all three are checked — a controller with no route, or a
route with no page swapped in, is still in progress.

## Infra / foundation

- [x] `laravel/sanctum` installed, token auth working (`Api/Admin/AuthController`)
- [x] `staff_or_admin` / `admin` middleware re-usable as route middleware under `/api/admin/*`
- [x] `admin/` Vite+React+TS app scaffolded (routing, auth context, api client, layout shell)
- [x] `admin` service added to `docker-compose.yml`

## Modules

| Module | API controller | Route | React page | Status |
|---|---|---|---|---|
| Auth (login/logout/me) | ✅ | ✅ | ✅ | **Done** |
| Dashboard | ✅ | ✅ | ✅ | **Done** |
| Services (+ categories) | ✅ | ✅ | ✅ | **Done** |
| Products (+ categories) | ✅ | ✅ | ✅ | **Done** — 2026-07-23 |
| Gallery (+ categories) | ❌ | ❌ | ❌ (ComingSoon) | Not started |
| Wedding Albums | ❌ | ❌ | ❌ (ComingSoon) | Not started — was missing from Sidebar entirely until 2026-07-23, added as placeholder |
| Appointments | ❌ | ❌ | ❌ (ComingSoon) | Not started |
| Jobs (daily ops: items/payments/receipt) | ❌ | ❌ | ❌ (ComingSoon) | Not started — was missing from Sidebar entirely until 2026-07-23, added as placeholder. Largest single module (job items, payments, PDF receipt) |
| Staff | ❌ | ❌ | ❌ (ComingSoon) | Not started |
| Customers | ❌ | ❌ | ❌ (ComingSoon) | Not started |
| Orders (+ invoice PDF) | ❌ | ❌ | ❌ (ComingSoon) | Not started |
| Testimonials | ❌ | ❌ | ❌ (ComingSoon) | Not started |
| Contact Messages | ❌ | ❌ | ❌ (ComingSoon) | Not started |
| Reports (×6: revenue, best-sellers, low-stock, appointments, outstanding-balances, staff-commission) | ❌ | ❌ | ❌ (ComingSoon) | Not started |
| Activity Log | ❌ | ❌ | ❌ (ComingSoon) | Not started |
| Users (admin/staff account mgmt) | ❌ | ❌ | ❌ (ComingSoon) | Not started |
| My Account (self-service, every role) | ❌ | ❌ | — (not yet in nav) | Not started |

## Cutover housekeeping (do once every module above is done)

- [ ] `barryvdh/laravel-dompdf` — confirm server-side PDF stays (Orders invoice, Jobs receipt); decision already made in SAAS-ROADMAP.md, just needs re-confirming once those two modules are ported
- [ ] Delete the 17 Blade `Admin/*` controllers and 44 Blade views under `resources/views/admin/*`
- [ ] Empty out `routes/web.php` down to a health check
- [ ] Remove `staff_or_admin`/`admin` session-middleware usage from `routes/web.php` (moot once web.php is emptied)

## Suggested build order (unchanged from SAAS-ROADMAP.md, Jobs/Albums added)

Gallery → Wedding Albums → Appointments → Jobs → Staff → Customers → Orders →
Testimonials → Contact Messages → Reports → Activity Log → Users → My Account

---

*How to use this file:* pick the next unchecked module, build controller +
route + page together, flip its row to Done in the same commit, and note the
date. Don't let a module sit half-checked across commits.
