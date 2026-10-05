<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto_img extends Model
{
    protected $table = 'producto_img';

    protected $primaryKey = 'id_img';

    public const UPDATED_AT = null;

    protected $fillable = [
        'producto_id',
        'url_img',
        'es_principal',
        'orden',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'orden' => 'integer',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'id_productos');
    }
}
