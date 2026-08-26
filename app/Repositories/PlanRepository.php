<?php

namespace App\Repositories;

use App\Models\Plan;

class PlanRepository
{
    public function getAll() { return Plan::all(); }
    public function getById($id) { return Plan::findOrFail($id); }
    public function create(array $data) { return Plan::create($data); }
    public function update($id, array $data) { $record = $this->getById($id); $record->update($data); return $record; }
    public function delete($id) { $record = $this->getById($id); return $record->delete(); }
    public function getPlanFeeByCode(?string $code): float { if(empty($code)) return 0.0; $plan = Plan::where('code', $code)->first(); if($plan) return (float)$plan->price; if($code==='starter') return 499.0; return 0.0; }
}
