<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Envio extends Model
{
    protected $table = 'envios';

    protected $primaryKey = 'id_envios';

    public $timestamps = false;

    protected $fillable = [
        'direccion_destino', 'paqueteria', 'numero_guia', 'estado',
        'fecha_envio', 'fecha_entrega', 'pedido_id',
    ];

    protected $casts = [
        'fecha_envio' => 'datetime',
        'fecha_entrega' => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id', 'id_pedidos');
    }
}
