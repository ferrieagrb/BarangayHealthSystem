<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccinationRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'citizen_id',
        'vaccine_name',
        'dose_number',
        'date_administered',
        'administered_by',
        'catch_up_reviewed',
    ];

    protected $casts = [
        'date_administered' => 'date',
        'eligibility_review_required' => 'boolean',
        'catch_up_reviewed' => 'boolean',
    ];

    public function citizen()
    {
        return $this->belongsTo(citizens::class, 'citizen_id');
    }
}
