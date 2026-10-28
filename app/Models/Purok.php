<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purok extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'user_id', 'latitude', 'longitude', 'boundary'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'boundary' => 'array',
    ];

    public function bhw()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

        public function subgroups()
        {
            return $this->hasMany(PurokSubgroup::class);
        }

        public function families()
        {
            return $this->hasMany(Family::class);
        }
}