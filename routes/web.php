<?php

use App\Http\Controllers\TechArenaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TechArenaController::class, 'index']);
