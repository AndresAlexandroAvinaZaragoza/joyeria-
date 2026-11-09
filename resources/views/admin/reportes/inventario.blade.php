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
                        <a href="{{ route('admin.reports.orders') }}">Pedidos</a>
                        <a href="{{ route('admin.reports.customers') }}">Clientes</a>
                    </div>
                    <div class="reportes-actions">
                        <a href="{{ route('admin.reports.inventory.pdf', request()->query()) }}" target="_blank" rel="noopener">PDF</a>
                        <a href="{{ route('admin.reports.inventory.excel', request()->query()) }}">EXCEL</a>
                    </div>
                </div>
            </header>

            <form method="GET" class="reportes-filters">
                <label>Categoría
                    <select name="category_id" onchange="this.form.submit()">
                        <option value="">Todas las categorías</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->getKey() }}" @selected($filters['category_id'] === $category->getKey())>{{ $category->nombre }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Estado
                    <select name="status" onchange="this.form.submit()">
                        <option value="">Todos los productos</option>
                        <option value="minimos" @selected($filters['status'] === 'minimos')>Stock mínimo</option>
                        <option value="agotados" @selected($filters['status'] === 'agotados')>Agotados</option>
                    </select>
                </label>
                <label class="reportes-checkbox">
                    <input type="checkbox" name="recent" value="1" @checked($filters['recent']) onchange="this.form.submit()">
                    Agregados en los últimos 30 días
                </label>
                <a href="{{ route('admin.reports.inventory') }}">LIMPIAR</a>
            </form>
            <div class="reportes-quick-filters" aria-label="Periodos rápidos">
                <span>PRODUCTOS AGREGADOS:</span>
                <a href="{{ route('admin.reports.inventory', array_merge(request()->except('period'), ['period' => 'today'])) }}" class="{{ request('period') === 'today' ? 'is-active' : '' }}">Hoy</a>
                <a href="{{ route('admin.reports.inventory', array_merge(request()->except('period'), ['period' => 'yesterday'])) }}" class="{{ request('period') === 'yesterday' ? 'is-active' : '' }}">Ayer</a>
                <a href="{{ route('admin.reports.inventory', array_merge(request()->except('period'), ['period' => 'last_week'])) }}" class="{{ request('period') === 'last_week' ? 'is-active' : '' }}">Semana pasada</a>
                <a href="{{ route('admin.reports.inventory', array_merge(request()->except('period'), ['period' => 'last_month'])) }}" class="{{ request('period') === 'last_month' ? 'is-active' : '' }}">Último mes</a>
            </div>

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
                    <span>{{ $report['total_products'] }} producto(s) encontrado(s)</span>
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
