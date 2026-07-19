<?php

return [
    // Where "new appointment / new order / new message" alerts go. Defaults
    // to the same inbox mail is sent *from*, but can be pointed elsewhere
    // (e.g. Shani's personal email) without touching MAIL_FROM_ADDRESS.
    'salon_email' => env('SALON_NOTIFY_EMAIL', env('MAIL_FROM_ADDRESS', 'info.salonbelinda@gmail.com')),
];
