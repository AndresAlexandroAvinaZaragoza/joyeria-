<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $primaryKey = 'id_categorias';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'status',
    ];
}
