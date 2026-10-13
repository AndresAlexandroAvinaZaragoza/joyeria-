<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarritoItem extends Model
{
    protected $table = 'carrito_items';

    protected $primaryKey = 'id_items';

    protected $fillable = [
        'cantidad',
        'producto_id',
        'carrito_id',
    ];

    protected $casts = [
        'cantidad' => 'integer',
    ];

    public function carrito(): BelongsTo
    {
        return $this->belongsTo(Carrito::class, 'carrito_id', 'id_carrito');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'id_productos');
    }
}
