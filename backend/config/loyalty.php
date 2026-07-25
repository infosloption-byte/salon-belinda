<?php

return [
    // SALON-OPS-ENHANCEMENTS.md, "Customers" (Tier 3) — points ledger.
    // How many currency units a customer needs to pay (on a job, via
    // JobController::addPayment) to earn a single point. Tip amount is
    // excluded, same reasoning as it's excluded from total_paid elsewhere —
    // a tip isn't salon revenue. floor(amount / this) points are awarded
    // per payment; a payment smaller than this earns 0, which is expected.
    'currency_per_point' => env('LOYALTY_CURRENCY_PER_POINT', 100),
];
