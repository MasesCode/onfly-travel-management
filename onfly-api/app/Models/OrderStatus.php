<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property bool $is_custom
 */
class OrderStatus extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'is_custom',
    ];

    protected $casts = [
        'is_custom' => 'boolean',
    ];
}
