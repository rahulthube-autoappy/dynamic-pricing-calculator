<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComponentRequest;
use App\Http\Requests\UpdateComponentRequest;
use App\Http\Resources\ComponentResource;
use App\Services\ComponentService;
use Illuminate\Support\Facades\Log;

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
        Log::info("Component: Listing all components");
        return ComponentResource::collection($this->service->getAll());
    }

    /** GET /api/components/bundles — automation bundles (is_bundle=true) */
    public function bundles()
    {
        Log::info("Component: Fetching active catalog bundles");
        return ComponentResource::collection($this->service->getBundles());
    }

    /** GET /api/components/groups — depth-1 component modules */
    public function groups()
    {
        Log::info("Component: Fetching depth-1 component modules");
        return response()->json([
            'success' => true,
            'data'    => $this->service->getGroups(),
        ]);
    }

    /** GET /api/components/subcomponents — all depth-2 subcomponents (leaves + sub-groups) */
    public function subcomponents()
    {
        Log::info("Component: Fetching subcomponents list");
        return response()->json([
            'success' => true,
            'data'    => $this->service->getSubcomponents(),
        ]);
    }

    /** GET /api/components/leaves — backwards compatible alias for subcomponents */
    public function leaves()
    {
        return $this->subcomponents();
    }

    /** GET /api/components/{id} — single component with full tree */
    public function show($id)
    {
        Log::info("Component: Fetching component details", ['component_id' => $id]);
        return new ComponentResource($this->service->getById($id));
    }

    /** POST /api/components */
    public function store(StoreComponentRequest $request)
    {
        $validated = $request->validated();
        Log::info("Component: Creating new component", [
            'name'      => $validated['name'] ?? null,
            'is_bundle' => $validated['is_bundle'] ?? false,
            'is_leaf'   => $validated['is_leaf'] ?? false,
        ]);
        $component = $this->service->create($validated);
        Log::info("Component: Component created successfully", ['component_id' => $component->id]);
        return (new ComponentResource($component))->response()->setStatusCode(201);
    }

    /** PUT|PATCH /api/components/{id} */
    public function update(UpdateComponentRequest $request, $id)
    {
        Log::info("Component: Updating component", ['component_id' => $id]);
        return new ComponentResource($this->service->update($id, $request->validated()));
    }

    /** DELETE /api/components/{id} */
    public function destroy($id)
    {
        Log::info("Component: Deleting component", ['component_id' => $id]);
        $this->service->delete($id);
        return response()->json(['message' => 'Deleted'], 200);
    }

    /**
     * GET /api/components/{id}/estimate or GET /api/components/bundle/{id}/estimate
     * Returns the complete tree with estimated pricing for any bundle, group, or leaf.
     */
    public function getComponentEstimate($id)
    {
        Log::info("Component: Fetching component estimate tree", ['component_id' => $id]);
        $treeData = $this->service->getComponentTree($id);

        return response()->json([
            'success' => true,
            'data'    => $treeData,
        ]);
    }

    /**
     * Backwards compatibility alias for getComponentEstimate
     */
    public function getBundleEstimate($id)
    {
        return $this->getComponentEstimate($id);
    }
}
