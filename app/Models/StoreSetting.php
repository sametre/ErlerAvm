<?php

namespace App\Models;

use App\Concerns\Commerce\HasPublicId;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Throwable;

class StoreSetting extends Model
{
    use HasPublicId;

    protected $fillable = [
        'public_id',
        'group',
        'key',
        'value',
        'is_public',
        'updated_by_id',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'is_public' => 'boolean',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'store_name' => config('app.name', 'ErlerAVM'),
            'logo_url' => '/erleravm.png',
            'favicon_url' => '/favicon.png',
            'primary_color' => '#ed1c24',
            'accent_color' => '#111827',
            'contact_phone' => '(0224) 334 16 30',
            'contact_email' => '',
            'address' => 'Runguşpaşa, Bursa Cd. No:87, 16700 Karacabey/Bursa',
            'facebook_url' => '',
            'instagram_url' => '',
            'twitter_url' => '',
            'youtube_url' => '',
            'homepage_sections' => [
                'hero_slider',
                'featured_categories',
                'campaigns',
                'best_sellers',
                'blog',
            ],
            'maintenance_mode_active' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function publicPayload(): array
    {
        try {
            if (! Schema::hasTable('store_settings') || ! Schema::hasColumn('store_settings', 'key')) {
                return self::defaults();
            }

            /** @var Collection<int, StoreSetting> $settings */
            $settings = self::query()
                ->where('is_public', true)
                ->get(['key', 'value']);

            return [
                ...self::defaults(),
                ...$settings->mapWithKeys(fn (StoreSetting $setting) => [
                    $setting->key => data_get($setting->value, 'value'),
                ])->all(),
            ];
        } catch (Throwable) {
            return self::defaults();
        }
    }

    public static function setValue(
        string $key,
        mixed $value,
        string $group = 'general',
        bool $isPublic = true,
        ?int $userId = null,
    ): self {
        return self::query()->updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => ['value' => $value],
                'is_public' => $isPublic,
                'updated_by_id' => $userId,
            ],
        );
    }
}
