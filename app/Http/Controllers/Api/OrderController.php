<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $service;

    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/orders — List all orders for the user
     */
    public function index(Request $request)
    {
        $userId = $request->input('user_id', 1); // fallback to user_id=1 for dev
        return OrderResource::collection($this->service->getByUser($userId));
    }

    /**
     * GET /api/orders/{id} — Show a single order with plan, quotation and pricing snapshots
     */
    public function show($id)
    {
        return new OrderResource($this->service->getById((int) $id));
    }

    /**
     * POST /api/checkout — Checkout a quotation to create an order
     */
    public function checkout(CheckoutRequest $request)
    {
        $data = $request->validated();
        $userId = $data['user_id'] ?? 1; // default user_id=1 for dev
        
        try {
            $order = $this->service->checkout(
                (int) $data['quotation_id'],
                (int) $userId,
                $data['idempotency_key']
            );
            return (new OrderResource($order))->response()->setStatusCode(201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /api/orders/{id}/cancel — Cancel a pending order
     */
    public function cancel($id)
    {
        try {
            $order = $this->service->cancel((int) $id);
            return new OrderResource($order);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /api/orders/{id}/confirm — Confirm a pending order (admin simulation)
     */
    public function confirm($id)
    {
        try {
            $order = $this->service->confirm((int) $id);
            return new OrderResource($order);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}