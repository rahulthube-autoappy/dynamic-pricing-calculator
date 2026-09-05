<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuotationNodeRequest;
use App\Http\Requests\UpdateQuotationNodeRequest;
use App\Http\Resources\QuotationNodeResource;
use App\Services\QuotationNodeService;
use Illuminate\Support\Facades\Log;

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
        Log::info("QuotationNode: Fetching nodes tree", ['quotation_id' => $quotationId]);
        $nodes = $this->service->getRootNodes((string) $quotationId);
        return QuotationNodeResource::collection($nodes);
    }

    /** POST /api/quotations/{quotationId}/nodes */
    public function store(StoreQuotationNodeRequest $request, $quotationId)
    {
        $validated = $request->validated();
        Log::info("QuotationNode: Adding custom node", [
            'quotation_id'   => $quotationId,
            'name'           => $validated['name'] ?? null,
            'parent_node_id' => $validated['parent_node_id'] ?? null,
        ]);
        $node = $this->service->create((string) $quotationId, $validated);
        return (new QuotationNodeResource($node))->response()->setStatusCode(201);
    }

    /** PUT|PATCH /api/quotations/{quotationId}/nodes/{nodeId} */
    public function update(UpdateQuotationNodeRequest $request, $quotationId, $nodeId)
    {
        $validated = $request->validated();
        Log::info("QuotationNode: Updating node", [
            'quotation_id' => $quotationId,
            'node_id'      => $nodeId,
            'fields'       => array_keys($validated),
        ]);
        return new QuotationNodeResource($this->service->update((string) $nodeId, $validated));
    }

    /** DELETE /api/quotations/{quotationId}/nodes/{nodeId} */
    public function destroy($quotationId, $nodeId)
    {
        Log::info("QuotationNode: Deleting node", [
            'quotation_id' => $quotationId,
            'node_id'      => $nodeId,
        ]);
        $this->service->delete((string) $nodeId);
        return response()->json(['message' => 'Node deleted']);
    }

    /** PATCH /api/quotations/{quotationId}/nodes/{nodeId}/toggle */
    public function toggleSelection($quotationId, $nodeId)
    {
        Log::info("QuotationNode: Toggling node selection", [
            'quotation_id' => $quotationId,
            'node_id'      => $nodeId,
        ]);
        $node = $this->service->toggleSelection((string) $nodeId);
        return new QuotationNodeResource($node);
    }

    /** GET /api/quotations/{quotationId}/nodes/estimate */
    public function estimate($quotationId)
    {
        Log::info("QuotationNode: Fetching quotation nodes estimate tree", ['quotation_id' => $quotationId]);
        $tree = app(\App\Services\QuotationService::class)->getQuotationTree((string) $quotationId);

        return response()->json([
            'success' => true,
            'data'    => $tree,
        ]);
    }
}