<?php

use App\Events\TestBroadcast;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/broadcast', function () {
    TestBroadcast::dispatch('Hello from PrimeForge at ' . now()->toDateTimeString());
    return response()->json(['status' => 'Event broadcast', 'time' => now()->toDateTimeString()]);
});

Route::get('/listen', function () {
    return view('listen');
});
