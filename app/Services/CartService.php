<?php

namespace App\Services;

class CartService
{
    public function createOrderSnapshot(array $payload)
    {
        $subtotal = 0;
        foreach ($payload['cartItems'] as $item) {
            $subtotal += $item['itemPrice'] ?? 0;
        }

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

        return [
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
        ];
    }
}
