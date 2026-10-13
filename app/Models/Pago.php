<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $primaryKey = 'id_pagos';

    public $timestamps = false;

    protected $fillable = [
        'metodo_pago', 'transaccion_id', 'monto', 'estado',
        'comprobante_url', 'fecha_pago', 'pedido_id',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id', 'id_pedidos');
    }
}
