<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $fillable = [
        'date_of_referral',
        'name',
        'age',
        'gender',
        'address',
        'requests_for',
        'vital_signs',
        'treatment_given',
        'medication_given',
        'self_medication',
        'maintenance_schedule',
        'referred_by',
        'status',
        'file_path',
    ];
}