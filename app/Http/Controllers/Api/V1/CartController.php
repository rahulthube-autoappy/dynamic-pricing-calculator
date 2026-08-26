<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CartController extends Controller
{
    /**
     * Complete checkout and generate a snapshot (order).
     * Endpoint: POST /api/v1/cart/snapshot
     */
    public function snapshot(Request $request)
    {
        $payload = $request->validate([
            'cartItems' => 'required|array',
            'selectedPlanId' => 'required|string',
            'customerDetails' => 'required|array',
            'appliedDiscounts' => 'nullable|array'
        ]);

        $subtotal = 0;
        foreach ($payload['cartItems'] as $item) {
            $subtotal += $item['itemPrice'] ?? 0;
        }

        // Mock plan fee + discounts + tax calculation
        $discountAmount = 0;
        if (!empty($payload['appliedDiscounts'])) {
            foreach ($payload['appliedDiscounts'] as $discount) {
                if (isset($discount['discountPercentage'])) {
                    $discountAmount += $subtotal * ($discount['discountPercentage'] / 100);
                }
            }
        }

        $taxableAmount = $subtotal - $discountAmount;
        $gst18 = round($taxableAmount * 0.18, 2);
        $finalAmount = $taxableAmount + $gst18;

        $quoteId = 'QUOTE-' . now()->year . '-' . rand(10000, 99999);

        // Here we would create rows in Orders and PricingSnapshots
        // Order::create([...])
        // PricingSnapshot::insert([...])

        return response()->json([
            'success' => true,
            'data' => [
                'quoteId' => $quoteId,
                'quoteUrl' => url('/quote/' . $quoteId),
                'status' => 'FROZEN',
                'createdAt' => now()->toIso8601String(),
                'expiresAt' => now()->addDays(30)->toIso8601String(),
                'pricingSummary' => [
                    'subtotal' => round($subtotal, 2),
                    'discountAmount' => round($discountAmount, 2),
                    'taxableAmount' => round($taxableAmount, 2),
                    'gst18' => $gst18,
                    'finalAmount' => round($finalAmount, 2),
                    'currency' => 'INR'
                ]
            ]
        ]);
    }
}
