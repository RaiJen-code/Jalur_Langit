<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThresholdController;
use App\Http\Controllers\DashboardController;


Route::apiResource('thresholds', ThresholdController::class);
// Dan route untuk testing
Route::post('/power-test', [DashboardController::class, 'testPowerData']);

