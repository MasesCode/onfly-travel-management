<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $order_status_id
 * @property string $requester_name
 * @property string $destination
 * @property string $departure_date
 * @property string $return_date
 */
class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_status_id',
        'requester_name',
        'destination',
        'departure_date',
        'return_date',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }
}
