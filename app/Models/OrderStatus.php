<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OrderStatus extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'status_group',
        'badge_class',
        'sort_order',
        'is_active',
        'is_default',
        'counts_as_active',
        'counts_as_delivered',
        'counts_as_cancelled',
        'counts_as_return',
        'counts_as_shipped',
        'marks_payment_paid',
        'restores_stock',
        'reduces_stock',
        'sms_key',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'counts_as_active' => 'boolean',
        'counts_as_delivered' => 'boolean',
        'counts_as_cancelled' => 'boolean',
        'counts_as_return' => 'boolean',
        'counts_as_shipped' => 'boolean',
        'marks_payment_paid' => 'boolean',
        'restores_stock' => 'boolean',
        'reduces_stock' => 'boolean',
    ];

    public static function booted(): void
    {
        static::saved(fn () => static::clearStatusCache());
        static::deleted(fn () => static::clearStatusCache());
    }

    public static function defaults(): array
    {
        return [
            ['name' => 'Pending', 'slug' => 'pending', 'status_group' => 'active', 'badge_class' => 'bg-warning text-dark', 'sort_order' => 10, 'is_active' => true, 'is_default' => true, 'counts_as_active' => true, 'counts_as_delivered' => false, 'counts_as_cancelled' => false, 'counts_as_return' => false, 'counts_as_shipped' => false, 'marks_payment_paid' => false, 'restores_stock' => false, 'reduces_stock' => true, 'sms_key' => 'pending'],
            ['name' => 'Incomplete', 'slug' => 'incomplete', 'status_group' => 'active', 'badge_class' => 'bg-warning text-dark', 'sort_order' => 20, 'is_active' => true, 'is_default' => true, 'counts_as_active' => true, 'counts_as_delivered' => false, 'counts_as_cancelled' => false, 'counts_as_return' => false, 'counts_as_shipped' => false, 'marks_payment_paid' => false, 'restores_stock' => false, 'reduces_stock' => true, 'sms_key' => 'incomplete'],
            ['name' => 'On Hold', 'slug' => 'on-hold', 'status_group' => 'active', 'badge_class' => 'bg-secondary', 'sort_order' => 30, 'is_active' => true, 'is_default' => true, 'counts_as_active' => true, 'counts_as_delivered' => false, 'counts_as_cancelled' => false, 'counts_as_return' => false, 'counts_as_shipped' => false, 'marks_payment_paid' => false, 'restores_stock' => false, 'reduces_stock' => true, 'sms_key' => 'on hold'],
            ['name' => 'Scheduled', 'slug' => 'scheduled', 'status_group' => 'active', 'badge_class' => 'bg-info text-white', 'sort_order' => 40, 'is_active' => true, 'is_default' => true, 'counts_as_active' => true, 'counts_as_delivered' => false, 'counts_as_cancelled' => false, 'counts_as_return' => false, 'counts_as_shipped' => false, 'marks_payment_paid' => false, 'restores_stock' => false, 'reduces_stock' => true, 'sms_key' => 'scheduled'],
            ['name' => 'Confirmed', 'slug' => 'confirmed', 'status_group' => 'active', 'badge_class' => 'bg-primary', 'sort_order' => 50, 'is_active' => true, 'is_default' => true, 'counts_as_active' => true, 'counts_as_delivered' => false, 'counts_as_cancelled' => false, 'counts_as_return' => false, 'counts_as_shipped' => false, 'marks_payment_paid' => false, 'restores_stock' => false, 'reduces_stock' => true, 'sms_key' => 'confirmed'],
            ['name' => 'Cancelled', 'slug' => 'cancelled', 'status_group' => 'cancelled', 'badge_class' => 'bg-danger', 'sort_order' => 60, 'is_active' => true, 'is_default' => true, 'counts_as_active' => false, 'counts_as_delivered' => false, 'counts_as_cancelled' => true, 'counts_as_return' => false, 'counts_as_shipped' => false, 'marks_payment_paid' => false, 'restores_stock' => true, 'reduces_stock' => false, 'sms_key' => 'cancelled'],
            ['name' => 'Processing', 'slug' => 'processing', 'status_group' => 'active', 'badge_class' => 'bg-info text-white', 'sort_order' => 70, 'is_active' => true, 'is_default' => true, 'counts_as_active' => true, 'counts_as_delivered' => false, 'counts_as_cancelled' => false, 'counts_as_return' => false, 'counts_as_shipped' => false, 'marks_payment_paid' => false, 'restores_stock' => false, 'reduces_stock' => true, 'sms_key' => 'processing'],
            ['name' => 'Courier Complete', 'slug' => 'courier-complete', 'status_group' => 'active', 'badge_class' => 'bg-primary', 'sort_order' => 80, 'is_active' => true, 'is_default' => true, 'counts_as_active' => true, 'counts_as_delivered' => false, 'counts_as_cancelled' => false, 'counts_as_return' => false, 'counts_as_shipped' => true, 'marks_payment_paid' => false, 'restores_stock' => false, 'reduces_stock' => true, 'sms_key' => 'courier complete'],
            ['name' => 'Shipped', 'slug' => 'shipped', 'status_group' => 'active', 'badge_class' => 'bg-primary', 'sort_order' => 90, 'is_active' => true, 'is_default' => true, 'counts_as_active' => true, 'counts_as_delivered' => false, 'counts_as_cancelled' => false, 'counts_as_return' => false, 'counts_as_shipped' => true, 'marks_payment_paid' => false, 'restores_stock' => false, 'reduces_stock' => true, 'sms_key' => 'shipped'],
            ['name' => 'Delivered', 'slug' => 'delivered', 'status_group' => 'delivered', 'badge_class' => 'bg-success', 'sort_order' => 100, 'is_active' => true, 'is_default' => true, 'counts_as_active' => true, 'counts_as_delivered' => true, 'counts_as_cancelled' => false, 'counts_as_return' => false, 'counts_as_shipped' => false, 'marks_payment_paid' => true, 'restores_stock' => false, 'reduces_stock' => true, 'sms_key' => 'delivered'],
            ['name' => 'Returning', 'slug' => 'returning', 'status_group' => 'return', 'badge_class' => 'bg-danger', 'sort_order' => 110, 'is_active' => true, 'is_default' => true, 'counts_as_active' => false, 'counts_as_delivered' => false, 'counts_as_cancelled' => false, 'counts_as_return' => true, 'counts_as_shipped' => false, 'marks_payment_paid' => false, 'restores_stock' => true, 'reduces_stock' => false, 'sms_key' => 'returning'],
            ['name' => 'Return Received', 'slug' => 'return-received', 'status_group' => 'return', 'badge_class' => 'bg-danger', 'sort_order' => 120, 'is_active' => true, 'is_default' => true, 'counts_as_active' => false, 'counts_as_delivered' => false, 'counts_as_cancelled' => false, 'counts_as_return' => true, 'counts_as_shipped' => false, 'marks_payment_paid' => false, 'restores_stock' => true, 'reduces_stock' => false, 'sms_key' => 'return received'],
            ['name' => 'Return Missing', 'slug' => 'return-missing', 'status_group' => 'return', 'badge_class' => 'bg-danger', 'sort_order' => 130, 'is_active' => true, 'is_default' => true, 'counts_as_active' => false, 'counts_as_delivered' => false, 'counts_as_cancelled' => false, 'counts_as_return' => true, 'counts_as_shipped' => false, 'marks_payment_paid' => false, 'restores_stock' => true, 'reduces_stock' => false, 'sms_key' => 'return missing'],
            ['name' => 'Trash', 'slug' => 'trash', 'status_group' => 'cancelled', 'badge_class' => 'bg-dark', 'sort_order' => 140, 'is_active' => true, 'is_default' => true, 'counts_as_active' => false, 'counts_as_delivered' => false, 'counts_as_cancelled' => true, 'counts_as_return' => false, 'counts_as_shipped' => false, 'marks_payment_paid' => false, 'restores_stock' => true, 'reduces_stock' => false, 'sms_key' => null],
        ];
    }

    public static function normalize(?string $status = null): string
    {
        return trim(Str::lower((string) $status));
    }

    public static function makeSlug(string $name): string
    {
        return Str::slug($name) ?: Str::slug(Str::random(8));
    }

    public static function activeOptions(bool $includeAll = true): array
    {
        $options = $includeAll ? ['' => 'All Order'] : [];
        $rows = static::configuredStatuses()->filter(fn ($row) => $row['is_active'] ?? true);

        foreach ($rows as $row) {
            $options[$row['name']] = $row['name'];
        }

        return $options;
    }

    public static function forStatus(?string $status = null): array
    {
        $needle = static::normalize($status);
        $slug = static::makeSlug((string) $status);

        foreach (static::configuredStatuses() as $row) {
            if (static::normalize($row['name']) === $needle || ($row['slug'] ?? '') === $slug) {
                return $row;
            }
        }

        return [
            'name' => (string) $status,
            'slug' => $slug,
            'status_group' => 'custom',
            'badge_class' => 'bg-secondary',
            'is_active' => true,
            'counts_as_active' => false,
            'counts_as_delivered' => false,
            'counts_as_cancelled' => false,
            'counts_as_return' => false,
            'counts_as_shipped' => false,
            'marks_payment_paid' => false,
            'restores_stock' => false,
            'reduces_stock' => false,
            'sms_key' => null,
        ];
    }

    public static function namesForFlag(string $flag): array
    {
        $names = static::configuredStatuses()
            ->filter(fn ($row) => !empty($row[$flag]))
            ->pluck('name')
            ->values()
            ->all();

        if ($flag === 'counts_as_delivered') {
            $names = array_merge($names, ['Complete', 'completed', 'delivered']);
        } elseif ($flag === 'counts_as_cancelled') {
            $names = array_merge($names, ['cancelled', 'Cancell', 'cancell', 'Canceled', 'canceled', 'trash', 'Trash']);
        } elseif ($flag === 'counts_as_return') {
            $names = array_merge($names, ['returning', 'return received', 'return missing', 'Return', 'return']);
        } elseif ($flag === 'counts_as_active') {
            $names = array_merge($names, ['pending', 'incomplete', 'on hold', 'scheduled', 'confirmed', 'processing', 'courier complete', 'shipped', 'delivered', 'courier', 'complete']);
        } elseif ($flag === 'counts_as_shipped') {
            $names = array_merge($names, ['courier', 'Shipped', 'shipped']);
        }

        return array_values(array_unique(array_filter($names, fn ($name) => $name !== '')));
    }

    public static function labelFor(?string $status = null): string
    {
        $row = static::forStatus($status);
        return $row['name'] ?: ucfirst((string) $status);
    }

    public static function badgeClassFor(?string $status = null): string
    {
        return static::forStatus($status)['badge_class'] ?? 'bg-secondary';
    }

    public static function smsKeyFor(?string $status = null): string
    {
        return static::forStatus($status)['sms_key'] ?: static::normalize($status);
    }

    public static function clearStatusCache(): void
    {
        Cache::forget('order_statuses.configured');
    }

    private static function configuredStatuses()
    {
        if (!static::tableReady()) {
            return collect(static::defaults());
        }

        try {
            return Cache::remember('order_statuses.configured', 300, function () {
                return static::query()
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($status) => $status->toArray());
            });
        } catch (\Throwable $e) {
            return collect(static::defaults());
        }
    }

    private static function tableReady(): bool
    {
        try {
            return Schema::hasTable('order_statuses');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
