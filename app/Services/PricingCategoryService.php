<?php

namespace App\Services;

use App\Repositories\PricingCategoryRepository;

class PricingCategoryService
{
    protected $repo;
    public function __construct(PricingCategoryRepository $repo) { $this->repo = $repo; }
    public function getAll() { return $this->repo->getAll(); }
    public function getById($id) { return $this->repo->getById($id); }
    public function create(array $data) { return $this->repo->create($data); }
    public function update($id, array $data) { return $this->repo->update($id, $data); }
    public function delete($id) { return $this->repo->delete($id); }
}
