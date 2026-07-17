<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    // Placeholder for the future payment gateway credentials (e.g. PayHere,
    // Stripe). Not wired up yet — card payments are simulated as successful
    // in App\Services\PaymentGatewayStub until this is configured.
    'payment_gateway' => [
        'key' => env('PAYMENT_GATEWAY_KEY'),
        'secret' => env('PAYMENT_GATEWAY_SECRET'),
    ],

];
