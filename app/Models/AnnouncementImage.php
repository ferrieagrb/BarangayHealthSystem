<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementImage extends Model
{
    protected $fillable = [
        'announcement_id',
        'path',
        'sort_order',
    ];

    public function getUrlAttribute(): string
    {
        return route('announcement.image', ['path' => $this->path]);
    }

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}