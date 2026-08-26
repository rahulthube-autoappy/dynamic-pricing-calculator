<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ComponentController;

Route::get('/components/bundle/{id}/estimate', [ComponentController::class, 'getBundleEstimate']);
Route::get('/catalog', [CatalogController::class, 'index']);
Route::post('/pricing/calculate', [PricingController::class, 'calculate']);
Route::post('/cart/snapshot', [CartController::class, 'snapshot']);

use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\PricingCategoryController;
use App\Http\Controllers\Api\ProviderController;

Route::apiResource('plans', PlanController::class);
Route::apiResource('pricing-categories', PricingCategoryController::class);
Route::apiResource('providers', ProviderController::class);
