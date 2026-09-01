<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'user_id'                 => $this->user_id,
            'quotation_id'            => $this->quotation_id,
            'plan_id'                 => $this->plan_id,
            'status'                  => $this->status,
            'currency'                => $this->currency,
            'subtotal'                => (float) $this->subtotal,
            'expert_fee_total'        => (float) $this->expert_fee_total,
            'one_time_total'          => (float) $this->one_time_total,
            'recurring_monthly_total' => (float) $this->recurring_monthly_total,
            'plan_price'              => (float) $this->plan_price,
            'discount_total'          => (float) $this->discount_total,
            'tax_total'               => (float) $this->tax_total,
            'grand_total'             => (float) $this->grand_total,
            'idempotency_key'         => $this->idempotency_key,
            'notes'                   => $this->notes,
            'plan'                    => $this->whenLoaded('plan', fn() => $this->plan ? [
                'id'    => $this->plan->id,
                'name'  => $this->plan->name,
                'code'  => $this->plan->code,
                'price' => (float) $this->plan->price,
            ] : null),
            'quotation'               => $this->whenLoaded('quotation', fn() => $this->quotation ? [
                'id'    => $this->quotation->id,
                'title' => $this->quotation->title,
                'type'  => $this->quotation->type,
            ] : null),
            'pricing_snapshots'       => PricingSnapshotResource::collection($this->whenLoaded('pricingSnapshots')),
            'created_at'              => $this->created_at,
            'updated_at'              => $this->updated_at,
        ];
    }
}