<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purok extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = ['name', 'description', 'user_id', 'latitude', 'longitude', 'boundary'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'boundary' => 'array',
    ];

    public static function orderedWithSubgroups()
    {
        return static::with('subgroups')->get()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->each(function (self $purok): void {
                $purok->setRelation(
                    'subgroups',
                    $purok->subgroups->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values()
                );
            });
    }

    public function bhw()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
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