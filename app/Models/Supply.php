<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    protected $fillable = [
    'name',
    'category',
    'quantity',
    'unit',
    'expiration_date',
    'supplier',
    'description'
];



}
