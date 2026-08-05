<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DynamicLandingPageComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'dynamic_landing_page_id',
        'component_key',
        'instance_scope',
        'sort_order',
        'config',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_enabled' => 'boolean',
        ];
    }

    public function dynamicLandingPage(): BelongsTo
    {
        return $this->belongsTo(DynamicLandingPage::class);
    }

    public function page(): BelongsTo
    {
        return $this->dynamicLandingPage();
    }
}
