<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WilayahHelpersDropdown
{
    protected static $baseUrl = 'https://www.emsifa.com/api-wilayah-indonesia/api/';

    public static function fetch($type, $parentId = null)
    {
        $url = match ($type) {
            'province' => self::$baseUrl . 'provinces.json',
            'regency'  => self::$baseUrl . "regencies/{$parentId}.json",
            'district' => self::$baseUrl . "districts/{$parentId}.json",
            'village'  => self::$baseUrl . "villages/{$parentId}.json",
            default    => null,
        };

        if (!$url) return [];

        try {
            $response = Http::timeout(10)->get($url);
            return $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            Log::error('WilayahDropdown Error: ' . $e->getMessage());
            return [];
        }
    }
}
