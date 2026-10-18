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

    public function supply()
    {
        return $this->belongsTo(Supply::class, 'supply_id');
    }

    public function citizen()
    {
        return $this->belongsTo(citizens::class, 'citizen_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
