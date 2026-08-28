<?php

namespace App\Repositories;

use App\Models\QuotationNode;
use Illuminate\Support\Str;

class QuotationNodeRepository
{
    public function getRootNodes(int $quotationId)
    {
        return QuotationNode::with('allChildren.selectedProvider')
            ->where('quotation_id', $quotationId)
            ->whereNull('parent_node_id')
            ->orderBy('sort_order')
            ->get();
    }

    public function getById(int $id): QuotationNode
    {
        return QuotationNode::with('allChildren', 'selectedProvider', 'pricingCategory')->findOrFail($id);
    }

    public function create(array $data): QuotationNode
    {
        $data['uuid'] = $data['uuid'] ?? (string) Str::uuid();
        return QuotationNode::create($data);
    }

    public function update(int $id, array $data): QuotationNode
    {
        $record = QuotationNode::findOrFail($id);
        $record->update($data);
        return $record->fresh(['allChildren', 'selectedProvider', 'pricingCategory']);
    }

    public function delete(int $id): bool
    {
        $record = QuotationNode::findOrFail($id);
        return $record->delete(); // cascades to children via DB FK
    }

    public function toggleSelection(int $id): QuotationNode
    {
        $record = QuotationNode::findOrFail($id);
        $record->update(['is_selected' => !$record->is_selected]);
        return $record->fresh();
    }
}
