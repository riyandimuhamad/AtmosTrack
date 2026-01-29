<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherLog extends Model
{
    use HasFactory;

    // Data disimpan ke database
    protected $fillable = [
        'user_id',
        'city_name',
        'temperature',
        'description',
    ];
}