<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Producto extends Model
{
    protected $table = 'productos';

    protected $primaryKey = 'id_productos';

    protected $fillable = [
        'sku',
        'nombre',
        'descripcion',
        'material',
        'peso_gr',
        'talla_medida',
        'precio_costo',
        'precio_venta',
        'status',
        'id_categoria',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categorias');
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(ProductoImg::class, 'producto_id', 'id_productos');
    }

    public function inventario(): HasOne
    {
        return $this->hasOne(Inventario::class, 'producto_id', 'id_productos');
    }
}
