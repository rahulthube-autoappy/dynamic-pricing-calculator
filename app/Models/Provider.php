<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $guarded = [];

    protected $casts = [
        'capabilities'    => 'array',
        'multipliers'     => 'array',
        'metadata'        => 'array',
        'input_rate'      => 'float',
        'output_rate'     => 'float',
        'rate'            => 'float',
        'allow_decimals'  => 'boolean',
        'is_active'       => 'boolean',
    ];
}
