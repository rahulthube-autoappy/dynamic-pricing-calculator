<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSnapshotRequest;
use App\Services\CartService;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Complete checkout and generate a snapshot (order).
     * Endpoint: POST /api/cart/snapshot
     */
    public function snapshot(CreateSnapshotRequest $request)
    {
        $payload = $request->validated();
        
        $snapshotData = $this->cartService->createOrderSnapshot($payload);

        return response()->json([
            'success' => true,
            'data' => $snapshotData
        ]);
    }
}
