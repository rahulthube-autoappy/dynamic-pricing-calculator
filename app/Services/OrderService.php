<?php

namespace App\Services;

use App\Models\Order;
use App\Models\QuotationNode;
use App\Models\Quotation;
use App\Repositories\OrderRepository;
use App\Repositories\PricingSnapshotRepository;
use App\Repositories\QuotationRepository;
use Illuminate\Support\Facades\DB;

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

    public function getById(string $id): Order
    {
        return $this->orderRepo->getById($id);
    }

    public function checkout(string $quotationId, int $userId, string $idempotencyKey): Order
    {
        $existing = $this->orderRepo->findByIdempotencyKey($idempotencyKey);
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($quotationId, $userId, $idempotencyKey) {
            $quotation = Quotation::with('selectedPlan')->findOrFail($quotationId);

            if (!$quotation->selectedPlan) {
                throw new \RuntimeException('A plan must be selected before checkout.');
            }
            if ($quotation->status === 'checked_out') {
                throw new \RuntimeException('This quotation has already been checked out.');
            }

            $allNodes = QuotationNode::with('selectedProvider', 'pricingCategory')
                ->where('quotation_id', $quotationId)
                ->orderBy('sort_order')
                ->get();

            foreach ($allNodes as $item) {
                $children = $allNodes->filter(fn($c) => $c->parent_node_id === $item->id)->values();
                $item->setRelation('children', $children);
            }

            $rootNodes = $allNodes->whereNull('parent_node_id')->values();

            $priceResult = $this->pricingEngine->calculate($rootNodes->all());
            $summary     = $priceResult['summary'];
            $plan        = $quotation->selectedPlan;
            $planPrice   = (float) $plan->price;
            $taxRate     = 0.18;
            $taxableAmt  = $summary['subtotal'] + $planPrice;
            $taxTotal    = round($taxableAmt * $taxRate, 2);
            $grandTotal  = round($taxableAmt + $taxTotal, 2);

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

            $this->writeSnapshots($order->id, $priceResult['breakdown'], null);

            $quotation->update(['status' => 'checked_out']);

            return $this->orderRepo->getById($order->id);
        });
    }

    public function cancel(string $id): Order
    {
        $order = Order::findOrFail($id);
        if ($order->status !== 'pending') {
            throw new \RuntimeException('Only pending orders can be cancelled.');
        }
        return $this->orderRepo->updateStatus($id, 'cancelled');
    }

    public function confirm(string $id): Order
    {
        $order = Order::findOrFail($id);
        if ($order->status !== 'pending') {
            throw new \RuntimeException('Only pending orders can be confirmed.');
        }
        return $this->orderRepo->updateStatus($id, 'confirmed');
    }

    protected function writeSnapshots(string $orderId, array $nodes, ?string $parentSnapshotId): void
    {
        foreach ($nodes as $node) {
            $snapshotId = $this->insertSnapshot($orderId, $node, $parentSnapshotId);
            if (!empty($node['children'])) {
                $this->writeSnapshots($orderId, $node['children'], $snapshotId);
            }
        }
    }

    protected function insertSnapshot(string $orderId, array $node, ?string $parentSnapshotId): string
    {
        $row = [
            'order_id'            => $orderId,
            'quotation_node_id'   => $node['id'],
            'parent_snapshot_id'  => $parentSnapshotId,
            'depth'               => $node['depth'],
            'node_name'           => $node['name'],
            'pricing_category'    => null,
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