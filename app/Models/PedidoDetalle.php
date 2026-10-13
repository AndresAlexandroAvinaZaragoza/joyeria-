<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoDetalle extends Model
{
    protected $table = 'pedido_detalles';

    protected $primaryKey = 'id_pedido_detalle';

    protected $fillable = [
        'precio_unitario', 'cantidad', 'importe', 'pedido_id', 'producto_id',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'cantidad' => 'integer',
        'importe' => 'decimal:2',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id', 'id_pedidos');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'id_productos');
    }
}
