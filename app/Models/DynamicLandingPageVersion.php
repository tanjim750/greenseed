<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DynamicLandingPageVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'dynamic_landing_page_id',
        'version_number',
        'snapshot',
        'status',
        'created_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(DynamicLandingPage::class, 'dynamic_landing_page_id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
