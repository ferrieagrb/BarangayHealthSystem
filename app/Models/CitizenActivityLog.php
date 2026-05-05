<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitizenActivityLog extends Model
{
    protected $table = 'citizen_activity_logs';

    protected $fillable = [
    'user_id',
    'action',
    'module',
    'citizen_id',
    'description',
];
}
