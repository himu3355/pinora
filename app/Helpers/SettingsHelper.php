<?php

use App\Models\Setting;
use Illuminate\Support\Str;

if (!function_exists('app_setting')) {
    function app_setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (!function_exists('app_setting_image')) {
    function app_setting_image(string $key, ?string $default = null): ?string
    {
        $val = Setting::get($key, $default);
        if (empty($val)) {
            return $default;
        }

        if (Str::startsWith($val, ['http://', 'https://'])) {
            return $val;
        }

        return asset('storage/' . ltrim($val, '/'));
    }
}

if (!function_exists('app_setting_bool')) {
    function app_setting_bool(string $key, bool $default = false): bool
    {
        $val = Setting::get($key, $default);
        if (is_bool($val)) {
            return $val;
        }
        return in_array(strtolower((string) $val), ['1', 'true', 'on', 'yes'], true);
    }
}
