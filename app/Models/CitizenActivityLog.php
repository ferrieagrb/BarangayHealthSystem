<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CitizenActivityLog extends Model
{
    use HasFactory;

    protected $table = 'citizen_activity_logs'; // Ensure this matches your actual table name

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'citizen_id',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function citizen()
    {
        return $this->belongsTo(citizens::class, 'citizen_id');
    }
}