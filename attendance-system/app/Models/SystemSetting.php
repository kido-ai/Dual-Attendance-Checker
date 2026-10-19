<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SystemSetting extends Model
{
    private const DEFAULTS = [
        'system_name' => 'Dual Auth Attendance',
        'school_name' => 'St. Jude College of Science and Technology',
        'school_logo' => null,
        'qr_expiration_days' => '365',
        'face_match_threshold' => '0.55',
    ];

    protected $fillable = ['key', 'value'];

    /*
    |--------------------------------------------------------------------------
    | Static helpers -- use these everywhere, never query directly
    |--------------------------------------------------------------------------
    |
    | SystemSetting::get('school_name')          --> 'Your School Name'
    | SystemSetting::get('face_match_threshold') --> '0.55'
    | SystemSetting::set('school_name', 'NU Manila')
    | SystemSetting::allKeyed()                  --> ['key' => 'value', ...]
    */

    /**
     * Get a single setting value by key. Results are cached for one hour
     * so every page load doesn't hit the database for system config.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::remember("setting:{$key}", 3600, function () use ($key, $default) {
            $row = static::where('key', $key)->first();
            return $row ? $row->value : $default;
        });
    }

    /**
     * Set a single setting. Upserts so callers don't care whether the
     * key already exists. Clears the relevant cache slot immediately.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting:{$key}");
        Cache::forget('settings:all');
    }

    /**
     * Return all settings as a flat ['key' => 'value'] array.
     * Used by the Settings controller to pre-fill the form.
     */
    public static function allKeyed(): array
    {
        return Cache::remember('settings:all', 3600, function () {
            $settings = array_replace(
                self::DEFAULTS,
                static::query()->pluck('value', 'key')->toArray(),
            );

            if (blank($settings['school_name']) || $settings['school_name'] === 'Your School Name') {
                $settings['school_name'] = self::DEFAULTS['school_name'];
            }

            return $settings;
        });
    }

    /**
     * Delete the current school logo from storage and clear its setting.
     */
    public static function removeLogo(): void
    {
        $path = static::get('school_logo');
        if ($path) {
            Storage::disk('public')->delete($path);
        }
        static::set('school_logo', null);
    }

    public static function resetToDefaults(): void
    {
        $logoPath = static::get('school_logo');

        if ($logoPath) {
            Storage::disk('public')->delete($logoPath);
        }

        foreach (self::DEFAULTS as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
            Cache::forget("setting:{$key}");
        }

        Cache::forget('settings:all');
    }
}
