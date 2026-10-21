<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use App\Models\Pedido;
use App\Services\JoyeriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PedidosController extends Controller
{
    public function index(Request $request): View
    {
        return view('client.pedidos', [
            'pedidos' => $request->user()->pedidos()->with(['detalles.producto', 'envio', 'pago'])->latest()->get(),
        ]);
    }

    public function adminIndex(Request $request): View
    {
        $this->ensureStaff($request);

        return view('admin.productos.pedidos', [
            'pedidos' => Pedido::with(['usuario', 'detalles.producto', 'envio', 'pago'])->latest()->get(),
        ]);
    }

    public function shipments(Request $request): View
    {
        $this->ensureStaff($request);

        return view('admin.productos.envios', [
            'envios' => Envio::with('pedido.usuario')->latest()->get(),
        ]);
    }

    public function updateShipment(Request $request, Envio $envio): RedirectResponse
    {
        $this->ensureStaff($request);

        $data = $request->validate([
            'estado' => ['required', 'in:pendiente,preparando,enviado,en_transito,entregado,cancelado'],
            'paqueteria' => ['nullable', 'string', 'max:50'],
            'numero_guia' => ['nullable', 'string', 'max:100'],
        ]);

        $envio->update($data + [
            'fecha_envio' => $data['estado'] === 'enviado' && ! $envio->fecha_envio ? now() : $envio->fecha_envio,
            'fecha_entrega' => $data['estado'] === 'entregado' && ! $envio->fecha_entrega ? now() : $envio->fecha_entrega,
        ]);

        if ($data['estado'] === 'entregado') {
            $envio->pedido()->update(['estado' => 'entregado']);
        }

        return back()->with('status', 'El envío se actualizó correctamente.');
    }

    public function cancel(Request $request, Pedido $pedido, JoyeriaService $service): RedirectResponse
    {
        $this->ensureStaff($request);
        $service->cancelOrder($pedido, $request->user()->getKey());

        return back()->with('status', 'El pedido fue cancelado y el inventario fue restaurado.');
    }

    private function ensureStaff(Request $request): void
    {
        $role = strtolower((string) $request->user()->rol?->nombre);
        abort_unless(in_array($role, ['admin', 'administrador', 'empleado'], true), 403);
    }
}
