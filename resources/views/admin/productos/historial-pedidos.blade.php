<x-app-layout>
    @vite(['resources/css/productos.css', 'resources/css/reportes.css'])
    <div class="productos-page">
        <div class="productos-container">
            <section class="productos-header">
                <div><span class="productos-label">OPERACIÓN</span><h1>Historial de <span>Pedidos</span></h1><p>Consulta los pedidos entregados y cancelados.</p></div>
            </section>
            <form method="GET" action="{{ route('admin.orders.history') }}" class="productos-filtros" x-data>
                <div class="productos-busqueda">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.05 6.05a7.5 7.5 0 0 0 10.6 10.6Z" /></svg>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar por pedido, cliente o estado..." @input.debounce.400ms="$event.target.form.requestSubmit()">
                </div>
                <button type="button" class="productos-limpiar" @click="window.location = '{{ route('admin.orders.history') }}'">LIMPIAR</button>
            </form>
            <div class="reportes-quick-filters" aria-label="Periodos rápidos">
                <span>REGISTROS CREADOS:</span>
                <a href="{{ route('admin.orders.history', array_merge(request()->except('period'), ['period' => 'today'])) }}" class="{{ $period === 'today' ? 'is-active' : '' }}">Hoy</a>
                <a href="{{ route('admin.orders.history', array_merge(request()->except('period'), ['period' => 'yesterday'])) }}" class="{{ $period === 'yesterday' ? 'is-active' : '' }}">Ayer</a>
                <a href="{{ route('admin.orders.history', array_merge(request()->except('period'), ['period' => 'this_week'])) }}" class="{{ $period === 'this_week' ? 'is-active' : '' }}">Esta semana</a>
                <a href="{{ route('admin.orders.history', array_merge(request()->except('period'), ['period' => 'last_month'])) }}" class="{{ $period === 'last_month' ? 'is-active' : '' }}">Último mes</a>
            </div>
            <section class="productos-table-card" x-data="{ openOrder: null }">
                <div class="productos-tabla-info">{{ $historial->count() }} registros encontrados</div>
                <div class="productos-table-wrap">
                    <table class="productos-table">
                        <thead><tr><th>PEDIDO</th><th>CLIENTE</th><th>ESTADO</th><th>MOTIVO</th><th>FECHA</th><th>RESPONSABLE</th><th>ACCIONES</th></tr></thead>
                        <tbody>
                            @forelse ($historial as $registro)
                                <tr>
                                    <td><strong>{{ $registro->pedido->numero_pedido }}</strong></td>
                                    <td>{{ $registro->pedido->usuario->name }}</td>
                                    <td><span class="stock-badge">{{ ucfirst($registro->estado) }}</span></td>
                                    <td>{{ $registro->motivo ?: 'Sin motivo registrado' }}</td>
                                    <td>{{ $registro->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $registro->usuario?->name ?? 'Sistema' }}</td>
                                    <td>
                                        <button type="button" class="productos-button productos-button--small" @click="openOrder = {{ $registro->pedido->id_pedidos }}">VER PEDIDO</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="inventario-vacio">Aún no hay pedidos históricos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @foreach ($historial as $registro)
                    @php($pedido = $registro->pedido)
                    <div x-cloak x-show="openOrder === {{ $pedido->id_pedidos }}" x-transition.opacity class="pedido-modal-backdrop" @click.self="openOrder = null">
                        <section class="pedido-modal" role="dialog" aria-modal="true" aria-label="Detalles del pedido {{ $pedido->numero_pedido }}">
                            <header class="pedido-modal__header">
                                <div>
                                    <span class="productos-label">DETALLE DEL PEDIDO</span>
                                    <h2>{{ $pedido->numero_pedido }}</h2>
                                    <p>{{ $pedido->usuario->name }} · {{ $pedido->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <button type="button" class="pedido-modal__close" aria-label="Cerrar detalles" @click="openOrder = null">&times;</button>
                            </header>
                            <div class="pedido-modal__meta">
                                <span><strong>Estado:</strong> {{ ucfirst($pedido->estado) }}</span>
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
                                        <div>
                                            <strong>{{ $detalle->producto?->nombre ?? 'Producto no disponible' }}</strong>
                                            <span>{{ $detalle->cantidad }} pieza(s) · ${{ number_format($detalle->precio_unitario, 2) }} c/u</span>
                                        </div>
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
