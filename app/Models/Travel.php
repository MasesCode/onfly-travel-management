<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Travel extends Model
{
    protected $fillable = [
        'order_id',
        'pickup_address',
        'delivery_address',
        'recipient_name',
        'recipient_email',
        'recipient_cpf',
        'weight',
        'height',
        'width',
        'length',
        'is_private_send',
    ];

    protected $casts = [
        'is_private_send' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
