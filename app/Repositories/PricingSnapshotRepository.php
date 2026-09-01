<?php

namespace App\Repositories;

use App\Models\PricingSnapshot;
use Illuminate\Support\Str;

class PricingSnapshotRepository
{
    public function bulkInsert(array $rows): void
    {
        $now = now()->toDateTimeString();
        foreach ($rows as &$row) {
            $row['id'] = $row['id'] ?? (string) Str::uuid();
            $row['created_at'] = $row['created_at'] ?? $now;
            if (isset($row['selected_dimensions']) && is_array($row['selected_dimensions'])) {
                $row['selected_dimensions'] = json_encode($row['selected_dimensions']);
            }
        }
        PricingSnapshot::insert($rows);
    }

    public function getByOrder(string $orderId)
    {
        return PricingSnapshot::where('order_id', $orderId)
            ->whereNull('parent_snapshot_id')
            ->orderBy('depth')
            ->get()
            ->each(fn($s) => $s->setRelation('children', $this->loadChildren($s->id)));
    }

    protected function loadChildren(string $parentId)
    {
        return PricingSnapshot::where('parent_snapshot_id', $parentId)
            ->orderBy('depth')
            ->get()
            ->each(fn($s) => $s->setRelation('children', $this->loadChildren($s->id)));
    }
}