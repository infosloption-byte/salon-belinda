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
| Gallery (+ categories) | ✅ | ✅ | ✅ | **Done** — 2026-07-23 |
| Wedding Albums | ✅ | ✅ | ✅ | **Done** — 2026-07-23 |
| Appointments | ✅ | ✅ | ✅ | **Done** — 2026-07-23 |
| Jobs (daily ops: items/payments/receipt) | ✅ | ✅ | ✅ | **Done** — 2026-07-23 |
| Customers | ✅ | ✅ | ✅ | **Done** — 2026-07-23 |
| Staff | ✅ | ✅ | ✅ | **Done** — 2026-07-23 |
| Orders (+ invoice PDF) | ✅ | ✅ | ✅ | **Done** — 2026-07-24 |
| Testimonials | ✅ | ✅ | ✅ | **Done** — 2026-07-24 |
| Contact Messages | ✅ | ✅ | ✅ | **Done** — 2026-07-24 |
| Reports (×6: revenue, best-sellers, low-stock, appointments, outstanding-balances, staff-commission) | ✅ | ✅ | ❌ (ComingSoon) | Backend done — 2026-07-23. `staff-commission` lives in the shared `staff_or_admin` route group (staff can pull their own); the other 5 are admin-only. React page pending |
| Activity Log | ✅ | ✅ | ❌ (ComingSoon) | Backend done — 2026-07-23. React page pending |
| Users | ✅ | ✅ | ✅ | **Done** — 2026-07-24 |
| My Account (self-service, every role) | ✅ | ✅ | ✅ | **Done** — 2026-07-24. Added sidebar entry + page — wasn't in the nav at all before |

**Step 1 (People & Access) and Step 2 (Commerce Operations — Orders, Testimonials, Contact Messages) complete as of 2026-07-24.** Remaining: Step 3 (Reports ×6, Activity Log) — the last two modules.

**All 17 Blade admin controllers are now ported to `Api/Admin/*` JSON controllers with routes wired.** What's left is entirely on the React side: 8 pages (Customers, Staff, Orders, Testimonials, Contact Messages, Reports, Activity Log, Users) plus My Account (new nav entry).

## Cutover housekeeping (do once every module above is done)

- [x] `barryvdh/laravel-dompdf` — confirmed staying server-side; Jobs receipt (`Api/Admin/JobController::receiptPreview/receiptDownload`) already streams/downloads PDFs this way. Orders invoice still needs the same treatment once that module is ported.
- [ ] Delete the 17 Blade `Admin/*` controllers and 44 Blade views under `resources/views/admin/*`
- [ ] Empty out `routes/web.php` down to a health check
- [ ] Remove `staff_or_admin`/`admin` session-middleware usage from `routes/web.php` (moot once web.php is emptied)

## Suggested build order (remaining — React pages only, backend is 100% done)

Staff → Customers → Orders → Testimonials → Contact Messages → Reports →
Activity Log → Users → My Account

---

*How to use this file:* pick the next unchecked module, build controller +
route + page together, flip its row to Done in the same commit, and note the
date. Don't let a module sit half-checked across commits.
