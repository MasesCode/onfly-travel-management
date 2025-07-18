<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_status_id',
        'requester_name',
        'destination',
        'departure_date',
        'return_date',
    ];

    protected $casts = [
        'departure_date' => 'date:Y-m-d',
        'return_date' => 'date:Y-m-d',
    ];

    protected $appends = ['start_date', 'end_date', 'requester', 'notes'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    protected function startDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->departure_date?->format('Y-m-d'),
        );
    }

    protected function endDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->return_date?->format('Y-m-d'),
        );
    }

    protected function requester(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->requester_name,
        );
    }

    protected function notes(): Attribute
    {
        return Attribute::make(
            get: fn () => '',
        );
    }
}
