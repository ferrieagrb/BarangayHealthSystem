<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\citizens;

class PurokSubgroup extends Model
{
    protected $fillable = [
        'purok_id',
        'name',
        'description',
        'user_id',
        'latitude',
        'longitude',
        'boundary',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'boundary' => 'array',
    ];

    public function purok()
    {
        return $this->belongsTo(Purok::class, 'purok_id');
    }

    public function families()
    {
        return $this->hasMany(Family::class, 'subgroup_id');
    }

    public function citizens()
    {
        return $this->hasMany(citizens::class, 'subgroup_id');
    }

    public function bhw()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bhws()
    {
        return $this->belongsToMany(User::class, 'purok_subgroup_user')
            ->withTimestamps();
    }
}