# Salon Operations — Feature Gaps & Enhancements

Living checklist, same rules as `ADMIN-MIGRATION-TASKS.md`: check the box,
add the date/commit note in the same commit as the work, don't leave things
half-done across commits. Identified 2026-07-24 via a full pass over the
schema, controllers, and mail setup — what a real single-salon operation is
still missing, before moving on to Phase 3 (multi-tenancy).

## Salon-operations feature gaps

### Appointments — the biggest one

- [ ] `appointments.time` is a free-text string, not a real slot. There's no staff assignment at booking time and no duration-based conflict checking, so **nothing stops two customers being booked for the same staff member at the same time** until someone manually catches it. This is the single highest-value fix — a proper booking engine (staff_id + computed end-time from service duration + overlap check).
- [ ] No calendar/day view in the admin — appointments render as a list, not a schedule grid. For a salon, staff want to see "who's free at 2pm today," not a paginated table.
- [ ] No **no-show** status — only `pending/confirmed/completed/cancelled`. No-shows get lumped in with cancellations, which corrupts your no-show rate and any future reminder/deposit-enforcement logic.
- [ ] No reminders. Zero SMS/WhatsApp integration anywhere in the codebase — only email (and see the mail issue below). For a walk-in-heavy salon, an SMS reminder 24h out is usually the single biggest lever on no-show rate.
- [ ] No waitlist for fully-booked slots.

### Staff

- [ ] No working-hours/shift/leave table at all. Staff scheduling and "who's on today" only exists in your head, not the system.
- [ ] No staff↔service qualification mapping (which staff can perform which services) — can't filter booking by "who can actually do a keratin treatment."
- [ ] Performance visibility stops at commission $ — no bookings-completed count, average ticket size, or no-show rate per staff.

### Customers

- [x] Search by name/phone/email already works server-side (good — that's already there).
- [ ] No loyalty/points, no customer tags (VIP, bridal, allergy notes as structured data rather than a free-text `notes` field), no birthday/anniversary reminders — all common salon retention levers.
- [ ] No visit-history rollup on a customer's profile (total spend, last visit, lifetime jobs) beyond drilling into their job list manually.

### Jobs (walk-in/POS)

- [ ] Discounts are per-item only — no job-level discount or **service package/bundle pricing** (e.g., a fixed-price bridal package covering hair+makeup+nails instead of three separately-discounted line items).
- [ ] No tip field for staff.
- [ ] No inventory decrement — a hair-color service consuming stock doesn't touch `products.stock_count`, so your low-stock report can't actually reflect real usage from services, only shop sales.
- [ ] No support for a second staff member assisting on one item (common for bridal jobs).

### Payments

- [ ] `job_payments.method` only covers cash/card/bank_transfer — no online deposit option. For wedding/bridal bookings specifically, a small non-refundable online deposit at booking time (rather than trusting a walk-in commitment) is a very common salon pattern and would plug directly into the existing `PaymentGatewayStub`.

### Inventory

- [ ] Stock is a single counter, no movement ledger (no record of *why* stock changed — sold, used in a service, damaged, restocked). Low-stock report exists, but there's no reorder-point-per-product or supplier/purchase-order tracking.

## Admin/reporting enhancements

- [ ] Reports cover revenue, best-sellers, low-stock, appointments, outstanding balances, staff commission — solid coverage. Missing: **repeat-customer/retention rate**, busiest-hours heatmap (useful for staffing), and month-over-month comparison (growth %) rather than just an absolute range.
- [ ] No CSV/Excel export on any report — only the invoice/receipt PDFs export. Your accountant will want CSV, not just PDF.
- [ ] No dashboard alerting (e.g., "3 jobs have an outstanding balance over 30 days old" surfaced proactively rather than requiring someone to open the report).

## Backend technical health

These aren't salon-specific but they're real operational risk, roughly in order of how much I'd worry about them:

- [ ] **All email is sent synchronously (no queue).** `QUEUE_CONNECTION=sync` and none of the 6 `Mail` classes implement `ShouldQueue`. Every appointment confirmation, order notification, contact form submission etc. blocks the HTTP request until the mail server responds. If your SMTP provider ever hiccups, customer-facing requests hang or time out. This is a quick, high-value fix — a real queue worker (`database` or `redis` driver) plus `ShouldQueue` on the mail classes.
- [ ] **No soft deletes anywhere.** Deleting a customer, staff member, or product is permanent — no recovery, and no audit trail of "this customer record used to exist." For financial/business data this is usually worth the small schema cost.
- [ ] **No automated tests.** There's no `tests/` directory at all. The financial calculations — discounts, commission percentages, balance-due math on jobs — are exactly the kind of logic where a silent regression costs real money and goes unnoticed until a report looks wrong weeks later.
- [ ] **No 2FA on admin login.** You're handling customer PII, payment records, and staff commission data behind a single password.
- [ ] Local filesystem for uploaded images (gallery, products, albums) — fine for one server, but no CDN/S3 means slower page loads and a single point of failure for media if the EC2 instance has issues.

## Priority order

For actual salon-operations impact (not the SaaS pivot):

1. [ ] **Appointment double-booking prevention + staff assignment** — this is a live operational risk right now, not a nice-to-have
2. [ ] **Queue the email sending** — quick fix, removes a real reliability risk
3. [ ] **SMS/WhatsApp reminders** — directly reduces no-shows, which is real revenue
4. [ ] **Staff shift/schedule table** — unblocks both the booking fix above and better staff reporting
5. [ ] Everything else (loyalty, inventory ledger, CSV export, 2FA, tests) — valuable, lower urgency

---

*How to use this file:* pick the next unchecked item, do the work, flip its
box to checked in the same commit, and note the date. Don't let an item sit
half-checked across commits.
