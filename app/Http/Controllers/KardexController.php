<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KardexController extends Controller
{
    public function index(Request $request): View
    {
        $role = strtolower((string) $request->user()->rol?->nombre);
        abort_unless(in_array($role, ['admin', 'administrador', 'empleado'], true), 403);

        return view('admin.productos.kardex', [
            'movimientos' => MovimientoInventario::with(['producto', 'usuario'])
                ->when($request->filled('tipo'), fn ($query) => $query->where('tipo_movimiento', $request->input('tipo')))
                ->latest('created_at')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }
}
