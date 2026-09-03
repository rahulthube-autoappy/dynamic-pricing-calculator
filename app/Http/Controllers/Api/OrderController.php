<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $userId = $request->input('user_id', 1);
        Log::info("Order: Listing orders for user", ['user_id' => $userId]);
        return OrderResource::collection($this->service->getByUser((int) $userId));
    }

    /**
     * GET /api/orders/{id} — Show a single order with plan, quotation and pricing snapshots
     */
    public function show($id)
    {
        Log::info("Order: Viewing order details", ['order_id' => $id]);
        return new OrderResource($this->service->getById((string) $id));
    }

    /**
     * POST /api/checkout — Checkout a quotation to create an order
     */
    public function checkout(CheckoutRequest $request)
    {
        $data = $request->validated();
        $userId = $data['user_id'] ?? 1;
        
        Log::info("Order: Checkout initiated", [
            'quotation_id'    => $data['quotation_id'],
            'user_id'         => $userId,
            'idempotency_key' => $data['idempotency_key'],
        ]);

        try {
            $order = $this->service->checkout(
                (string) $data['quotation_id'],
                (int) $userId,
                $data['idempotency_key']
            );
            Log::info("Order: Checkout successful", [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'grand_total'  => $order->grand_total,
            ]);
            return (new OrderResource($order))->response()->setStatusCode(201);
        } catch (\RuntimeException $e) {
            Log::warning("Order: Checkout failed", [
                'quotation_id' => $data['quotation_id'],
                'error'        => $e->getMessage(),
            ]);
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
        Log::info("Order: Cancellation requested", ['order_id' => $id]);
        try {
            $order = $this->service->cancel((string) $id);
            Log::info("Order: Cancelled successfully", ['order_id' => $id]);
            return new OrderResource($order);
        } catch (\RuntimeException $e) {
            Log::warning("Order: Cancellation failed", ['order_id' => $id, 'error' => $e->getMessage()]);
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
        Log::info("Order: Confirmation requested", ['order_id' => $id]);
        try {
            $order = $this->service->confirm((string) $id);
            Log::info("Order: Confirmed successfully", ['order_id' => $id]);
            return new OrderResource($order);
        } catch (\RuntimeException $e) {
            Log::warning("Order: Confirmation failed", ['order_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}