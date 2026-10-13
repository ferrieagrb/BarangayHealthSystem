<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurokSubgroup extends Model
{
    protected $fillable = ['purok_id', 'name','user_id'];

    public function purok()
{
    return $this->belongsTo(Purok::class, 'purok_id');
}

    public function families()
    {
        return $this->hasMany(Family::class, 'subgroup_id');
    }

    public function bhw()
{
    return $this->belongsTo(User::class, 'user_id');
}
}