<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyRequest extends Model
{
    protected $fillable = [
        'citizen_id',
        'supply_id',
        'quantity',
        'priority',
        'status',
        'bhw_remarks',
    ];

    public function citizen()
    {
        return $this->belongsTo(citizens::class, 'citizen_id');
    }

    public function supply()
    {
        return $this->belongsTo(Supply::class, 'supply_id');
    }
}