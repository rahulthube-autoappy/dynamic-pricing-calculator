<?php

namespace App\Repositories;

use App\Models\Provider;

class ProviderRepository
{
    public function getAll() { return Provider::all(); }
    public function getById($id) { return Provider::findOrFail($id); }
    public function create(array $data) { return Provider::create($data); }
    public function update($id, array $data) { $record = $this->getById($id); $record->update($data); return $record; }
    public function delete($id) { $record = $this->getById($id); return $record->delete(); }
    public function getAllActiveKeyedById() { return Provider::where('is_active', true)->get()->keyBy('id'); }
}
