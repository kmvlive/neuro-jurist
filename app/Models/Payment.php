<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'order_id', 'payment_id', 'plan',
        'amount', 'status', 'tinkoff_status', 'response', 'paid_at',
        'promo_code', 'original_amount'
    ];

    protected $casts = [
        'response' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
