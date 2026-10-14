<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address',
        'payment_method',
        'subtotal',
        'shipping',
        'total',
        'status',          // 'pending', 'dispatched', 'completed'
        'tracking_number', // India Post consignment tracking number
        'printed_at',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}