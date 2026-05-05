<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleLog extends Model
{
    protected $fillable = [
    'vehicle_name',
    'borrower',
    'borrowed_at',
];

    protected $table = 'vehicle_logs';
}
