<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComponentRequest;
use App\Http\Requests\UpdateComponentRequest;
use App\Http\Resources\ComponentResource;
use App\Services\ComponentService;

class ComponentController extends Controller
{
    protected ComponentService $service;

    public function __construct(ComponentService $service)
    {
        $this->service = $service;
    }

    /** GET /api/components — master library list */
    public function index()
    {
        return ComponentResource::collection($this->service->getAll());
    }

    /** GET /api/components/bundles — automation bundles (is_bundle=true) */
    public function bundles()
    {
        return ComponentResource::collection($this->service->getBundles());
    }

    /** GET /api/components/{id} — single component with full tree */
    public function show($id)
    {
        return new ComponentResource($this->service->getById($id));
    }

    /** POST /api/components */
    public function store(StoreComponentRequest $request)
    {
        $component = $this->service->create($request->validated());
        return (new ComponentResource($component))->response()->setStatusCode(201);
    }

    /** PUT|PATCH /api/components/{id} */
    public function update(UpdateComponentRequest $request, $id)
    {
        return new ComponentResource($this->service->update($id, $request->validated()));
    }

    /** DELETE /api/components/{id} */
    public function destroy($id)
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Deleted'], 200);
    }

    /**
     * GET /api/components/bundle/{id}/estimate
     * Returns the bundle tree with estimated pricing from the component library.
     */
    public function getBundleEstimate($id)
    {
        $treeData = $this->service->getBundleTree($id);

        return response()->json([
            'success' => true,
            'data'    => $treeData,
        ]);
    }
}
