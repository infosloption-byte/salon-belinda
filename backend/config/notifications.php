<?php

return [
    // Where "new appointment / new order / new message" alerts go. Defaults
    // to the same inbox mail is sent *from*, but can be pointed elsewhere
    // (e.g. the owner's personal email) without touching MAIL_FROM_ADDRESS.
    'salon_email' => env('SALON_NOTIFY_EMAIL', env('MAIL_FROM_ADDRESS', 'info@yoursalon.com')),

    // Displayed on invoices/receipts/emails. Single-tenant stop-gap — once
    // multi-tenant, these come from the tenant's own settings instead
    // (see SAAS-ROADMAP.md Phase 4).
    'salon_phone' => env('SALON_PHONE', '000 000 0000'),
    'salon_address' => env('SALON_ADDRESS', '123 Main Street, Your City'),
];
