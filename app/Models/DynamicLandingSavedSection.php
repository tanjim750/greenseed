<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicLandingSavedSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'components',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'components' => 'array',
        ];
    }
}
