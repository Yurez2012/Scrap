<?php

use App\Http\Controllers\TechArenaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TechArenaController::class, 'index']);
Route::get('/all-profile-ids', [TechArenaController::class, 'getAllProfileIds']);
Route::get('/save-profile-ids', [TechArenaController::class, 'saveProfileIds']);
Route::get('/fetch-profiles-data', [TechArenaController::class, 'fetchProfilesData']);
