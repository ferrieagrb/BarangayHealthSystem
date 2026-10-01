<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    protected $fillable = ['family_name', 'purok_id', 'subgroup_id'];

    public function purok()
    {
        return $this->belongsTo(Purok::class);
    }

    public function subgroup()
    {
        return $this->belongsTo(PurokSubgroup::class, 'subgroup_id');
    }

    public function members()
    {
        return $this->hasMany(citizens::class, 'family_id');
    }
}