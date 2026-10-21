<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ComprasController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureStaff($request);

        return view('admin.productos.compras', [
            'productos' => Producto::where('status', true)->orderBy('nombre')->get(),
            'compras' => Compra::with(['usuario', 'detalles.producto'])->latest()->limit(20)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureStaff($request);

        $data = $request->validate([
            'numero_compra' => ['nullable', 'string', 'max:30', 'unique:compras,numero_compra'],
            'producto_id' => ['required', 'exists:productos,id_productos'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'costo_unitario' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $data): void {
            $compra = Compra::create([
                'numero_compra' => $data['numero_compra'] ?: 'COM-'.now()->format('YmdHis').'-'.Str::upper(Str::random(3)),
                'user_id' => $request->user()->getKey(),
            ]);
            $compra->detalles()->create([
                'cantidad' => $data['cantidad'],
                'costo_unitario' => $data['costo_unitario'],
                'producto_id' => $data['producto_id'],
            ]);

            MovimientoInventario::create([
                'tipo_movimiento' => 'ENTRADA',
                'cantidad' => $data['cantidad'],
                'created_at' => now(),
                'producto_id' => $data['producto_id'],
                'user_id' => $request->user()->getKey(),
            ]);
        });

        return back()->with('status', 'Compra registrada y stock actualizado correctamente.');
    }

    private function ensureStaff(Request $request): void
    {
        $role = strtolower((string) $request->user()->rol?->nombre);
        abort_unless(in_array($role, ['admin', 'administrador', 'empleado'], true), 403);
    }
}
