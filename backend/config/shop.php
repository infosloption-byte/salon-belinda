<?php

return [
    // Kept in sync with shop/src/data/site.ts `shopSettings`.
    'delivery_fee' => env('SHOP_DELIVERY_FEE', 350),
    'free_delivery_threshold' => env('SHOP_FREE_DELIVERY_THRESHOLD', 8000),
];
