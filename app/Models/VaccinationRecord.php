<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccinationRecord extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'citizen_id',
        'vaccine_name',
        'cvx_code',
        'vaccine_formulation',
        'dose_number',
        'date_administered',
        'administered_by',
        'historical_recording',
        'eligibility_review_required',
        'eligibility_review_reason',
        'catch_up_reviewed',
    ];

    protected $casts = [
        'date_administered' => 'date',
        'historical_recording' => 'boolean',
        'eligibility_review_required' => 'boolean',
        'catch_up_reviewed' => 'boolean',
    ];

    public function citizen()
    {
        return $this->belongsTo(citizens::class, 'citizen_id')->withTrashed();
    }
}
