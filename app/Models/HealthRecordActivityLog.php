<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthRecordActivityLog extends Model
{
    protected $table = 'health_record_activity_logs';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'action',
        'citizen_id',
        'health_record_id',
        'description',
    ];
}
