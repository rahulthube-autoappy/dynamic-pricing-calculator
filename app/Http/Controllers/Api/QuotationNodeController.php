<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuotationNodeRequest;
use App\Http\Requests\UpdateQuotationNodeRequest;
use App\Http\Resources\QuotationNodeResource;
use App\Services\QuotationNodeService;

class QuotationNodeController extends Controller
{
    protected QuotationNodeService $service;

    public function __construct(QuotationNodeService $service)
    {
        $this->service = $service;
    }

    /** GET /api/quotations/{quotationId}/nodes */
    public function index($quotationId)
    {
        $nodes = $this->service->getRootNodes((string) $quotationId);
        return QuotationNodeResource::collection($nodes);
    }

    /** POST /api/quotations/{quotationId}/nodes */
    public function store(StoreQuotationNodeRequest $request, $quotationId)
    {
        $node = $this->service->create((string) $quotationId, $request->validated());
        return (new QuotationNodeResource($node))->response()->setStatusCode(201);
    }

    /** PUT|PATCH /api/quotations/{quotationId}/nodes/{nodeId} */
    public function update(UpdateQuotationNodeRequest $request, $quotationId, $nodeId)
    {
        return new QuotationNodeResource($this->service->update((string) $nodeId, $request->validated()));
    }

    /** DELETE /api/quotations/{quotationId}/nodes/{nodeId} */
    public function destroy($quotationId, $nodeId)
    {
        $this->service->delete((string) $nodeId);
        return response()->json(['message' => 'Node deleted']);
    }

    /** PATCH /api/quotations/{quotationId}/nodes/{nodeId}/toggle */
    public function toggleSelection($quotationId, $nodeId)
    {
        $node = $this->service->toggleSelection((string) $nodeId);
        return new QuotationNodeResource($node);
    }
}