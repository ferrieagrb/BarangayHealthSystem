<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleLog extends Model
{
    protected $fillable = [
    'vehicle_name',
    'borrower',
    'borrowed_at',
    'user_id',
];

    protected $table = 'vehicle_logs';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
