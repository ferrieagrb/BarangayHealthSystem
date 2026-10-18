<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'citizen_id',
        'medicine_name',
        'dosage',
        'frequency',
        'start_date',
        'end_date',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function citizen()
    {
        return $this->belongsTo(citizens::class, 'citizen_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}