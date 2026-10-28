<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'title',
        'start',
        'time',
        'description',
        'announcement_id',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
