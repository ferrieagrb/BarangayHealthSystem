<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = ['family_name', 'purok_id', 'subgroup_id'];

    protected static function booted(): void
    {
        static::updated(function (Family $family) {
            if ($family->wasChanged(['purok_id', 'subgroup_id'])) {
                $family->loadMissing(['purok', 'subgroup']);
                $family->members()->update([
                    'Citizen_Purok' => $family->purok->name,
                    'subgroup_id' => $family->subgroup->id,
                ]);
            }
        });
    }

    public function purok()
    {
        return $this->belongsTo(Purok::class)->withTrashed();
    }

    public function subgroup()
    {
        return $this->belongsTo(PurokSubgroup::class, 'subgroup_id')->withTrashed();
    }

    public function members()
    {
        return $this->hasMany(citizens::class, 'family_id');
    }
}