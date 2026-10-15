<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Envio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EnvioController extends Controller
{
    private function ensureStaff(Request $request): void
    {
        $rol = strtolower((string) $request->user()->rol?->nombre);

        abort_unless(
            in_array($rol, ['admin', 'administrador', 'empleado'], true),
            403,
            'No tienes permisos para administrar envíos.'
        );
    }

    // Consultar todos los envíos
    public function index(Request $request): JsonResponse
    {
        $this->ensureStaff($request);

        $envios = Envio::with('pedido.usuario')
            ->orderByDesc('id_envios')
            ->get();

        return response()->json([
            'message' => 'Envíos obtenidos correctamente.',
            'envios' => $envios
        ]);
    }

    // Consultar un envío
    public function show(Request $request, int $envio): JsonResponse
    {
        $this->ensureStaff($request);

        $envioModel = Envio::with('pedido.usuario')
            ->findOrFail($envio);

        return response()->json([
            'message' => 'Envío obtenido correctamente.',
            'envio' => $envioModel
        ]);
    }

    // Actualizar envío
    public function update(Request $request, int $envio): JsonResponse
    {
        $this->ensureStaff($request);

        $envioModel = Envio::findOrFail($envio);

        $data = $request->validate([
            'estado' => [
                'required',
                Rule::in([
                    'pendiente',
                    'preparando',
                    'enviado',
                    'en_transito',
                    'entregado',
                    'cancelado'
                ])
            ],
            'paqueteria' => ['nullable', 'string', 'max:50'],
            'numero_guia' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($envioModel, $data) {
            $estado = $data['estado'];

            $envioModel->update([
                'estado' => $estado,
                'paqueteria' => $data['paqueteria'] ?? null,
                'numero_guia' => $data['numero_guia'] ?? null,
                'fecha_envio' => (
                    $estado === 'enviado' && !$envioModel->fecha_envio
                ) ? now() : $envioModel->fecha_envio,
                'fecha_entrega' => (
                    $estado === 'entregado' && !$envioModel->fecha_entrega
                ) ? now() : $envioModel->fecha_entrega,
            ]);

            if ($estado === 'entregado') {
                $envioModel->pedido()->update([
                    'estado' => 'entregado'
                ]);
            }
        });

        return response()->json([
            'message' => 'Envío actualizado correctamente.',
            'envio' => $envioModel->fresh(['pedido.usuario'])
        ]);
    }
}
