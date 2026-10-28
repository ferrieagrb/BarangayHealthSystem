<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\citizens;

class HealthRecord extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'citizen_id',
        'diagnosis',
        'record_date',
        'comments',
        'severity',
        'weight',
        'height',
        'bmi',
        'temperature',
        'blood_pressure'
    ];

    public function citizen()
    {
        return $this->belongsTo(citizens::class, 'citizen_id', 'id')->withTrashed();
    }
}