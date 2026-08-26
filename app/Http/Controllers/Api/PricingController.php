<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CalculatePricingRequest;
use App\Repositories\PlanRepository;
use App\Services\PricingEngineService;

class PricingController extends Controller
{
    protected $pricingEngineService;
    protected $planRepository;

    public function __construct(PricingEngineService $pricingEngineService, PlanRepository $planRepository)
    {
        $this->pricingEngineService = $pricingEngineService;
        $this->planRepository = $planRepository;
    }

    /**
     * Calculate quotation pricing.
     * Endpoint: POST /api/pricing/calculate
     */
    public function calculate(CalculatePricingRequest $request)
    {
        $payload = $request->validated();
        
        $planFee = $this->planRepository->getPlanFeeByCode($payload['selectedPlanId'] ?? null);

        $pricingData = $this->pricingEngineService->calculateQuotationPricing($payload, $planFee);

        return response()->json([
            'success' => true,
            'data' => array_merge(['automationId' => $payload['automationId'], 'currency' => 'INR'], $pricingData)
        ]);
    }
}
