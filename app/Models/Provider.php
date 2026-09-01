<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Provider extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'capabilities'        => 'array',
        'billing_granularity' => 'integer',
        'allow_decimals'      => 'boolean',
        'input_rate'          => 'float',
        'output_rate'         => 'float',
        'rate'                => 'float',
        'multipliers'         => 'array',
        'is_active'           => 'boolean',
        'metadata'            => 'array',
    ];
}