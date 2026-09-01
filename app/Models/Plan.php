<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Plan extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'price'      => 'float',
        'max_tasks'  => 'integer',
        'features'   => 'array',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];
}