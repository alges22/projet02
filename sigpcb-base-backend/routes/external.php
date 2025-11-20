<?php

use App\Http\Controllers\SendToAnipController;
use Illuminate\Support\Facades\Route;

Route::get('/transmissions', [SendToAnipController::class, 'getCompletedExamsData'])->middleware('checkApiKey');
