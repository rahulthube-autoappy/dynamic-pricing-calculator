<?php

namespace App\Repositories;

use App\Models\QuotationNode;

class QuotationNodeRepository
{
    public function getRootNodes(string $quotationId)
    {
        $all = QuotationNode::with('selectedProvider', 'pricingCategory')
            ->where('quotation_id', $quotationId)
            ->orderBy('sort_order')
            ->get();

        $this->linkChildren($all);

        return $all->whereNull('parent_node_id')->values();
    }

    public function getById(string $id): QuotationNode
    {
        $node = QuotationNode::findOrFail($id);
        
        $all = QuotationNode::with('selectedProvider', 'pricingCategory')
            ->where('quotation_id', $node->quotation_id)
            ->orderBy('sort_order')
            ->get();

        $this->linkChildren($all);

        return $all->firstWhere('id', $id) ?? $node;
    }

    public function create(array $data): QuotationNode
    {
        return QuotationNode::create($data);
    }

    public function update(string $id, array $data): QuotationNode
    {
        $record = QuotationNode::findOrFail($id);
        $record->update($data);
        return $this->getById($id);
    }

    public function delete(string $id): bool
    {
        $record = QuotationNode::findOrFail($id);
        return $record->delete();
    }

    public function toggleSelection(string $id): QuotationNode
    {
        $record = QuotationNode::findOrFail($id);
        $record->update(['is_selected' => !$record->is_selected]);
        return $record->fresh();
    }

    protected function linkChildren($collection)
    {
        foreach ($collection as $item) {
            $children = $collection->filter(function ($c) use ($item) {
                return $c->parent_node_id === $item->id;
            })->values();
            $item->setRelation('children', $children);
        }
    }
}