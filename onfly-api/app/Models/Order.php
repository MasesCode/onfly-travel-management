<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

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

    public function travel()
    {
        return $this->hasOne(Travel::class);
    }

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

    // Acessores virtuais para compatibilidade com o frontend
    protected $appends = ['start_date', 'end_date', 'requester', 'notes'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    // Acessor para start_date (alias para departure_date)
    protected function startDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->departure_date,
        );
    }

    // Acessor para end_date (alias para return_date)
    protected function endDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->return_date,
        );
    }

    // Acessor para requester (alias para requester_name)
    protected function requester(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->requester_name,
        );
    }

    // Acessor para notes (campo virtual por enquanto)
    protected function notes(): Attribute
    {
        return Attribute::make(
            get: fn () => '',
        );
    }
}
