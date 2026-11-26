<x-app-layout>
    @vite(['resources/css/productos.css', 'resources/css/reportes.css'])
    <div class="productos-page">
        <div class="productos-container">
            <section class="productos-header">
                <div><span class="productos-label">LOGÍSTICA</span><h1>Gestión de <span>Envíos</span></h1><p>Actualiza la paquetería, guía y estado de cada entrega.</p></div>
                <a href="{{ route('admin.shipments.history') }}" class="productos-button">VER HISTORIAL</a>
            </section>
            @if (session('status'))<div class="store-notice">{{ session('status') }}</div>@endif
            <form method="GET" action="{{ route('admin.shipments.index') }}" class="productos-filtros" x-data>
                <div class="productos-busqueda">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.05 6.05a7.5 7.5 0 0 0 10.6 10.6Z" /></svg>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar por pedido, cliente o guía..." @input.debounce.400ms="$event.target.form.requestSubmit()">
                </div>
                <div class="productos-select">
                    <select name="status" aria-label="Filtrar envíos por estado" @change="$event.target.form.requestSubmit()">
                        <option value="" @selected(request('status', '') === '')>TODOS LOS ENVÍOS</option>
                        <option value="pendiente" @selected(request('status') === 'pendiente')>PENDIENTES</option>
                        <option value="por_enviar" @selected(request('status') === 'por_enviar')>POR ENVIAR (SIN GUÍA NI PAQUETERÍA)</option>
                        <option value="en_transito" @selected(request('status') === 'en_transito')>EN TRÁNSITO</option>
                        <option value="entregado" @selected(request('status') === 'entregado')>ENTREGADOS</option>
                        <option value="enviado" @selected(request('status') === 'enviado')>ENVIADOS</option>
                    </select>
                </div>
                <button type="button" class="productos-limpiar" @click="window.location = '{{ route('admin.shipments.index') }}'">LIMPIAR</button>
            </form>
            <div class="reportes-quick-filters" aria-label="Periodos rápidos">
                <span>ENVÍOS CREADOS:</span>
                <a href="{{ route('admin.shipments.index', array_merge(request()->except('period'), ['period' => 'today'])) }}" class="{{ $period === 'today' ? 'is-active' : '' }}">Hoy</a>
                <a href="{{ route('admin.shipments.index', array_merge(request()->except('period'), ['period' => 'yesterday'])) }}" class="{{ $period === 'yesterday' ? 'is-active' : '' }}">Ayer</a>
                <a href="{{ route('admin.shipments.index', array_merge(request()->except('period'), ['period' => 'this_week'])) }}" class="{{ $period === 'this_week' ? 'is-active' : '' }}">Esta semana</a>
                <a href="{{ route('admin.shipments.index', array_merge(request()->except('period'), ['period' => 'last_month'])) }}" class="{{ $period === 'last_month' ? 'is-active' : '' }}">Último mes</a>
            </div>
            <section class="productos-table-card">
                <div class="productos-tabla-info">Mostrando {{ $envios->firstItem() ?? 0 }} a {{ $envios->lastItem() ?? 0 }} de {{ $envios->total() }} envíos</div>
                <div class="productos-table-wrap">
                    <table class="productos-table">
                        <thead><tr><th>PEDIDO</th><th>CLIENTE</th><th>DIRECCIÓN</th><th>PAQUETERÍA / GUÍA</th><th>ESTADO</th><th>ACTUALIZAR</th></tr></thead>
                        <tbody>
                            @forelse ($envios as $envio)
                                <tr>
                                    <td><strong>{{ $envio->pedido->numero_pedido }}</strong></td>
                                    <td>{{ $envio->pedido->usuario->name }}</td>
                                    <td>{{ $envio->direccion_destino }}</td>
                                    @if ($envio->estado === 'entregado' || $envio->pedido->estado === 'entregado')
                                        <td>{{ $envio->paqueteria ?: 'Sin paquetería' }}<br>{{ $envio->numero_guia ?: 'Sin guía' }}</td>
                                        <td>Entregado</td>
                                        <td>—</td>
                                    @else
                                        <td><input form="envio-{{ $envio->getKey() }}" name="paqueteria" maxlength="50" value="{{ $envio->paqueteria }}" placeholder="Paquetería"><input form="envio-{{ $envio->getKey() }}" name="numero_guia" maxlength="100" value="{{ $envio->numero_guia }}" placeholder="Número de guía"></td>
                                        <td><select form="envio-{{ $envio->getKey() }}" name="estado"><option value="pendiente" @selected($envio->estado === 'pendiente')>Pendiente</option><option value="preparando" @selected($envio->estado === 'preparando')>Preparando</option><option value="enviado" @selected($envio->estado === 'enviado')>Enviado</option><option value="en_transito" @selected($envio->estado === 'en_transito')>En tránsito</option><option value="entregado" @selected($envio->estado === 'entregado')>Entregado</option></select></td>
                                        <td><form id="envio-{{ $envio->getKey() }}" method="POST" action="{{ route('admin.shipments.update', $envio) }}">@csrf @method('PATCH')<button type="submit" class="productos-button">GUARDAR</button></form></td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="6" class="inventario-vacio">Aún no hay envíos.</td></tr>
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
