<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProviderRequest;
use App\Http\Requests\UpdateProviderRequest;
use App\Http\Resources\ProviderResource;
use App\Services\ProviderService;
use App\Models\Provider;

class ProviderController extends Controller
{
    protected $service;
    public function __construct(ProviderService $service) { $this->service = $service; }
    public function index() { return ProviderResource::collection($this->service->getAll()); }
    public function store(StoreProviderRequest $request) { return new ProviderResource($this->service->create($request->validated())); }
    public function show($id) { return new ProviderResource($this->service->getById($id)); }
    public function update(UpdateProviderRequest $request, $id) { return new ProviderResource($this->service->update($id, $request->validated())); }
    public function destroy($id) { $this->service->delete($id); return response()->json(['message' => 'Deleted']); }
}
