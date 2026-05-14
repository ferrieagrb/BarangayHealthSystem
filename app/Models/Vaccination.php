<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\citizens;

class Vaccination extends Model
{
    protected $fillable = [
        'citizen_id',
        'vaccine_name',
        'date_administered',
        'notes',
    ];

    public function citizen()
    {
        return $this->belongsTo(citizens::class, 'citizen_id');
    }

    public function vaccinations()
{
    return $this->hasMany(Vaccination::class, 'citizen_id', 'id');
}
}