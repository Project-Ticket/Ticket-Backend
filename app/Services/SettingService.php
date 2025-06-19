<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Get setting value by key
     */
    public static function get($key, $default = null)
    {
        $cacheKey = "setting.{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();

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
        $setting = Setting::where('key', $key)->first();

        if ($setting) {
            $setting->value = is_array($value) ? json_encode($value) : $value;
            $setting->updated_at = now();
            $setting->save();
        } else {
            Setting::create([
                'key'        => $key,
                'value'      => is_array($value) ? json_encode($value) : $value,
                'type'       => self::detectType($value),
                'group'      => 'custom',
                'is_public'  => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

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
            $settings = Setting::where('group', $group)->get();

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
            $settings = Setting::where('is_public', true)->get();

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
        $keys = Setting::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }

        $groups = Setting::distinct()->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("settings.group.{$group}");
        }

        Cache::forget("settings.public");
    }
}
