<?php

namespace App\Repositories;

use App\Models\PricingSnapshot;

class PricingSnapshotRepository
{
    /**
     * Bulk insert a flat list of snapshot rows (insert-only, no update).
     */
    public function bulkInsert(array $rows): void
    {
        $now = now()->toDateTimeString();
        foreach ($rows as &$row) {
            $row['created_at'] = $row['created_at'] ?? $now;
            if (isset($row['selected_dimensions']) && is_array($row['selected_dimensions'])) {
                $row['selected_dimensions'] = json_encode($row['selected_dimensions']);
            }
        }
        PricingSnapshot::insert($rows);
    }

    public function getByOrder(int $orderId)
    {
        return PricingSnapshot::where('order_id', $orderId)
            ->whereNull('parent_snapshot_id')
            ->orderBy('depth')
            ->get()
            ->each(fn($s) => $s->setRelation('children', $this->loadChildren($s->id)));
    }

    protected function loadChildren(int $parentId)
    {
        return PricingSnapshot::where('parent_snapshot_id', $parentId)
            ->orderBy('depth')
            ->get()
            ->each(fn($s) => $s->setRelation('children', $this->loadChildren($s->id)));
    }
}