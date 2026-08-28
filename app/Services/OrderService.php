<?php

namespace App\Services;

use App\Models\Order;
use App\Models\QuotationNode;
use App\Models\Quotation;
use App\Repositories\OrderRepository;
use App\Repositories\PricingSnapshotRepository;
use App\Repositories\QuotationRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    protected OrderRepository $orderRepo;
    protected PricingSnapshotRepository $snapshotRepo;
    protected QuotationRepository $quotationRepo;
    protected QuotationNodePricingEngine $pricingEngine;

    public function __construct(
        OrderRepository $orderRepo,
        PricingSnapshotRepository $snapshotRepo,
        QuotationRepository $quotationRepo,
        QuotationNodePricingEngine $pricingEngine
    ) {
        $this->orderRepo    = $orderRepo;
        $this->snapshotRepo = $snapshotRepo;
        $this->quotationRepo = $quotationRepo;
        $this->pricingEngine = $pricingEngine;
    }

    public function getByUser(int $userId)
    {
        return $this->orderRepo->getByUser($userId);
    }

    public function getById(int $id): Order
    {
        return $this->orderRepo->getById($id);
    }

    /**
     * Checkout: create an Order from a Quotation.
     *
     * Steps:
     *  1. Idempotency check — return existing order if key already used.
     *  2. Validate quotation belongs to user and has a plan selected.
     *  3. Re-calculate all pricing server-side (never trust frontend).
     *  4. Create the Order row with locked amounts.
     *  5. Write immutable PricingSnapshot rows for every billable node.
     *  6. Mark quotation as checked_out.
     */
    public function checkout(int $quotationId, int $userId, string $idempotencyKey): Order
    {
        // 1. Idempotency guard
        $existing = $this->orderRepo->findByIdempotencyKey($idempotencyKey);
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($quotationId, $userId, $idempotencyKey) {
            // 2. Load + validate quotation
            $quotation = Quotation::with([
                'plan',
                'rootNodes.allChildren.selectedProvider',
            ])->findOrFail($quotationId);

            if (!$quotation->plan) {
                throw new \RuntimeException('A plan must be selected before checkout.');
            }
            if ($quotation->status === 'checked_out') {
                throw new \RuntimeException('This quotation has already been checked out.');
            }

            // 3. Server-side pricing recalculation
            $rootNodes = QuotationNode::with('allChildren.selectedProvider')
                ->where('quotation_id', $quotationId)
                ->whereNull('parent_node_id')
                ->orderBy('sort_order')
                ->get();

            $priceResult = $this->pricingEngine->calculate($rootNodes->all());
            $summary     = $priceResult['summary'];
            $plan        = $quotation->plan;
            $planPrice   = (float) $plan->price;
            $taxRate     = 0.18;
            $taxableAmt  = $summary['subtotal'] + $planPrice;
            $taxTotal    = round($taxableAmt * $taxRate, 2);
            $grandTotal  = round($taxableAmt + $taxTotal, 2);

            // 4. Create the locked Order row
            $order = $this->orderRepo->create([
                'user_id'                  => $userId,
                'quotation_id'             => $quotation->id,
                'plan_id'                  => $plan->id,
                'idempotency_key'          => $idempotencyKey,
                'status'                   => 'pending',
                'currency'                 => 'INR',
                'subtotal'                 => round($summary['subtotal'], 2),
                'expert_fee_total'         => round($summary['expert_fee_total'], 2),
                'one_time_total'           => round($summary['one_time_total'], 2),
                'recurring_monthly_total'  => round($summary['recurring_monthly_total'], 2),
                'plan_price'               => $planPrice,
                'discount_total'           => 0.00,
                'tax_total'                => $taxTotal,
                'grand_total'              => $grandTotal,
            ]);

            // 5. Write immutable pricing snapshots
            $this->writeSnapshots($order->id, $priceResult['breakdown'], null);

            // 6. Lock the quotation
            $quotation->update(['status' => 'checked_out']);

            return $this->orderRepo->getById($order->id);
        });
    }

    /**
     * Cancel a pending order.
     */
    public function cancel(int $id): Order
    {
        $order = Order::findOrFail($id);
        if ($order->status !== 'pending') {
            throw new \RuntimeException('Only pending orders can be cancelled.');
        }
        return $this->orderRepo->updateStatus($id, 'cancelled');
    }

    /**
     * Confirm an order (admin action).
     */
    public function confirm(int $id): Order
    {
        $order = Order::findOrFail($id);
        if ($order->status !== 'pending') {
            throw new \RuntimeException('Only pending orders can be confirmed.');
        }
        return $this->orderRepo->updateStatus($id, 'confirmed');
    }

    // ── Snapshot writing ─────────────────────────────────────────────────────

    /**
     * Recursively write pricing_snapshots from the pricing engine breakdown.
     * The breakdown is already the output of QuotationNodePricingEngine::calculate().
     */
    protected function writeSnapshots(int $orderId, array $nodes, ?int $parentSnapshotId): void
    {
        foreach ($nodes as $node) {
            $snapshotId = $this->insertSnapshot($orderId, $node, $parentSnapshotId);
            if (!empty($node['children'])) {
                $this->writeSnapshots($orderId, $node['children'], $snapshotId);
            }
        }
    }

    protected function insertSnapshot(int $orderId, array $node, ?int $parentSnapshotId): int
    {
        $row = [
            'order_id'            => $orderId,
            'quotation_node_id'   => $node['id'],
            'parent_snapshot_id'  => $parentSnapshotId,
            'depth'               => $node['depth'],
            'node_name'           => $node['name'],
            'pricing_category'    => null, // plain string copy — filled below if available
            'pricing_method'      => $node['pricing_method'] ?? null,
            'billing_type'        => $node['billing_type'] ?? null,
            'unit'                => $node['unit'] ?? null,
            'quantity'            => $node['quantity'] ?? null,
            'unit_price'          => $node['unit_price'] ?? null,
            'calculated_total'    => $node['calculated_price'] ?? 0,
            'provider_name'       => isset($node['provider'])
                ? ($node['provider']['model_name'] . ' (' . $node['provider']['company'] . ')')
                : null,
            'selected_dimensions' => isset($node['selected_dimensions'])
                ? json_encode($node['selected_dimensions'])
                : null,
            'created_at'          => now()->toDateTimeString(),
        ];

        $snapshot = \App\Models\PricingSnapshot::create($row);
        return $snapshot->id;
    }
}