<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/send-verification', [AuthController::class, 'sendVerification']);
Route::get('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/submit', [AuthController::class, 'submitApplication']);
Route::post('/intern/apply', [AuthController::class, 'submitApplication']);


// Used by the "Available Events" cards
Route::get('/events', [EventController::class, 'getAllEvents']);
// Used by the "Complete Registration" button
Route::post('/event/register-multiple', [EventController::class, 'registerMultiple']);





