<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyLog extends Model
{
    protected $fillable = [
        'action',
        'supply_id',
        'quantity',
        'user_id',
        'citizen_id',
        'notes'
    ];
}
