<?php

namespace App\Repositories;

use App\Models\Quotation;
use App\Models\QuotationNode;

class QuotationRepository
{
    public function getByUser(int $userId, ?string $status = null, bool $includeArchived = false)
    {
        $query = Quotation::with(['selectedPlan', 'sourceComponent'])
            ->where('user_id', $userId);

        if ($status) {
            $query->where('status', $status);
        } elseif (!$includeArchived) {
            $query->where('status', '!=', 'archived');
        }

        return $query->orderByDesc('updated_at')->get();
    }

    public function getById(string $id): Quotation
    {
        $quotation = Quotation::with(['selectedPlan', 'sourceComponent'])->findOrFail($id);
        
        $allNodes = QuotationNode::with('selectedProvider', 'pricingCategory')
            ->where('quotation_id', $id)
            ->orderBy('sort_order')
            ->get();

        foreach ($allNodes as $item) {
            $children = $allNodes->filter(function ($c) use ($item) {
                return $c->parent_node_id === $item->id;
            })->values();
            $item->setRelation('children', $children);
        }

        $rootNodes = $allNodes->whereNull('parent_node_id')->values();
        $quotation->setRelation('rootNodes', $rootNodes);

        return $quotation;
    }

    public function getActiveCartForUser(int $userId): ?Quotation
    {
        return Quotation::where('user_id', $userId)
            ->where('type', 'cart')
            ->whereIn('status', ['draft', 'active'])
            ->first();
    }

    public function create(array $data): Quotation
    {
        return Quotation::create($data);
    }

    public function update(string $id, array $data): Quotation
    {
        $record = Quotation::findOrFail($id);
        $record->update($data);
        return $record->fresh(['selectedPlan', 'sourceComponent']);
    }

    public function delete(string $id): bool
    {
        $record = Quotation::findOrFail($id);
        $record->update(['status' => 'archived']);
        return true;
    }
}