<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Support\Str;

class OrderRepository
{
    public function getByUser(int $userId)
    {
        return Order::with(['plan', 'quotation'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getById(int $id): Order
    {
        return Order::with([
            'plan',
            'quotation',
            'pricingSnapshots' => fn($q) => $q->whereNull('parent_snapshot_id')->orderBy('depth'),
        ])->findOrFail($id);
    }

    public function findByIdempotencyKey(string $key): ?Order
    {
        return Order::where('idempotency_key', $key)->first();
    }

    public function create(array $data): Order
    {
        $data['uuid'] = $data['uuid'] ?? (string) Str::uuid();
        return Order::create($data);
    }

    public function updateStatus(int $id, string $status): Order
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => $status]);
        return $order->fresh();
    }
}