<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Http\Resources\QuotationResource;
use App\Services\QuotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuotationController extends Controller
{
    protected QuotationService $service;

    public function __construct(QuotationService $service)
    {
        $this->service = $service;
    }

    /** GET /api/quotations — list all quotations for the user */
    public function index(Request $request)
    {
        $userId = $request->input('user_id', 1);
        $status = $request->input('status');
        $includeArchived = $request->boolean('include_archived', false);

        Log::info("Quotation: Listing quotations for user", [
            'user_id'          => $userId,
            'status'           => $status,
            'include_archived' => $includeArchived,
        ]);

        return QuotationResource::collection(
            $this->service->getByUser((int) $userId, $status, $includeArchived)
        );
    }

    /** GET /api/quotations/{id} */
    public function show($id)
    {
        Log::info("Quotation: Fetching quotation details", ['quotation_id' => $id]);
        return new QuotationResource($this->service->getById((string) $id));
    }

    /** POST /api/quotations */
    public function store(StoreQuotationRequest $request)
    {
        $data = $request->validated();
        if (empty($data['user_id'])) {
            $data['user_id'] = 1;
        }
        Log::info("Quotation: Creating new quotation", [
            'type'                => $data['type'] ?? 'cart',
            'source_component_id' => $data['source_component_id'] ?? null,
            'user_id'             => $data['user_id'],
        ]);
        $quotation = $this->service->create($data);
        Log::info("Quotation: Created quotation successfully", ['quotation_id' => $quotation->id]);
        return (new QuotationResource($quotation))->response()->setStatusCode(201);
    }

    /** PUT|PATCH /api/quotations/{id} */
    public function update(UpdateQuotationRequest $request, $id)
    {
        Log::info("Quotation: Updating quotation", [
            'quotation_id' => $id,
            'fields'       => array_keys($request->validated()),
        ]);
        return new QuotationResource($this->service->update((string) $id, $request->validated()));
    }

    /** DELETE /api/quotations/{id} — archives the quotation */
    public function destroy($id)
    {
        Log::info("Quotation: Archiving quotation", ['quotation_id' => $id]);
        $this->service->delete((string) $id);
        return response()->json(['message' => 'Quotation archived']);
    }

    /**
     * POST /api/quotations/{id}/calculate
     * Recalculate pricing for a quotation from its quotation_nodes tree.
     */
    public function calculate($id)
    {
        Log::info("Quotation: Calculating dynamic pricing", ['quotation_id' => $id]);
        $pricing = $this->service->calculatePricing((string) $id);
        Log::info("Quotation: Calculation complete", [
            'quotation_id' => $id,
            'subtotal'     => $pricing['subtotal'] ?? null,
            'grand_total'  => $pricing['grand_total'] ?? null,
        ]);
        return response()->json([
            'success' => true,
            'data'    => $pricing,
        ]);
    }

    /**
     * GET /api/quotations/{id}/estimate
     * Returns the full quotation nodes tree with calculated/estimated prices, providers, and cycle metadata.
     */
    public function estimate($id)
    {
        Log::info("Quotation: Fetching quotation estimate tree", ['quotation_id' => $id]);
        $tree = $this->service->getQuotationTree((string) $id);

        return response()->json([
            'success' => true,
            'data'    => $tree,
        ]);
    }
}