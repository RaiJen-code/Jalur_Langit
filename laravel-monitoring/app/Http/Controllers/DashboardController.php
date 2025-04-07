<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
    }

    public function getLatestData(Request $request)
    {
        try {
            // Enable query log untuk debugging
            DB::enableQueryLog();

            // Ambil data sensor
            $sensorData = DB::table('sensors')
                ->select('*')
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();

            // Ambil data power
            $powerData = DB::table('powers')
                ->select('*')
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();

            // Debug info
            $queries = DB::getQueryLog();

            // Format data
            $formattedSensors = $sensorData->map(function($item) {
                return [
                    'suhu' => $item->suhu,
                    'kelembapan' => $item->kelembapan,
                    'cahaya' => $item->cahaya,
                    'created_at' => Carbon::parse($item->created_at)->format('Y-m-d H:i:s')
                ];
            });

            $formattedPowers = $powerData->map(function($item) {
                return [
                    'voltage' => $item->voltage,
                    'current' => $item->current,
                    'reactive_energy' => $item->reactive_energy,
                    'reactive_power' => $item->reactive_power,
                    'frequency' => $item->frequency,
                    'power_factor' => $item->power_factor,
                    'apparent_power' => $item->apparent_power,
                    'created_at' => Carbon::parse($item->created_at)->format('Y-m-d H:i:s')
                ];
            });

            return response()->json([
                'debug' => [
                    'queries' => $queries,
                    'sensor_count' => $sensorData->count(),
                    'power_count' => $powerData->count()
                ],
                'sensor' => [
                    'latest' => $formattedSensors->first(),
                    'history' => $formattedSensors
                ],
                'power' => [
                    'latest' => $formattedPowers->first(),
                    'history' => $formattedPowers
                ],
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'sql' => DB::getQueryLog(),
            ], 500);
        }
    }
}