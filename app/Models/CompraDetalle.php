<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompraDetalle extends Model
{
    protected $table = 'compra_detalles';

    protected $primaryKey = 'id_compra_detalle';

    protected $fillable = ['cantidad', 'costo_unitario', 'compra_id', 'producto_id'];

    protected $casts = ['cantidad' => 'integer', 'costo_unitario' => 'decimal:2'];

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id', 'id_compra');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'id_productos');
    }
}
