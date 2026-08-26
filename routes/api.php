<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\PricingController;
use App\Http\Controllers\Api\V1\CartController;

Route::prefix('v1')->group(function () {
    Route::get('/catalog', [CatalogController::class, 'index']);
    Route::post('/pricing/calculate', [PricingController::class, 'calculate']);
    Route::post('/cart/snapshot', [CartController::class, 'snapshot']);
});