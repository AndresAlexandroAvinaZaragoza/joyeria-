<x-app-layout>
    @vite(['resources/css/productos.css', 'resources/css/reportes.css'])
    <div class="productos-page">
        <div class="productos-container">
            <section class="productos-header">
                <div><span class="productos-label">LOGÍSTICA</span><h1>Historial de <span>Envíos</span></h1><p>Consulta los envíos entregados y cancelados. Esta vista es solo de consulta.</p></div>
                <a href="{{ route('admin.shipments.index') }}" class="productos-button">ENVÍOS ACTUALES</a>
            </section>
            <form method="GET" action="{{ route('admin.shipments.history') }}" class="productos-filtros" x-data>
                <div class="productos-busqueda">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.05 6.05a7.5 7.5 0 0 0 10.6 10.6Z" /></svg>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar por pedido, cliente o guía..." @input.debounce.400ms="$event.target.form.requestSubmit()">
                </div>
                <button type="button" class="productos-limpiar" @click="window.location = '{{ route('admin.shipments.history') }}'">LIMPIAR</button>
            </form>
            <div class="reportes-quick-filters" aria-label="Periodos rápidos">
                <span>ENVÍOS CREADOS:</span>
                <a href="{{ route('admin.shipments.history', array_merge(request()->except('period'), ['period' => 'today'])) }}" class="{{ $period === 'today' ? 'is-active' : '' }}">Hoy</a>
                <a href="{{ route('admin.shipments.history', array_merge(request()->except('period'), ['period' => 'yesterday'])) }}" class="{{ $period === 'yesterday' ? 'is-active' : '' }}">Ayer</a>
                <a href="{{ route('admin.shipments.history', array_merge(request()->except('period'), ['period' => 'this_week'])) }}" class="{{ $period === 'this_week' ? 'is-active' : '' }}">Esta semana</a>
                <a href="{{ route('admin.shipments.history', array_merge(request()->except('period'), ['period' => 'last_month'])) }}" class="{{ $period === 'last_month' ? 'is-active' : '' }}">Último mes</a>
            </div>
            <section class="productos-table-card">
                <div class="productos-tabla-info">Mostrando {{ $envios->firstItem() ?? 0 }} a {{ $envios->lastItem() ?? 0 }} de {{ $envios->total() }} envíos históricos</div>
                <div class="productos-table-wrap">
                    <table class="productos-table">
                        <thead><tr><th>PEDIDO</th><th>CLIENTE</th><th>DIRECCIÓN</th><th>PAQUETERÍA / GUÍA</th><th>ESTADO</th><th>FECHA</th><th>ACCIONES</th></tr></thead>
                        <tbody>
                            @forelse ($envios as $envio)
                                <tr>
                                    <td><strong>{{ $envio->pedido->numero_pedido }}</strong></td>
                                    <td>{{ $envio->pedido->usuario->name }}<br><small>{{ $envio->pedido->usuario->email }}</small></td>
                                    <td>{{ $envio->direccion_destino }}</td>
                                    <td>{{ $envio->paqueteria ?: 'Sin paquetería' }}<br><small>{{ $envio->numero_guia ?: 'Sin guía' }}</small></td>
                                    <td><span class="stock-badge">{{ ucfirst(str_replace('_', ' ', $envio->estado)) }}</span></td>
                                    <td>{{ $envio->fecha_entrega?->format('d/m/Y H:i') ?? \Illuminate\Support\Carbon::parse($envio->created_at)->format('d/m/Y H:i') }}</td>
                                    <td><a href="{{ route('admin.orders.print', $envio->pedido) }}" target="_blank" rel="noopener" class="productos-button productos-button--small">PDF</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="inventario-vacio">Aún no hay envíos históricos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="productos-paginacion">
                    @include('components.pagination-window', ['paginator' => $envios])
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
