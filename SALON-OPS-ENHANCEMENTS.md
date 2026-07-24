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
- [ ] **(Tier 1 — quick win)** No visit-history rollup on a customer's profile (total spend, last visit, lifetime jobs) beyond drilling into their job list manually. `Customer::totalSpent()` and `Customer::visitCount()` already exist on the model unused — just needs a `lastVisit()` addition and wiring into the customer detail API + admin UI. No migration needed.

### Jobs (walk-in/POS)

- [ ] **(Tier 2)** Discounts are per-item only — no job-level discount. New `discount_type`/`discount_value` columns on `jobs_salon`, folded into `SalonJob::recalculateTotals()`. Small, contained.
- [ ] **(Tier 4)** No **service package/bundle pricing** (e.g., a fixed-price bridal package covering hair+makeup+nails instead of three separately-discounted line items). Split out from the job-level-discount item above — this one needs a new pricing entity (bundles of services at a fixed price) plugged into the Jobs POS flow, discounting, and per-line commission attribution. The bigger of the two "discounts" gaps.
- [ ] **(Tier 1 — quick win)** No tip field for staff. One column on `job_payments`, included in `recalculateTotals()`, add the input to the Jobs POS UI.
- [ ] **(Tier 4)** No inventory decrement — a hair-color service consuming stock doesn't touch `products.stock_count`, so your low-stock report can't actually reflect real usage from services, only shop sales. Needs a new service→product consumption mapping table, then a hook into `JobItem`'s save lifecycle. Also affects low-stock report accuracy once live.
- [ ] **(Tier 4)** No support for a second staff member assisting on one item (common for bridal jobs). `job_items.staff_id` is a single FK feeding directly into commission math and the `staffCommission` report — this needs a pivot table and a rethink of how commission splits, not just a UI add.

### Payments

- [ ] `job_payments.method` only covers cash/card/bank_transfer — no online deposit option. For wedding/bridal bookings specifically, a small non-refundable online deposit at booking time (rather than trusting a walk-in commitment) is a very common salon pattern and would plug directly into the existing `PaymentGatewayStub`.

### Inventory

- [ ] **(Tier 3)** Stock is a single counter, no movement ledger (no record of *why* stock changed — sold, used in a service, damaged, restocked). New `stock_movements` table; refactor `Product::decrementStock()` and any other stock-writing path to log a reason instead of just mutating the counter.
- [ ] **(Tier 1 — quick win)** No reorder-point-per-product. One nullable column on `products`; tweak `ReportController::lowStock()` to compare against it instead of the hardcoded `<= 10`.
- [ ] **(Tier 4)** No supplier/purchase-order tracking. Biggest item on the whole list — a full new CRUD subsystem (suppliers, POs, PO line items) with its own admin pages.

## Admin/reporting enhancements

- [ ] **(Tier 2)** Reports cover revenue, best-sellers, low-stock, appointments, outstanding balances, staff commission — solid coverage. Missing three, all straightforward extensions of the existing `ReportController` date-range pattern: **repeat-customer/retention rate**, busiest-hours heatmap (useful for staffing — note appointments with unparseable free-text `time` need excluding, same as the calendar view already does), and month-over-month comparison (growth %) rather than just an absolute range.
- [ ] **(Tier 1 — quick win)** No CSV/Excel export on any report — only the invoice/receipt PDFs export. Your accountant will want CSV, not just PDF. Every report already returns clean structured data; just a stream-download endpoint reusing the same queries.
- [ ] **(Tier 1 — quick win)** No dashboard alerting (e.g., "3 jobs have an outstanding balance over 30 days old" surfaced proactively rather than requiring someone to open the report). No new data needed — just surfaces the existing `outstandingBalances` and `lowStock` queries as a Dashboard widget.

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
     - [ ] Customer visit-history rollup (model helpers already exist, just needs wiring)
     - [ ] CSV/Excel export on reports (reuses existing report queries)
     - [ ] Inventory reorder-point per product (one column + a report tweak)
     - [ ] Dashboard alerting (reuses existing outstanding-balance/low-stock queries)
     - [ ] Jobs: tip field (one column + totals + UI)
   - **Tier 2 — small, contained (new column or query, no new domain):**
     - [ ] Reporting: retention rate
     - [ ] Reporting: busiest-hours heatmap
     - [ ] Reporting: month-over-month comparison
     - [ ] Jobs: job-level discount
   - **Tier 3 — moderate (new table, but isolated):**
     - [ ] Inventory movement ledger
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
