<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ComponentRepository;
use App\Repositories\ProviderRepository;
use App\Services\PricingEngineService;

class ComponentController extends Controller
{
    protected $componentRepository;
    protected $providerRepository;
    protected $pricingEngineService;

    public function __construct(
        ComponentRepository $componentRepository,
        ProviderRepository $providerRepository,
        PricingEngineService $pricingEngineService
    ) {
        $this->componentRepository = $componentRepository;
        $this->providerRepository = $providerRepository;
        $this->pricingEngineService = $pricingEngineService;
    }

    public function getBundleEstimate($id)
    {
        $bundle = $this->componentRepository->getBundleWithChildren($id);

        $summary = [
            'one_time_total' => 0,
            'recurring_monthly_subtotal' => 0,
            'estimated_price' => 0,
            'currency' => 'INR'
        ];
        $keyPoints = [];

        $allProviders = $this->providerRepository->getAllActiveKeyedById();

        $formattedHierarchy = $this->pricingEngineService->processNode($bundle, $summary, $keyPoints, $allProviders);

        $summary['estimated_price'] = $summary['one_time_total'] + $summary['recurring_monthly_subtotal'];

        return response()->json([
            'success' => true,
            'data' => [
                'bundle' => [
                    'id' => $bundle->id,
                    'uuid' => $bundle->uuid,
                    'name' => $bundle->name,
                    'platform' => $bundle->platform,
                    'category' => $bundle->category,
                    'description' => $bundle->description,
                ],
                'summary' => [
                    'one_time_total' => round($summary['one_time_total'], 2),
                    'recurring_monthly_subtotal' => round($summary['recurring_monthly_subtotal'], 2),
                    'estimated_price' => round($summary['estimated_price'], 2),
                    'currency' => $summary['currency']
                ],
                'key_points' => $keyPoints,
                'hierarchy' => $formattedHierarchy
            ]
        ]);
    }
}
