<?php
// app/Models/Power.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Power extends Model
{
    protected $fillable = [
        'reactive_energy',
        'reactive_power',
        'current',
        'voltage',
        'frequency',
        'power_factor',
        'apparent_power',
        'power_timestamp',
        'created_at',    // Tambahkan ini
        'updated_at'     // Tambahkan ini
    ];

    protected $casts = [
        'reactive_energy' => 'float',
        'reactive_power' => 'float',
        'current' => 'float',
        'voltage' => 'float',
        'frequency' => 'float',
        'power_factor' => 'float',
        'apparent_power' => 'float',
        'power_timestamp' => 'datetime',
        'created_at' => 'datetime',     // Tambahkan ini
        'updated_at' => 'datetime'      // Tambahkan ini
    ];
}