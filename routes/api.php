<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiquidateDisposalController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Liquidation and Disposal API Routes
Route::middleware('auth')->group(function () {
    // Disposal routes
    Route::post('/disposals/{id}/approve', [LiquidateDisposalController::class, 'approveDisposal']);
    Route::post('/disposals/{id}/reject', [LiquidateDisposalController::class, 'rejectDisposal']);
    Route::post('/disposals/{id}/rollback', [LiquidateDisposalController::class, 'rollbackDisposal']);
    
    // Liquidation routes
    Route::post('/liquidates/{id}/approve', [LiquidateDisposalController::class, 'approveLiquidate']);
    Route::post('/liquidates/{id}/reject', [LiquidateDisposalController::class, 'rejectLiquidate']);
    Route::post('/liquidates/{id}/rollback', [LiquidateDisposalController::class, 'rollbackLiquidate']);
});
