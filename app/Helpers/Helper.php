<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class Helper
{
    public static function generateUniqueOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
    }

    public static function generateTicketCode(
        string $prefix = 'TKT-',
        int $length = 8
    ): string {
        return $prefix . strtoupper(Str::random($length));
    }

    public static function generateQrCode(int $length = 16): string
    {
        return strtoupper(Str::random($length));
    }
}
