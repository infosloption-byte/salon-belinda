# Admin Migration & Salon-Ops Task Tracker

Living checklist. Started as the tracker for SAAS-ROADMAP.md Phase 2
("Separate Laravel into a pure API + scaffold the React admin") — that
phase is done (see below). It now also tracks the salon-operations
feature/enhancement backlog identified after Phase 2, which we're working
through before starting Phase 3 (multi-tenancy). Update this file in the
same commit as any item you finish — check the box, add the date/commit
note. This is the single source of truth for "what's done vs what's
left"; SAAS-ROADMAP.md only tracks phase-level progress, not this level
of detail.

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
| Reports (×6: revenue, best-sellers, low-stock, appointments, outstanding-balances, staff-commission) | ✅ | ✅ | ✅ | **Done** — 2026-07-23 (React page 2026-07-24). `staff-commission` lives in the shared `staff_or_admin` route group (staff see only their own row/detail); the other 5 are admin-only and hidden client-side for staff logins. |
| Activity Log | ✅ | ✅ | ✅ | **Done** — 2026-07-23 (React page 2026-07-24). Admin-only; page shows a plain message for non-admin logins as a client-side guard alongside the 403 the API already returns. |
| Users | ✅ | ✅ | ✅ | **Done** — 2026-07-24 |
| My Account (self-service, every role) | ✅ | ✅ | ✅ | **Done** — 2026-07-24. Added sidebar entry + page — wasn't in the nav at all before |

**Step 1 (People & Access), Step 2 (Commerce Operations), and Step 3 (Reports ×6, Activity Log) are all complete as of 2026-07-24.** Every module in the table above is now fully ported — API controller, route, and React page.

**All 17 Blade admin controllers are now ported to `Api/Admin/*` JSON controllers with routes wired, and every React page has replaced its `ComingSoon` placeholder.** Next up is the cutover housekeeping below (deleting the old Blade admin controllers/views and trimming `routes/web.php`).

## Cutover housekeeping (do once every module above is done)

- [x] `barryvdh/laravel-dompdf` — confirmed staying server-side; Jobs receipt (`Api/Admin/JobController::receiptPreview/receiptDownload`) and Orders invoice (`Api/Admin/OrderController::invoicePreview/invoiceDownload`) both stream/download via `Pdf::loadView()`.
- [x] Delete the 17 Blade `Admin/*` controllers and 44 Blade views under `resources/views/admin/*` — done 2026-07-23. Kept only `resources/views/admin/orders/invoice.blade.php` and `resources/views/admin/jobs/receipt.blade.php`, since the new API controllers still render those two through `Pdf::loadView()`.
- [x] Empty out `routes/web.php` down to a health check — done 2026-07-23. Now just `/` and `/up` JSON health endpoints; all admin traffic goes through `/api/admin/*`.
- [x] Remove `staff_or_admin`/`admin` session-middleware usage from `routes/web.php` — done 2026-07-23 (moot now that web.php is emptied). The middleware aliases themselves stay registered in `bootstrap/app.php` since `routes/api.php` still uses them for the token-auth admin API.

**Phase 2 is fully complete as of 2026-07-23: all 17 modules ported, old Blade admin controllers/views deleted, `web.php` cut over to a health check.**

## Salon Operations — feature gaps & enhancements (next, before Phase 3)

Identified 2026-07-24 via a full pass over the schema, controllers, and mail
setup (not just this doc) — what a real single-salon operation is still
missing, before we move on to Phase 3 multi-tenancy. Same rule as above:
check the box, note the date/commit, don't leave things half-done across
commits. Ordered by how much operational risk / value each carries, not
by ease of implementation.

### Ops-1 — Appointment double-booking prevention (live risk, do first)

`appointments.time` is currently free-text with no staff assignment and no
duration, so nothing stops two customers being booked against the same
staff member at the same time.

- [ ] Add `staff_id` (nullable FK) to `appointments`
- [ ] Convert `services.duration` from a free-text string to a real minutes column (or add one alongside it) so an end-time can be computed
- [ ] Add an overlap-check (staff_id + date + computed time range) on create/update, both in the public booking flow and the admin Appointments page
- [ ] Add a `no_show` status, distinct from `cancelled`, so no-show rate can actually be reported on
- [ ] Add a calendar/day-grid view to the admin Appointments page (who's free at X, not just a paginated list)

### Ops-2 — Queue outgoing email (reliability, quick win)

All 6 `Mail` classes send synchronously (`QUEUE_CONNECTION=sync`) — every
appointment confirmation, order notification, and contact-form submission
blocks the HTTP request on the mail server.

- [ ] Add `ShouldQueue` to `AppointmentConfirmed`, `AppointmentCancelled`, `NewAppointmentNotification`, `NewContactMessageNotification`, `NewOrderNotification`, `OrderReceipt`
- [ ] Switch `QUEUE_CONNECTION` from `sync` to `database` (or `redis` if available) and run a queue worker in `docker-compose.yml`

### Ops-3 — SMS/WhatsApp appointment reminders (retention, revenue)

No SMS/WhatsApp integration exists anywhere in the codebase — email only.

- [ ] Pick a provider (Twilio, or a local Sri Lankan SMS gateway) and add the service class
- [ ] Scheduled command: reminder N hours before each confirmed appointment
- [ ] Surface delivery status/failures somewhere in the admin (even just Activity Log)

### Ops-4 — Staff shift/schedule table (unblocks Ops-1's overlap check properly)

No working-hours/shift/leave model exists — "who's on today" only exists
in someone's head.

- [ ] `staff_shifts` table (staff_id, date/day-of-week, start_time, end_time)
- [ ] Leave/time-off records
- [ ] Feed shift data into the Ops-1 overlap check so bookings can't land outside a staff member's working hours either

### Ops-5 — Everything else (real value, lower urgency)

- [ ] Job-level discounts / service package bundling (e.g. a fixed-price bridal package instead of per-item discounts)
- [ ] Tip field on job items/payments
- [ ] Inventory movement ledger (why stock changed — sale, service use, damage, restock) instead of a single counter; service items should decrement stock too
- [ ] Reorder point + supplier/purchase-order tracking per product
- [ ] Online deposit payments for bookings (plug into the existing `PaymentGatewayStub`)
- [ ] Waitlist for fully-booked slots
- [ ] Staff↔service qualification mapping (which staff can perform which services)
- [ ] Staff performance metrics beyond commission $ (bookings completed, average ticket size, no-show rate)
- [ ] Customer visit-history rollup on profile (total spend, last visit, lifetime jobs) instead of manually drilling into jobs
- [ ] Loyalty/points + customer tags (VIP, bridal, allergy notes as structured data, not a free-text `notes` field) + birthday/anniversary reminders
- [ ] CSV/Excel export on all 6 reports (currently PDF-only, via invoice/receipt)
- [ ] New report angles: repeat-customer/retention rate, busiest-hours heatmap, month-over-month comparison
- [ ] Proactive dashboard alerts (e.g. outstanding balances aged over 30 days) instead of requiring someone to open the report
- [ ] Soft deletes on `customers`, `staff`, `products` (currently hard deletes, no recovery/audit trail)
- [ ] Automated test suite — especially the financial math (discounts, commission %, job balance-due) where a silent regression costs real money
- [ ] 2FA on admin login
- [ ] CDN/S3 for uploaded media (gallery/products/albums) instead of local disk

---

*How to use this file:* pick the next unchecked module, build controller +
route + page together, flip its row to Done in the same commit, and note the
date. Don't let a module sit half-checked across commits.
