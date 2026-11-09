<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use App\Models\Pedido;
use App\Models\PedidoHistorial;
use App\Services\JoyeriaService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PedidosController extends Controller
{
    public function index(Request $request): View
    {
        return view('client.pedidos', [
            'pedidos' => $request->user()->pedidos()->with(['detalles.producto.imagenes', 'envio', 'pago'])->latest()->get(),
        ]);
    }

    public function adminIndex(Request $request): View
    {
        $this->ensureStaff($request);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'period' => ['nullable', 'in:today,yesterday,this_week,last_month'],
        ]);

        $pedidosQuery = Pedido::whereNotIn('estado', ['cancelado', 'entregado'])
            ->with(['usuario', 'detalles.producto.imagenes', 'envio', 'pago']);

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $pedidosQuery->where(function ($query) use ($search) {
                $query->where('numero_pedido', 'like', "%{$search}%")
                    ->orWhere('estado', 'like', "%{$search}%")
                    ->orWhereHas('usuario', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $today = CarbonImmutable::now();
        [$from, $to] = match ($filters['period'] ?? null) {
            'today' => [$today->startOfDay(), $today->endOfDay()],
            'yesterday' => [$today->subDay()->startOfDay(), $today->subDay()->endOfDay()],
            'this_week' => [$today->startOfWeek(), $today->endOfWeek()],
            'last_month' => [$today->subMonth()->startOfMonth(), $today->subMonth()->endOfMonth()],
            default => [null, null],
        };

        if ($from && $to) {
            $pedidosQuery->whereBetween('created_at', [$from, $to]);
        }

        return view('admin.productos.pedidos', [
            'pedidos' => $pedidosQuery->latest()->get(),
            'period' => $filters['period'] ?? null,
        ]);
    }

    public function history(Request $request): View
    {
        $this->ensureStaff($request);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'period' => ['nullable', 'in:today,yesterday,this_week,last_month'],
        ]);

        $historialQuery = PedidoHistorial::with([
            'pedido.usuario',
            'pedido.detalles.producto.imagenes',
            'pedido.envio',
            'pedido.pago',
            'usuario',
        ]);

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $historialQuery->where(function ($query) use ($search) {
                $query->where('estado', 'like', "%{$search}%")
                    ->orWhere('motivo', 'like', "%{$search}%")
                    ->orWhereHas('pedido', function ($pedidoQuery) use ($search) {
                        $pedidoQuery->where('numero_pedido', 'like', "%{$search}%")
                            ->orWhereHas('usuario', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('usuario', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
            });
        }

        [$from, $to] = $this->periodBounds($filters['period'] ?? null);
        if ($from && $to) {
            $historialQuery->whereBetween('created_at', [$from, $to]);
        }

        return view('admin.productos.historial-pedidos', [
            'historial' => $historialQuery->latest()->get(),
            'period' => $filters['period'] ?? null,
        ]);
    }

    public function print(Request $request, Pedido $pedido): View
    {
        $this->ensureStaff($request);
        $pedido->load(['usuario', 'detalles.producto.imagenes', 'envio', 'pago']);

        return view('admin.productos.pedido-print', compact('pedido'));
    }

    public function shipments(Request $request): View
    {
        $this->ensureStaff($request);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'period' => ['nullable', 'in:today,yesterday,this_week,last_month'],
        ]);

        $enviosQuery = Envio::whereHas('pedido', fn ($query) => $query->whereNotIn('estado', ['cancelado', 'entregado']))
            ->with('pedido.usuario');

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $enviosQuery->where(function ($query) use ($search) {
                $query->where('paqueteria', 'like', "%{$search}%")
                    ->orWhere('numero_guia', 'like', "%{$search}%")
                    ->orWhere('estado', 'like', "%{$search}%")
                    ->orWhereHas('pedido', function ($pedidoQuery) use ($search) {
                        $pedidoQuery->where('numero_pedido', 'like', "%{$search}%")
                            ->orWhereHas('usuario', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                    });
            });
        }

        [$from, $to] = $this->periodBounds($filters['period'] ?? null);
        if ($from && $to) {
            $enviosQuery->whereBetween('created_at', [$from, $to]);
        }

        return view('admin.productos.envios', [
            'envios' => $enviosQuery->latest()->get(),
            'period' => $filters['period'] ?? null,
        ]);
    }

    public function updateShipment(Request $request, Envio $envio): RedirectResponse
    {
        $this->ensureStaff($request);

        $data = $request->validate([
            'estado' => ['required', 'in:pendiente,preparando,enviado,en_transito,entregado'],
            'paqueteria' => ['nullable', 'string', 'max:50'],
            'numero_guia' => ['nullable', 'string', 'max:100'],
        ]);

        abort_if(
            in_array($envio->pedido->estado, ['cancelado', 'entregado'], true),
            422,
            'Este pedido ya está en el historial y no se puede modificar.'
        );

        $envio->update($data + [
            'fecha_envio' => $data['estado'] === 'enviado' && ! $envio->fecha_envio ? now() : $envio->fecha_envio,
            'fecha_entrega' => $data['estado'] === 'entregado' && ! $envio->fecha_entrega ? now() : $envio->fecha_entrega,
        ]);

        if ($data['estado'] === 'entregado') {
            $envio->pedido()->update(['estado' => 'entregado']);
            PedidoHistorial::create([
                'pedido_id' => $envio->pedido->getKey(),
                'estado' => 'entregado',
                'user_id' => $request->user()->getKey(),
                'motivo' => 'Pedido marcado como entregado.',
            ]);
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

    /** @return array{0: CarbonImmutable|null, 1: CarbonImmutable|null} */
    private function periodBounds(?string $period): array
    {
        $today = CarbonImmutable::now();

        return match ($period) {
            'today' => [$today->startOfDay(), $today->endOfDay()],
            'yesterday' => [$today->subDay()->startOfDay(), $today->subDay()->endOfDay()],
            'this_week' => [$today->startOfWeek(), $today->endOfWeek()],
            'last_month' => [$today->subMonth()->startOfMonth(), $today->subMonth()->endOfMonth()],
            default => [null, null],
        };
    }
}
