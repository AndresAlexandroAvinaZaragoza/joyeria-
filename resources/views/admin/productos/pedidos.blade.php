<x-app-layout>
    @vite(['resources/css/productos.css'])
    <div class="productos-page">
        <div class="productos-container">
            <section class="productos-header">
                <div><span class="productos-label">OPERACIÓN</span><h1>Gestión de <span>Pedidos</span></h1><p>Consulta los pedidos y el estado de pago de tus clientes.</p></div>
            </section>
            <section class="productos-table-card">
                <div class="productos-tabla-info">{{ $pedidos->count() }} pedidos registrados</div>
                <div class="productos-table-wrap">
                    <table class="productos-table">
                        <thead><tr><th>PEDIDO</th><th>CLIENTE</th><th>FECHA</th><th>PRODUCTOS</th><th>TOTAL</th><th>PAGO</th><th>ENVÍO</th><th>ACCIONES</th></tr></thead>
                        <tbody>
                            @forelse ($pedidos as $pedido)
                                <tr>
                                    <td><strong>{{ $pedido->numero_pedido }}</strong><br><small>{{ $pedido->estado }}</small></td>
                                    <td>{{ $pedido->usuario->name }}<br><small>{{ $pedido->usuario->email }}</small></td>
                                    <td>{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $pedido->detalles->sum('cantidad') }} pieza(s)</td>
                                    <td class="producto-precio">${{ number_format($pedido->total, 2) }} MXN</td>
                                    <td><span class="stock-badge">{{ ucfirst($pedido->pago?->estado ?? 'pendiente') }}</span></td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $pedido->envio?->estado ?? 'pendiente')) }}</td>
                                    <td>@if (! in_array($pedido->estado, ['cancelado', 'enviado', 'entregado'], true))<form method="POST" action="{{ route('admin.orders.cancel', $pedido) }}">@csrf @method('PATCH')<button type="submit" class="productos-limpiar">CANCELAR</button></form>@else — @endif</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="inventario-vacio">Aún no hay pedidos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>