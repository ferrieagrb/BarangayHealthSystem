<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'citizen_id',
        'medicine_name',
        'dosage',
        'quantity_dispensed',
        'date_dispensed',
    ];

    public function citizen()
    {
        return $this->belongsTo(citizens::class, 'citizen_id');
    }
}
