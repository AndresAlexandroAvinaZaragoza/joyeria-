<x-app-layout>
    @vite(['resources/css/productos.css'])
    <div class="productos-page">
        <div class="productos-container">
            @if (session('status'))
                <div class="store-notice">{{ session('status') }}</div>
            @endif
            <section class="productos-header">
                <div><span class="productos-label">ABASTECIMIENTO</span><h1>Registrar <span>Compra</span></h1><p>Ingresa compras de proveedor y actualiza automáticamente el inventario.</p></div>
            </section>
            <section class="productos-table-card" style="margin-bottom: 25px;">
                <form method="POST" action="{{ route('admin.purchases.store') }}" class="productos-form">
                    @csrf
                    <div class="productos-form-group"><label for="numero_compra">Folio (opcional)</label><input id="numero_compra" name="numero_compra" value="{{ old('numero_compra') }}" placeholder="Se genera automáticamente"></div>
                    <div class="productos-form-group"><label for="producto_id">Producto</label><select id="producto_id" name="producto_id" required><option value="">Selecciona un producto</option>@foreach ($productos as $producto)<option value="{{ $producto->id_productos }}" @selected(old('producto_id') == $producto->id_productos)>{{ $producto->sku }} - {{ $producto->nombre }}</option>@endforeach</select></div>
                    <div class="productos-form-group"><label for="cantidad">Cantidad</label><input id="cantidad" name="cantidad" type="number" min="1" value="{{ old('cantidad', 1) }}" required></div>
                    <div class="productos-form-group"><label for="costo_unitario">Costo unitario</label><input id="costo_unitario" name="costo_unitario" type="number" min="0" step="0.01" value="{{ old('costo_unitario') }}" required></div>
                    <div class="productos-form-actions"><button class="productos-guardar" type="submit">REGISTRAR COMPRA</button></div>
                </form>
            </section>
            <section class="productos-table-card">
                <div class="productos-tabla-info">{{ $compras->count() }} compras recientes</div>
                <div class="productos-table-wrap"><table class="productos-table"><thead><tr><th>FOLIO</th><th>PRODUCTO</th><th>CANTIDAD</th><th>COSTO</th><th>RESPONSABLE</th><th>FECHA</th></tr></thead><tbody>
                    @forelse ($compras as $compra)
                        @foreach ($compra->detalles as $detalle)
                            <tr><td><strong>{{ $compra->numero_compra }}</strong></td><td>{{ $detalle->producto->nombre }}</td><td>{{ $detalle->cantidad }}</td><td>${{ number_format($detalle->costo_unitario, 2) }} MXN</td><td>{{ $compra->usuario?->name ?? 'Sistema' }}</td><td>{{ $compra->created_at->format('d/m/Y H:i') }}</td></tr>
                        @endforeach
                    @empty
                        <tr><td colspan="6" class="inventario-vacio">Aún no hay compras registradas.</td></tr>
                    @endforelse
                </tbody></table></div>
            </section>
        </div>
    </div>
</x-app-layout>
