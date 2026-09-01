<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'user_id'             => $this->user_id,
            'type'                => $this->type,
            'title'               => $this->title,
            'status'              => $this->status,
            'requires_expert'     => $this->requires_expert,
            'expert_notes'        => $this->expert_notes,
            'notes'               => $this->notes,
            'idempotency_key'     => $this->idempotency_key,
            'selected_plan'       => $this->whenLoaded('selectedPlan', fn() => $this->selectedPlan ? [
                'id'    => $this->selectedPlan->id,
                'name'  => $this->selectedPlan->name,
                'code'  => $this->selectedPlan->code,
                'price' => $this->selectedPlan->price,
            ] : null),
            'source_component'    => $this->whenLoaded('sourceComponent', fn() => $this->sourceComponent ? [
                'id'   => $this->sourceComponent->id,
                'name' => $this->sourceComponent->name,
            ] : null),
            'nodes'               => QuotationNodeResource::collection($this->whenLoaded('rootNodes')),
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}