<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePricingCategoryRequest;
use App\Http\Requests\UpdatePricingCategoryRequest;
use App\Http\Resources\PricingCategoryResource;
use App\Services\PricingCategoryService;
use App\Models\PricingCategory;

class PricingCategoryController extends Controller
{
    protected $service;
    public function __construct(PricingCategoryService $service) { $this->service = $service; }
    public function index() { return PricingCategoryResource::collection($this->service->getAll()); }
    public function store(StorePricingCategoryRequest $request) { return new PricingCategoryResource($this->service->create($request->validated())); }
    public function show($id) { return new PricingCategoryResource($this->service->getById($id)); }
    public function update(UpdatePricingCategoryRequest $request, $id) { return new PricingCategoryResource($this->service->update($id, $request->validated())); }
    public function destroy($id) { $this->service->delete($id); return response()->json(['message' => 'Deleted']); }
}
