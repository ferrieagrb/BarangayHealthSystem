<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AnnouncementImage;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category'
        ,'user_id'
        ,'facebook_post_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(AnnouncementImage::class)->orderBy('sort_order');
    }
}
