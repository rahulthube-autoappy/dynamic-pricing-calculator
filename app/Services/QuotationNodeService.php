<?php

namespace App\Services;

use App\Models\QuotationNode;
use App\Repositories\QuotationNodeRepository;

class QuotationNodeService
{
    protected QuotationNodeRepository $repo;

    public function __construct(QuotationNodeRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getRootNodes(string $quotationId)
    {
        return $this->repo->getRootNodes($quotationId);
    }

    public function getById(string $id): QuotationNode
    {
        return $this->repo->getById($id);
    }

    public function create(string $quotationId, array $data): QuotationNode
    {
        $data['quotation_id'] = $quotationId;
        return $this->repo->create($data);
    }

    public function update(string $id, array $data): QuotationNode
    {
        return $this->repo->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->repo->delete($id);
    }

    public function toggleSelection(string $id): QuotationNode
    {
        return $this->repo->toggleSelection($id);
    }
}