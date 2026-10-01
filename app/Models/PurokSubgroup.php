<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurokSubgroup extends Model
{
    protected $fillable = ['purok_id', 'name'];

    public function purok()
    {
        return $this->belongsTo(Purok::class);
    }

    public function families()
    {
        return $this->hasMany(Family::class, 'subgroup_id');
    }
}