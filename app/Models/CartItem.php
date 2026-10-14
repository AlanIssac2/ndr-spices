<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'variant_id',
        'quantity',
    ];

    // Link back to the user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Link back to the product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}