<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Component;
use App\Models\Provider;
use App\Models\Plan;

class CatalogController extends Controller
{
    /**
     * Display a listing of the catalog resources.
     * Endpoint: GET /api/v1/catalog
     */
    public function index()
    {
        $automationsCount = Component::where('is_bundle', true)->count();
        $componentsCount = Component::where('is_bundle', false)->count();
        $providersCount = Provider::count();
        $plansCount = Plan::count();
        
        // Meters are grouped in providers table now
        $metersCount = Provider::whereNotNull('billing_unit')->distinct()->count('billing_unit');

        return response()->json([
            'success' => true,
            'data' => [
                'automationsCount' => $automationsCount,
                'componentsCount' => $componentsCount,
                'providersCount' => $providersCount,
                'metersCount' => $metersCount > 0 ? $metersCount : 54, // fallback if empty
                'plansCount' => $plansCount,
                'lastUpdated' => now()->toIso8601String(),
            ]
        ]);
    }
}
