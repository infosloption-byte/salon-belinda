<?php

namespace App\Support;

class InvoiceBranding
{
    public static function logo(): ?string
    {
        $path = public_path('images/invoice-logo.png');

        if (! file_exists($path)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode(file_get_contents($path));
    }
}
