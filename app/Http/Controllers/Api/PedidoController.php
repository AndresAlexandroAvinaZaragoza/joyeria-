<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    private function ensureStaff(Request $request): void
    {
        $rol = strtolower(
            (string) $request->user()->rol?->nombre
        );

        abort_unless(
            in_array($rol, ['admin', 'administrador', 'empleado'], true),
            403,
            'No tienes permisos para consultar pedidos.'
        );
    }

    public function index(Request $request): JsonResponse
    {
        $this->ensureStaff($request);

        $pedidos = Pedido::with([
            'usuario',
            'detalles.producto',
            'pago',
            'envio'
        ])
        ->latest()
        ->get();

        return response()->json([
            'message' => 'Pedidos obtenidos correctamente.',
            'pedidos' => $pedidos
        ]);
    }

    public function show(Request $request, int $pedido): JsonResponse
    {
        $this->ensureStaff($request);

        $pedidoModel = Pedido::with([
            'usuario',
            'detalles.producto',
            'pago',
            'envio'
        ])->findOrFail($pedido);

        return response()->json([
            'message' => 'Detalle del pedido obtenido correctamente.',
            'pedido' => $pedidoModel
        ]);
    }
}
