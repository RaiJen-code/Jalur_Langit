<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Threshold extends Model
{
    protected $fillable = [
        'parameter',
        'min_value',
        'max_value',
        'is_active'
    ];

    protected $casts = [
        'min_value' => 'float',
        'max_value' => 'float',
        'is_active' => 'boolean'
    ];
}