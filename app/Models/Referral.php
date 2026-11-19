<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $fillable = [
        'citizen_id',
        'birth_date',
        'contact_number',
        'referred_at',
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
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'referred_at' => 'datetime',
        ];
    }

    public function citizen()
    {
        return $this->belongsTo(citizens::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
