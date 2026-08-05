<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DynamicLandingPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'theme',
        'seo',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'theme' => 'array',
            'seo' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function components(): HasMany
    {
        return $this->hasMany(DynamicLandingPageComponent::class)
            ->orderBy('sort_order');
    }

    public function enabledComponents(): HasMany
    {
        return $this->components()->where('is_enabled', true);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DynamicLandingPageVersion::class);
    }

    public function publishedVersion(): HasOne
    {
        return $this->hasOne(DynamicLandingPageVersion::class)
            ->where('status', 'published')
            ->latestOfMany('version_number');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
