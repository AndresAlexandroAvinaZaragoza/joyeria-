<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $primaryKey = 'id_pedidos';

    protected $fillable = [
        'numero_pedido', 'subtotal', 'costo_envio', 'total', 'estado', 'user_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'costo_envio' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(PedidoDetalle::class, 'pedido_id', 'id_pedidos');
    }

    public function pago(): HasOne
    {
        return $this->hasOne(Pago::class, 'pedido_id', 'id_pedidos');
    }

    public function envio(): HasOne
    {
        return $this->hasOne(Envio::class, 'pedido_id', 'id_pedidos');
    }
}
