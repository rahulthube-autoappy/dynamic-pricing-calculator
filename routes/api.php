<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ComponentController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\PricingCategoryController;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\QuotationNodeController;
use App\Http\Controllers\Api\OrderController;

// ── Catalog ──────────────────────────────────────────────────────────────────
Route::get('/catalog', [CatalogController::class, 'index']);

// ── Master Library: Plans, PricingCategories, Providers ─────────────────────
Route::apiResource('plans', PlanController::class);
Route::apiResource('pricing-categories', PricingCategoryController::class);
Route::apiResource('providers', ProviderController::class);

// ── Components (master library) ──────────────────────────────────────────────
Route::get('/components/bundles', [ComponentController::class, 'bundles']);
Route::get('/components/groups', [ComponentController::class, 'groups']);
Route::get('/components/subcomponents', [ComponentController::class, 'subcomponents']);
Route::get('/components/leaves', [ComponentController::class, 'leaves']);
Route::get('/components/bundle/{id}/estimate', [ComponentController::class, 'getComponentEstimate']);
Route::get('/components/{id}/estimate', [ComponentController::class, 'getComponentEstimate']);
Route::apiResource('components', ComponentController::class);

// ── Quotations ───────────────────────────────────────────────────────────────
Route::post('/quotations/{id}/calculate', [QuotationController::class, 'calculate']);
Route::apiResource('quotations', QuotationController::class);

// ── Quotation Nodes (nested under quotations) ────────────────────────────────
Route::prefix('quotations/{quotationId}/nodes')->group(function () {
    Route::get('/', [QuotationNodeController::class, 'index']);
    Route::post('/', [QuotationNodeController::class, 'store']);
    Route::put('/{nodeId}', [QuotationNodeController::class, 'update']);
    Route::patch('/{nodeId}', [QuotationNodeController::class, 'update']);
    Route::delete('/{nodeId}', [QuotationNodeController::class, 'destroy']);
    Route::patch('/{nodeId}/toggle', [QuotationNodeController::class, 'toggleSelection']);
});

// ── Checkout & Orders ────────────────────────────────────────────────────────
Route::post('/checkout', [OrderController::class, 'checkout']);
Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
Route::post('/orders/{id}/confirm', [OrderController::class, 'confirm']);
Route::apiResource('orders', OrderController::class)->only(['index', 'show']);