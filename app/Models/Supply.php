<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    protected $fillable = [
        'name',
        'item_number',
        'serial_number',
        'category',
        'quantity',
        'unit',
        'min_stock',
        'expiration_date',
        'supplier',
        'description',
        'status',
    ];

    protected $casts = [
        'expiration_date' => 'date',
    ];
}