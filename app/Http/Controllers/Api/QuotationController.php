<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Http\Resources\QuotationResource;
use App\Services\QuotationService;
use Illuminate\Http\Request;

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
        // Temporarily use user_id=1 until auth middleware is wired up
        $userId = $request->input('user_id', 1);
        return QuotationResource::collection($this->service->getByUser($userId));
    }

    /** GET /api/quotations/{id} */
    public function show($id)
    {
        return new QuotationResource($this->service->getById($id));
    }

    /** POST /api/quotations */
    public function store(StoreQuotationRequest $request)
    {
        $data = $request->validated();
        // Temporarily default user_id=1 until auth middleware is wired up
        if (empty($data['user_id'])) {
            $data['user_id'] = 1;
        }
        $quotation = $this->service->create($data);
        return (new QuotationResource($quotation))->response()->setStatusCode(201);
    }

    /** PUT|PATCH /api/quotations/{id} */
    public function update(UpdateQuotationRequest $request, $id)
    {
        return new QuotationResource($this->service->update($id, $request->validated()));
    }

    /** DELETE /api/quotations/{id} — archives the quotation */
    public function destroy($id)
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Quotation archived']);
    }

    /**
     * POST /api/quotations/{id}/calculate
     * Recalculate pricing for a quotation from its quotation_nodes tree.
     */
    public function calculate($id)
    {
        $pricing = $this->service->calculatePricing((int) $id);
        return response()->json([
            'success' => true,
            'data'    => $pricing,
        ]);
    }
}
