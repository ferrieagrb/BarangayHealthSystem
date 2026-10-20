<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplyRelease extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'citizen_id',
        'supply_id',
        'quantity',
        'batch_title',
        'details',
        'claim_date',
        'status',
        'claimed_at',
        'claimed_by_user_id',
    ];

    protected $casts = [
        'claim_date' => 'date',
        'claimed_at' => 'datetime',
    ];

    public function citizen()
    {
        return $this->belongsTo(citizens::class, 'citizen_id');
    }

    public function supply()
    {
        return $this->belongsTo(Supply::class, 'supply_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}