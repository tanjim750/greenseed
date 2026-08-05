<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DynamicLandingActionAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'dynamic_landing_page_component_id',
        'dynamic_landing_page_version_id',
        'source_component_id',
        'action_key',
        'idempotency_key',
        'request_hash',
        'status',
        'order_id',
        'response',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'response' => 'array',
        ];
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(DynamicLandingPageComponent::class, 'dynamic_landing_page_component_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(DynamicLandingPageVersion::class, 'dynamic_landing_page_version_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
