# Salon Operations — Feature Gaps & Enhancements

Living checklist, same rules as `ADMIN-MIGRATION-TASKS.md`: check the box,
add the date/commit note in the same commit as the work, don't leave things
half-done across commits. Identified 2026-07-24 via a full pass over the
schema, controllers, and mail setup — what a real single-salon operation is
still missing, before moving on to Phase 3 (multi-tenancy).

## Salon-operations feature gaps

### Appointments — the biggest one

Overlap-prevention core done 2026-07-24 (backend + admin UI): `appointments.time` was a free-text string with no staff assignment and no duration-based conflict checking, so nothing stopped two customers being booked for the same staff member at the same time. Fixed via a proper booking engine — `staff_id` + computed end-time from service duration + an overlap check on staff assignment. Calendar/day-grid view and waitlist both done as of 2026-07-24 — **all of Ops-1 is now complete.**

- [x] Add `staff_id` (nullable FK) to `appointments`, `staff()` relation on the model — 2026-07-24
- [x] `duration_minutes` (nullable int) added to `services`, alongside the existing free-text `duration` (kept for the public site); seeded with a best-effort single-sitting estimate per existing service; editable in the admin Services page — 2026-07-24
- [x] Overlap-check on staff assignment: new `App\Services\AppointmentScheduler::findConflict()`, wired into a new `PATCH /admin/appointments/{id}/staff` endpoint (`Api/Admin/AppointmentController::assignStaff`) that returns a 422 with a human-readable conflict message instead of silently double-booking; only `pending`/`confirmed` appointments hold the slot — 2026-07-24
- [x] `no_show` status added (`pending/confirmed/completed/cancelled/no_show`), via raw `ALTER ... MODIFY` migration (no `doctrine/dbal` dependency needed) — 2026-07-24
- [x] Admin Appointments page: staff-assignment dropdown per row (shows the conflict error inline if blocked), `no_show` selectable in the status dropdown, service duration shown next to date/time — 2026-07-24
- [x] Calendar/day-grid view in the admin Appointments page (who's free at X, not just a paginated list) — 2026-07-24. New "Calendar" tab alongside the existing list view: `GET /admin/appointments/calendar?date=` (unpaginated, one day) renders as a per-staff column grid with time-positioned blocks sized by service duration; an "Unassigned" column catches appointments with no staff yet; clicking a block opens the same staff/status/delete controls as the list view. Appointments with an unparseable free-text `time` value are listed separately below the grid rather than silently dropped.
- [ ] No reminders yet — tracked separately as its own item below (SMS/WhatsApp)
- [x] Waitlist for fully-booked slots — done 2026-07-24. `is_waitlisted` boolean added to `appointments` (migration `2026_07_24_000006`). At public booking time (`Api\AppointmentController::store`), `AppointmentScheduler::isFullyBooked()` checks whether every active staff member qualified for the requested service (or every active staff member, if none are marked qualified yet) already has an overlapping pending/confirmed appointment at that date/time — staff on explicit full-day leave (`staff_shifts`, type `leave`) are excluded from the "available" pool, but an unscheduled staff member still counts as available. If nobody's free, the appointment still saves (not rejected outright) but is flagged `is_waitlisted`, and the customer gets a distinct confirmation message about being on the waitlist instead of the normal "we'll call to confirm" one. Cleared automatically the moment an admin successfully assigns any staff member via `assignStaff()` — no separate "promote from waitlist" action needed. Admin UI: a "Waitlist (N)" toggle button next to the List/Calendar switcher (only shown when N > 0) filters the list to just those; a small amber "Waitlisted" tag shows on the row/calendar block/detail panel wherever the appointment appears.

### Staff

Done 2026-07-24 (backend + admin UI), all three items from the original gap analysis:

- [x] **Working-hours/shift/leave table.** New `staff_shifts` table — one row per staff member per date, either a `work` entry (with start/end time) or a `leave` entry (whole day). Deliberately one table for both rather than two, so "who's on today" is a single query, not a join across differently-shaped tables. `Api\Admin\StaffShiftController` for CRUD (`/admin/staff-shifts*`), plus a dedicated `GET /admin/staff/roster?date=` rollup — every active staff member annotated `work`/`leave`/`unscheduled` for that date (a staff member with no entry at all is reported `unscheduled`, not assumed either way). Admin UI: `RosterWidget.tsx` at the top of the Staff page (date-switchable, defaults to today), and a "Schedule" tab in the new expandable per-staff `StaffDetailPanel.tsx` for adding/removing entries.
- [x] **Staff↔service qualification mapping.** New `service_staff` pivot table (plain many-to-many, no extra columns yet — per-staff price override or skill level would be a further enhancement, out of scope here). `Staff::services()` / `Service::staff()` relations. `StaffController::services()` returns every service flagged `qualified` for a given staff member (one call, not two lists to cross-reference client-side); `syncServices()` saves the checkbox state. Admin UI: "Qualified Services" tab in `StaffDetailPanel.tsx`. **Also wired into the actual booking flow** (the point of this item) — `AppointmentController`'s `staffList` (both `index()` and `calendar()`) now carries each staff member's `service_ids`, and the staff-assignment dropdown in both `Appointments.tsx` and `AppointmentsCalendar.tsx` sorts qualified staff first and labels unqualified ones "(not marked qualified)" rather than hiding them outright — hiding would make assignment impossible for any service that hasn't had its qualified staff configured yet, which won't be all of them on day one.
- [x] **Performance visibility beyond commission $.** `StaffController::performance()` (`GET /admin/staff/{staff}/performance`) — bookings-completed count and no-show rate from `appointments` (staff-assigned, date-ranged), average ticket size and services-performed count from the staff member's own `job_items` (not the whole job total, since a job can have multiple staff on different line items). Admin UI: "Performance" tab in `StaffDetailPanel.tsx`, 5 stat tiles over a 90-day default window.

### Customers

- [x] Search by name/phone/email already works server-side (good — that's already there).
- [ ] **(Tier 3)** No loyalty/points, no customer tags (VIP, bridal, allergy notes as structured data rather than a free-text `notes` field), no birthday/anniversary reminders — all common salon retention levers. Bundled sub-work, roughly in order: tags (quick pivot/enum) → points (small ledger) → birthday/anniversary reminders (needs a scheduled command + Mailable, the biggest sub-item here).
- [x] **(Tier 1)** Visit-history rollup on a customer's profile (total spend, last visit, lifetime jobs) — done 2026-07-25. `Customer::lastVisit()` added alongside the existing `totalSpent()`/`visitCount()`; wired into `CustomerController::show()` and the admin Customers page (shows "N visits · total spent · last visit" in the expanded row).

### Jobs (walk-in/POS)

- [x] **(Tier 2)** Discounts are per-item only — no job-level discount. New `discount_type`/`discount_value` columns on `jobs_salon`, folded into `SalonJob::recalculateTotals()`. Small, contained. — done 2026-07-25. Job-level discount applies on top of the item subtotal (independent of per-item discounts already baked into each item's `final_price`); appended `discount_amount`/`total_after_discount` accessors on the model so every endpoint serializing a job carries them for free. New `PATCH /admin/jobs/{job}/discount` endpoint, admin UI in the Jobs detail panel ("Job Discount" card next to Treatments/Payments), and the PDF receipt now shows a discount line + "Total after discount" when one's applied.
- [ ] **(Tier 4)** No **service package/bundle pricing** (e.g., a fixed-price bridal package covering hair+makeup+nails instead of three separately-discounted line items). Split out from the job-level-discount item above — this one needs a new pricing entity (bundles of services at a fixed price) plugged into the Jobs POS flow, discounting, and per-line commission attribution. The bigger of the two "discounts" gaps.
- [x] **(Tier 1)** Tip field for staff — done 2026-07-25. `job_payments.tip_amount` (per-payment) + cached `jobs_salon.total_tips`, deliberately excluded from `total_paid`/`balance_due` (a tip isn't part of the service price owed). Surfaced in the admin Jobs payment form/list, the totals summary, and the PDF receipt.
- [ ] **(Tier 4)** No inventory decrement — a hair-color service consuming stock doesn't touch `products.stock_count`, so your low-stock report can't actually reflect real usage from services, only shop sales. Needs a new service→product consumption mapping table, then a hook into `JobItem`'s save lifecycle. Also affects low-stock report accuracy once live.
- [ ] **(Tier 4)** No support for a second staff member assisting on one item (common for bridal jobs). `job_items.staff_id` is a single FK feeding directly into commission math and the `staffCommission` report — this needs a pivot table and a rethink of how commission splits, not just a UI add.

### Payments

- [ ] `job_payments.method` only covers cash/card/bank_transfer — no online deposit option. For wedding/bridal bookings specifically, a small non-refundable online deposit at booking time (rather than trusting a walk-in commitment) is a very common salon pattern and would plug directly into the existing `PaymentGatewayStub`.

### Inventory

- [x] **(Tier 3)** Stock movement ledger — done 2026-07-25. New `stock_movements` table records every stock change with a reason: `Product::decrementStock()` (public checkout) now logs a `sale` movement tagged with the order, `ProductController::update()` logs a `correction` movement when `stock_count` is edited directly on the product form, and a new `Product::applyMovement()` behind `GET/POST /admin/products/{product}/stock-movements` lets an admin log a deliberate `restock` or `adjustment` with an optional reason. Note: doesn't yet cover stock consumed by a service (that's the separate Tier 4 "inventory decrement on service use" item — this ledger is ready to log it once that hook exists).
- [x] **(Tier 1)** Reorder-point-per-product — done 2026-07-25. Nullable `reorder_point` column on `products`; `Product::scopeLowStock()` is the single source of truth (per-product override, falls back to the old hardcoded `<=10`), used by both the Low Stock report and the new dashboard alert. Editable in the admin Products form; low-stock products get an amber indicator in the product grid.
- [ ] **(Tier 4)** No supplier/purchase-order tracking. Biggest item on the whole list — a full new CRUD subsystem (suppliers, POs, PO line items) with its own admin pages.

## Admin/reporting enhancements

- [x] **(Tier 2)** Reports cover revenue, best-sellers, low-stock, appointments, outstanding balances, staff commission — solid coverage. Missing three, all straightforward extensions of the existing `ReportController` date-range pattern: **repeat-customer/retention rate**, busiest-hours heatmap (useful for staffing — note appointments with unparseable free-text `time` need excluding, same as the calendar view already does), and month-over-month comparison (growth %) rather than just an absolute range. — done 2026-07-25. `revenue()` now compares against the immediately-preceding period of equal length (`growthPercent`, null rather than 0% with nothing to compare against); new `busiestHours()` buckets appointments by hour reusing the exact same free-text time parser as `AppointmentsCalendar.tsx` (unparseable times excluded and counted, not dropped silently); new `retentionRate()` splits customers with a visit in range into new vs. returning. Two new Reports tabs (Busiest Hours, Retention) plus the Revenue panel's new growth card.
- [x] **(Tier 1)** CSV/Excel export on reports — done 2026-07-25. Client-side only (`admin/src/lib/csv.ts`), no backend endpoint needed — every report already returns clean structured data, just serialized and downloaded from what's already loaded. Wired into all six report panels.
- [x] **(Tier 1)** Dashboard alerting — done 2026-07-25. `DashboardController::index()` returns an `alerts` array (overdue 30+ day outstanding balances, low-stock products via the same `Product::scopeLowStock()`), rendered as banner cards at the top of the Dashboard, linking through to Reports.

## Backend technical health

These aren't salon-specific but they're real operational risk, roughly in order of how much I'd worry about them:

- [ ] **All email is sent synchronously (no queue).** `QUEUE_CONNECTION=sync` and none of the 6 `Mail` classes implement `ShouldQueue`. Every appointment confirmation, order notification, contact form submission etc. blocks the HTTP request until the mail server responds. If your SMTP provider ever hiccups, customer-facing requests hang or time out. This is a quick, high-value fix — a real queue worker (`database` or `redis` driver) plus `ShouldQueue` on the mail classes.
- [ ] **No soft deletes anywhere.** Deleting a customer, staff member, or product is permanent — no recovery, and no audit trail of "this customer record used to exist." For financial/business data this is usually worth the small schema cost.
- [ ] **No automated tests.** There's no `tests/` directory at all. The financial calculations — discounts, commission percentages, balance-due math on jobs — are exactly the kind of logic where a silent regression costs real money and goes unnoticed until a report looks wrong weeks later.
- [ ] **No 2FA on admin login.** You're handling customer PII, payment records, and staff commission data behind a single password.
- [ ] Local filesystem for uploaded images (gallery, products, albums) — fine for one server, but no CDN/S3 means slower page loads and a single point of failure for media if the EC2 instance has issues.

## Priority order

For actual salon-operations impact (not the SaaS pivot):

1. [x] **Appointment double-booking prevention + staff assignment** — this is a live operational risk right now, not a nice-to-have. **Done 2026-07-24** — staff assignment, overlap check, no-show status, calendar/day-grid view, and waitlist for fully-booked slots. See Appointments section above.
2. [x] **Staff shift/schedule table** — unblocks both the booking fix above and better staff reporting. **Done 2026-07-24** along with the other two Staff items (qualification mapping, performance stats) — see Staff section above.
3. [ ] **Customers / Jobs / Inventory / Reporting gaps** — re-sorted 2026-07-24 by actual implementation effort (checked against the current schema/models/controllers), not by category. Work top-down within each tier; flip boxes in the sections above as items land.
   - **Tier 1 — quick wins, no new schema or trivial one-column additions:**
     - [x] Customer visit-history rollup (model helpers already exist, just needs wiring) — 2026-07-25
     - [x] CSV/Excel export on reports (reuses existing report queries) — 2026-07-25. Client-side only (`lib/csv.ts`), no backend endpoint — every report already returns clean structured data.
     - [x] Inventory reorder-point per product (one column + a report tweak) — 2026-07-25. `Product::scopeLowStock()` is now the single source of truth for both the Low Stock report and the dashboard alert below.
     - [x] Dashboard alerting (reuses existing outstanding-balance/low-stock queries) — 2026-07-25
     - [x] Jobs: tip field — 2026-07-25. Tracked per-payment (`job_payments.tip_amount`) and cached as `jobs_salon.total_tips`, deliberately kept separate from `total_paid`/`balance_due` so it doesn't distort what a customer still owes for services.
   - **Tier 2 — small, contained (new column or query, no new domain):**
     - [x] Reporting: retention rate — 2026-07-25
     - [x] Reporting: busiest-hours heatmap — 2026-07-25
     - [x] Reporting: month-over-month comparison — 2026-07-25
     - [x] Jobs: job-level discount — 2026-07-25
   - **Tier 3 — moderate (new table, but isolated):**
     - [x] Inventory movement ledger — 2026-07-25. New `stock_movements` table (`product_id`, `type` — `sale`/`restock`/`adjustment`/`correction`, signed `quantity_change`, `balance_after`, `reason`, loose `reference_type`/`reference_id`, `created_by`). Logged automatically from the three places stock already changes — `Product::decrementStock()` (public checkout, type `sale`, tagged with the order) and `ProductController::update()` (raw stock_count edit on the product form, type `correction`) — plus a new deliberate-entry path: `Product::applyMovement()` behind `GET/POST /admin/products/{product}/stock-movements` for admin-triggered restocks and adjustments (adjustment can go either direction — restock write-offs use adjustment with a negative quantity, restock itself is validated positive-only so the type alone tells you the direction). Admin UI: a ledger toggle (clipboard icon) on each Products card expands a `StockLedgerPanel` — paginated history plus a restock/adjustment form with an optional reason.
     - [ ] Customers: tags → points → birthday/anniversary reminders (in that sub-order)
   - **Tier 4 — bigger lifts (touches core pricing/commission logic, or a new subsystem):**
     - [ ] Jobs: inventory decrement on service use
     - [ ] Jobs: second staff member on one item
     - [ ] Jobs: service package/bundle pricing
     - [ ] Inventory: supplier/purchase-order tracking
4. [ ] **Queue the email sending** — quick fix, removes a real reliability risk. Deferred behind item 3 above per 2026-07-24 direction, but still cheap whenever picked up.
5. [ ] **SMS/WhatsApp reminders** — directly reduces no-shows, which is real revenue. **Explicitly deferred 2026-07-24** — do after everything in item 3.
6. [ ] Remaining backend technical health items (soft deletes, automated tests, 2FA, S3/CDN for uploads) — valuable, lower urgency, not yet scheduled.

---

*How to use this file:* pick the next unchecked item, do the work, flip its
box to checked in the same commit, and note the date. Don't let an item sit
half-checked across commits.
