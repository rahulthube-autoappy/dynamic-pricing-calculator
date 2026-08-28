<?php

namespace App\Repositories;

use App\Models\Quotation;
use Illuminate\Support\Str;

class QuotationRepository
{
    public function getByUser(int $userId)
    {
        return Quotation::with(['plan', 'sourceComponent'])
            ->where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->get();
    }

    public function getById(int $id): Quotation
    {
        return Quotation::with(['plan', 'sourceComponent', 'rootNodes.allChildren.selectedProvider'])->findOrFail($id);
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
        $data['uuid'] = $data['uuid'] ?? (string) Str::uuid();
        return Quotation::create($data);
    }

    public function update(int $id, array $data): Quotation
    {
        $record = Quotation::findOrFail($id);
        $record->update($data);
        return $record->fresh(['plan', 'sourceComponent']);
    }

    public function delete(int $id): bool
    {
        $record = Quotation::findOrFail($id);
        $record->update(['status' => 'archived']);
        return true;
    }
}
