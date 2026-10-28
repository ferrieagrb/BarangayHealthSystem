<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccinationSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'citizen_id',
        'vaccine_name',
        'dose_number',
        'scheduled_date',
        'notes',
        'status',
        'scheduled_by',
        'catch_up_reviewed',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'catch_up_reviewed' => 'boolean',
    ];

    public function citizen()
    {
        return $this->belongsTo(citizens::class, 'citizen_id')->withTrashed();
    }

    public function scheduler()
    {
        return $this->belongsTo(User::class, 'scheduled_by')->withTrashed();
    }
}