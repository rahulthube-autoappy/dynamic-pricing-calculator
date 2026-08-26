<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Http\Resources\PlanResource;
use App\Services\PlanService;
use App\Models\Plan;

class PlanController extends Controller
{
    protected $service;
    public function __construct(PlanService $service) { $this->service = $service; }
    public function index() { return PlanResource::collection($this->service->getAll()); }
    public function store(StorePlanRequest $request) { return new PlanResource($this->service->create($request->validated())); }
    public function show($id) { return new PlanResource($this->service->getById($id)); }
    public function update(UpdatePlanRequest $request, $id) { return new PlanResource($this->service->update($id, $request->validated())); }
    public function destroy($id) { $this->service->delete($id); return response()->json(['message' => 'Deleted']); }
}
