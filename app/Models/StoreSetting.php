<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = ['shipping_cost'];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
    ];
}
