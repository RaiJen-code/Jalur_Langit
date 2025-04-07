<?php

namespace App\Http\Controllers;

use App\Models\Threshold;
use Illuminate\Http\Request;

class ThresholdController extends Controller
{
    public function index()
    {
        $thresholds = Threshold::all();
        return response()->json($thresholds);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parameter' => 'required|string',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric',
            'is_active' => 'boolean'
        ]);

        $threshold = Threshold::create($validated);
        return response()->json($threshold, 201);
    }

    public function update(Request $request, Threshold $threshold)
    {
        $validated = $request->validate([
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric',
            'is_active' => 'boolean'
        ]);

        $threshold->update($validated);
        return response()->json($threshold);
    }
}