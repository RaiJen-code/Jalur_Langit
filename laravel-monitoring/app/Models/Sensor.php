<?php
// app/Models/Sensor.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    protected $fillable = [
        'suhu',
        'kelembapan',
        'cahaya',
        'sensor_timestamp',
        'created_at',    // Tambahkan ini
        'updated_at'     // Tambahkan ini
    ];

    protected $casts = [
        'suhu' => 'float',
        'kelembapan' => 'float',
        'cahaya' => 'float',
        'sensor_timestamp' => 'datetime',
        'created_at' => 'datetime',     // Tambahkan ini
        'updated_at' => 'datetime'      // Tambahkan ini
    ];
}