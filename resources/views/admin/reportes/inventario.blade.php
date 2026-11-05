<x-app-layout>
    @vite(['resources/css/reportes.css'])

    <div class="reportes-page">
        <div class="reportes-container">
            <header class="reportes-header">
                <div>
                    <span class="reportes-eyebrow">CONTROL DE EXISTENCIAS</span>
                    <h1>Reporte de <span>inventario</span></h1>
                    <p>Consulta las existencias, niveles mínimos y el valor actual de tu inventario.</p>
                </div>
                <div class="reportes-header-actions">
                    <div class="reportes-type-switch" aria-label="Tipo de reporte">
                        <a href="{{ route('admin.reports.sales') }}">Ventas</a>
                        <a href="{{ route('admin.reports.inventory') }}" class="is-active">Inventario</a>
                    </div>
                    <div class="reportes-actions">
                        <a href="{{ route('admin.reports.inventory.pdf') }}" target="_blank" rel="noopener">PDF</a>
                        <a href="{{ route('admin.reports.inventory.excel') }}">EXCEL</a>
                    </div>
                </div>
            </header>

            <section class="reportes-metrics reportes-inventory-metrics" aria-label="Resumen de inventario">
                <article><span>PRODUCTOS CON INVENTARIO</span><strong>{{ number_format($report['total_products']) }}</strong><small>Productos activos</small></article>
                <article><span>EXISTENCIAS ACTUALES</span><strong>{{ number_format($report['total_units']) }}</strong><small>Unidades disponibles</small></article>
                <article><span>VALOR DEL INVENTARIO</span><strong>${{ number_format($report['total_value'], 2) }}</strong><small>MXN · a costo de compra</small></article>
                <article><span>STOCK MÍNIMO</span><strong>{{ number_format($report['low_stock_count']) }}</strong><small>Requieren reabastecimiento</small></article>
                <article><span>AGOTADOS</span><strong>{{ number_format($report['out_of_stock_count']) }}</strong><small>Sin existencias</small></article>
            </section>

            <section class="reportes-card">
                <div class="reportes-card__heading">
                    <div><span class="reportes-eyebrow">DETALLE</span><h2>Existencias por producto</h2></div>
                    <span>Actualizado {{ now()->format('d/m/Y H:i') }}</span>
                </div>
                <div class="reportes-table-wrap">
                    <table class="reportes-table">
                        <thead><tr><th>PRODUCTO</th><th>SKU</th><th>CATEGORÍA</th><th>EXISTENCIAS</th><th>MÍNIMO</th><th>COSTO UNITARIO</th><th>VALOR</th><th>ESTADO</th></tr></thead>
                        <tbody>
                            @forelse ($report['items'] as $item)
                                <tr>
                                    <td><strong>{{ $item['name'] }}</strong></td>
                                    <td>{{ $item['sku'] }}</td>
                                    <td>{{ $item['category'] }}</td>
                                    <td>{{ number_format($item['stock']) }}</td>
                                    <td>{{ number_format($item['minimum']) }}</td>
                                    <td>${{ number_format($item['unit_cost'], 2) }}</td>
                                    <td>${{ number_format($item['value'], 2) }}</td>
                                    <td><span class="reportes-badge reportes-badge--{{ $item['status'] }}">{{ match ($item['status']) { 'agotado' => 'Agotado', 'stock_minimo' => 'Stock mínimo', default => 'Normal' } }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="reportes-empty">No hay inventario registrado para productos activos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
