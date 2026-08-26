<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    protected $guarded = [];

    protected $casts = [
        'available_providers' => 'array',
        'tags' => 'array',
        'metadata' => 'array',
        'is_bundle' => 'boolean',
        'is_leaf' => 'boolean',
        'unit_price' => 'float',
        'quantity' => 'float',
    ];

    public function parent()
    {
        return $this->belongsTo(Component::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Component::class, 'parent_id');
    }
}
