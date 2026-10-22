<x-app-layout>
    @vite(['resources/css/productos.css'])
    <div class="productos-page">
        <div class="productos-container">
            <section class="productos-header">
                <div><span class="productos-label">OPERACIÓN</span><h1>Gestión de <span>Pedidos</span></h1><p>Consulta los pedidos y el estado de pago de tus clientes.</p></div>
            </section>
            <section class="productos-table-card" x-data="{ openOrder: null }">
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
                                    <td class="pedido-acciones">
                                        <button type="button" class="productos-button productos-button--small" @click="openOrder = {{ $pedido->id_pedidos }}">DETALLES</button>
                                        <a href="{{ route('admin.orders.print', $pedido) }}" target="_blank" rel="noopener" class="productos-button productos-button--small">PDF</a>
                                        @if (! in_array($pedido->estado, ['cancelado', 'enviado', 'entregado'], true))
                                            <form method="POST" action="{{ route('admin.orders.cancel', $pedido) }}">@csrf @method('PATCH')<button type="submit" class="productos-limpiar">CANCELAR</button></form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="inventario-vacio">Aún no hay pedidos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @foreach ($pedidos as $pedido)
                    <div x-cloak x-show="openOrder === {{ $pedido->id_pedidos }}" x-transition.opacity class="pedido-modal-backdrop" @click.self="openOrder = null">
                        <section class="pedido-modal" role="dialog" aria-modal="true" aria-label="Detalles del pedido {{ $pedido->numero_pedido }}">
                            <header class="pedido-modal__header">
                                <div><span class="productos-label">DETALLE DEL PEDIDO</span><h2>{{ $pedido->numero_pedido }}</h2><p>{{ $pedido->usuario->name }} · {{ $pedido->created_at->format('d/m/Y H:i') }}</p></div>
                                <button type="button" class="pedido-modal__close" aria-label="Cerrar detalles" @click="openOrder = null">&times;</button>
                            </header>
                            <div class="pedido-modal__meta">
                                <span><strong>Pago:</strong> {{ ucfirst($pedido->pago?->estado ?? 'pendiente') }}</span>
                                <span><strong>Envío:</strong> {{ ucfirst(str_replace('_', ' ', $pedido->envio?->estado ?? 'pendiente')) }}</span>
                                <span><strong>Total:</strong> ${{ number_format($pedido->total, 2) }} MXN</span>
                            </div>
                            <div class="pedido-modal__items">
                                @foreach ($pedido->detalles as $detalle)
                                    @php($imagen = $detalle->producto?->imagenes->first()?->url_img)
                                    <div class="pedido-modal__item">
                                        <div class="pedido-modal__image">
                                            @if ($imagen)
                                                <img src="{{ str_starts_with($imagen, 'http') ? $imagen : Storage::disk('public')->url($imagen) }}" alt="{{ $detalle->producto?->nombre ?? 'Producto' }}">
                                            @else
                                                <span>Lesa</span>
                                            @endif
                                        </div>
                                        <div><strong>{{ $detalle->producto?->nombre ?? 'Producto no disponible' }}</strong><span>{{ $detalle->cantidad }} pieza(s) · ${{ number_format($detalle->precio_unitario, 2) }} c/u</span></div>
                                        <strong>${{ number_format($detalle->importe, 2) }}</strong>
                                    </div>
                                @endforeach
                            </div>
                            <footer class="pedido-modal__footer">
                                <a href="{{ route('admin.orders.print', $pedido) }}" target="_blank" rel="noopener" class="productos-button">ABRIR HOJA PDF</a>
                                <button type="button" class="productos-limpiar" @click="openOrder = null">CERRAR</button>
                            </footer>
                        </section>
                    </div>
                @endforeach
            </section>
        </div>
    </div>
</x-app-layout>