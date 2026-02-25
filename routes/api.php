<?php

use App\Http\Controllers\PremiumController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\SnapshotController;
use Illuminate\Support\Facades\Route;

Route::prefix('snapshots')->group(function () {
    Route::post('/upload', [SnapshotController::class, 'upload']);
    Route::get('/fetch/{anon_id}', [SnapshotController::class, 'fetch']);
});

Route::prefix('premium')->group(function () {
    Route::post('/verify', [PremiumController::class, 'verify']);
    Route::get('/status/{anon_id}', [PremiumController::class, 'status']);
    Route::post('/revoke', [PremiumController::class, 'revoke']);
});

Route::get('/rules/update', [RulesController::class, 'update']);
