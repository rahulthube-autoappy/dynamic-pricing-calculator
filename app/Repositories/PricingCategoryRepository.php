<?php

namespace App\Repositories;

use App\Models\PricingCategory;

class PricingCategoryRepository
{
    public function getAll() { return PricingCategory::all(); }
    public function getById($id) { return PricingCategory::findOrFail($id); }
    public function create(array $data) { return PricingCategory::create($data); }
    public function update($id, array $data) { $record = $this->getById($id); $record->update($data); return $record; }
    public function delete($id) { $record = $this->getById($id); return $record->delete(); }
}
