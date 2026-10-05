<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DireccionCliente extends Model
{
    protected $table = 'direccion_clientes';

    protected $primaryKey = 'id_direccion_clientes';

    protected $fillable = [
        'calle_numero',
        'colonia',
        'ciudad',
        'estado',
        'cp',
        'referencias',
        'predeterminada',
        'user_id',
    ];

    protected $casts = [
        'predeterminada' => 'boolean',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
