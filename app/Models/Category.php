<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Type;
use App\Models\Product;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Type relation
    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    // Products relation
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Parent category
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Child categories (Level-1)
    public function subcats()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('name');
    }

    // Recursive children (Level-1,2,3...)
    public function subcatsRecursive()
    {
        return $this->subcats()->with('subcatsRecursive');
    }
}
