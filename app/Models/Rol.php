<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'rol';

    protected $primaryKey = 'id_rol';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function isProtected(): bool
    {
        $nombre = mb_strtoupper(trim($this->nombre));

        return in_array($nombre, ['CLIENTE', 'ADMINISTRADOR'], true);
    }

    public function isAdministrator(): bool
    {
        $nombre = mb_strtoupper(trim($this->nombre));

        return in_array($nombre, ['ADMIN', 'ADMINISTRADOR'], true);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id', 'id_rol');
    }
}
