<x-app-layout>
    @vite(['resources/css/productos.css'])
    <div class="productos-page">
        <div class="productos-container">
            <section class="productos-header">
                <div><span class="productos-label">LOGÍSTICA</span><h1>Gestión de <span>Envíos</span></h1><p>Actualiza la paquetería, guía y estado de cada entrega.</p></div>
            </section>
            @if (session('status'))<div class="store-notice">{{ session('status') }}</div>@endif
            <section class="productos-table-card">
                <div class="productos-tabla-info">{{ $envios->count() }} envíos registrados</div>
                <div class="productos-table-wrap">
                    <table class="productos-table">
                        <thead><tr><th>PEDIDO</th><th>CLIENTE</th><th>DIRECCIÓN</th><th>PAQUETERÍA / GUÍA</th><th>ESTADO</th><th>ACTUALIZAR</th></tr></thead>
                        <tbody>
                            @forelse ($envios as $envio)
                                <tr>
                                    <td><strong>{{ $envio->pedido->numero_pedido }}</strong></td>
                                    <td>{{ $envio->pedido->usuario->name }}</td>
                                    <td>{{ $envio->direccion_destino }}</td>
                                    <td><input form="envio-{{ $envio->getKey() }}" name="paqueteria" value="{{ $envio->paqueteria }}" placeholder="Paquetería"><input form="envio-{{ $envio->getKey() }}" name="numero_guia" value="{{ $envio->numero_guia }}" placeholder="Número de guía"></td>
                                    <td><select form="envio-{{ $envio->getKey() }}" name="estado"><option value="pendiente" @selected($envio->estado === 'pendiente')>Pendiente</option><option value="preparando" @selected($envio->estado === 'preparando')>Preparando</option><option value="enviado" @selected($envio->estado === 'enviado')>Enviado</option><option value="en_transito" @selected($envio->estado === 'en_transito')>En tránsito</option><option value="entregado" @selected($envio->estado === 'entregado')>Entregado</option><option value="cancelado" @selected($envio->estado === 'cancelado')>Cancelado</option></select></td>
                                    <td><form id="envio-{{ $envio->getKey() }}" method="POST" action="{{ route('admin.shipments.update', $envio) }}">@csrf @method('PATCH')<button type="submit" class="productos-button">GUARDAR</button></form></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="inventario-vacio">Aún no hay envíos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
