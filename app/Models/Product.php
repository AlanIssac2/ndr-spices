<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_path',
        'media_paths', // <-- Add this here
        'is_active',
    ];
public function variants()
    {
        // Change ProductVariant::class to Variant::class if your model is named differently
        return $this->hasMany(\App\Models\ProductVariant::class);
    }
    // Tell Laravel to handle multiple files as an array
    protected $casts = [
        'media_paths' => 'array',
        'is_active' => 'boolean',
    ];
}