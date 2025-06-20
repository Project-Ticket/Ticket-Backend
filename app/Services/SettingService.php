<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingService
{
    /**
     * Get setting value by key
     */
    public static function get($key, $default = null)
    {
        $cacheKey = "setting.{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = DB::table('settings')->where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set setting value
     */
    public static function set($key, $value)
    {
        $setting = DB::table('settings')->where('key', $key)->first();

        if ($setting) {
            DB::table('settings')
                ->where('key', $key)
                ->update([
                    'value' => is_array($value) ? json_encode($value) : $value,
                    'updated_at' => now()
                ]);
        } else {
            DB::table('settings')->insert([
                'key' => $key,
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => self::detectType($value),
                'group' => 'custom',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Clear cache
        Cache::forget("setting.{$key}");

        return true;
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup($group)
    {
        $cacheKey = "settings.group.{$group}";

        return Cache::remember($cacheKey, 3600, function () use ($group) {
            $settings = DB::table('settings')->where('group', $group)->get();

            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = self::castValue($setting->value, $setting->type);
            }

            return $result;
        });
    }

    /**
     * Get all public settings
     */
    public static function getPublicSettings()
    {
        $cacheKey = "settings.public";

        return Cache::remember($cacheKey, 3600, function () {
            $settings = DB::table('settings')->where('is_public', true)->get();

            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = self::castValue($setting->value, $setting->type);
            }

            return $result;
        });
    }

    /**
     * Cast value to appropriate type
     */
    private static function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Detect value type
     */
    private static function detectType($value)
    {
        if (is_bool($value)) return 'boolean';
        if (is_int($value)) return 'integer';
        if (is_array($value)) return 'json';
        return 'string';
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache()
    {
        $keys = DB::table('settings')->pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }

        $groups = DB::table('settings')->distinct()->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("settings.group.{$group}");
        }

        Cache::forget("settings.public");
    }
}
