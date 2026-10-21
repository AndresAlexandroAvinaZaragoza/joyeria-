<x-app-layout>
    @vite(['resources/css/productos.css'])
    <div class="productos-page">
        <div class="productos-container">
            <section class="productos-header">
                <div><span class="productos-label">AUDITORÍA</span><h1>Kardex de <span>Inventario</span></h1><p>Consulta las entradas y salidas registradas por cada pieza.</p></div>
            </section>
            <form method="GET" action="{{ route('admin.kardex.index') }}" class="productos-filtros">
                <div class="productos-select"><select name="tipo" onchange="this.form.submit()"><option value="">TODOS LOS MOVIMIENTOS</option><option value="ENTRADA" @selected(request('tipo') === 'ENTRADA')>ENTRADAS</option><option value="SALIDA" @selected(request('tipo') === 'SALIDA')>SALIDAS</option><option value="AJUSTE" @selected(request('tipo') === 'AJUSTE')>AJUSTES</option></select></div>
                <a class="productos-limpiar" href="{{ route('admin.kardex.index') }}">LIMPIAR</a>
            </form>
            <section class="productos-table-card">
                <div class="productos-tabla-info">{{ $movimientos->total() }} movimientos registrados</div>
                <div class="productos-table-wrap"><table class="productos-table"><thead><tr><th>FECHA</th><th>SKU</th><th>PRODUCTO</th><th>TIPO</th><th>CANTIDAD</th><th>RESPONSABLE</th></tr></thead><tbody>
                    @forelse ($movimientos as $movimiento)
                        <tr><td>{{ $movimiento->created_at->format('d/m/Y H:i') }}</td><td>{{ $movimiento->producto?->sku ?? '—' }}</td><td><strong>{{ $movimiento->producto?->nombre ?? 'Producto eliminado' }}</strong></td><td><span class="stock-badge {{ $movimiento->tipo_movimiento === 'SALIDA' ? 'stock-bajo' : '' }}">{{ $movimiento->tipo_movimiento }}</span></td><td>{{ $movimiento->cantidad }}</td><td>{{ $movimiento->usuario?->name ?? 'Sistema' }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="inventario-vacio">Aún no hay movimientos registrados.</td></tr>
                    @endforelse
                </tbody></table></div>
                <div class="productos-paginacion">{{ $movimientos->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
