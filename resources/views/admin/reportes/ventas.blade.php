<x-app-layout>
    @vite(['resources/css/reportes.css'])

    <div class="reportes-page">
        <div class="reportes-container">
            <header class="reportes-header">
                <div>
                    <span class="reportes-eyebrow">ANÁLISIS COMERCIAL</span>
                    <h1>Reporte de <span>ventas</span></h1>
                    <p>Consulta el rendimiento de tus ventas y los productos con mayor movimiento.</p>
                </div>
                <div class="reportes-header-actions">
                    <div class="reportes-type-switch" aria-label="Tipo de reporte">
                        <a href="{{ route('admin.reports.sales') }}" class="is-active">Ventas</a>
                        <a href="{{ route('admin.reports.inventory') }}">Inventario</a>
                        <a href="{{ route('admin.reports.orders') }}">Pedidos</a>
                        <a href="{{ route('admin.reports.customers') }}">Clientes</a>
                    </div>
                    <div class="reportes-actions">
                        <a href="{{ route('admin.reports.sales.pdf', request()->query()) }}" target="_blank" rel="noopener">PDF</a>
                        <a href="{{ route('admin.reports.sales.excel', request()->query()) }}">EXCEL</a>
                    </div>
                </div>
            </header>

            <form method="GET" class="reportes-filters">
                <label>Desde <input type="date" name="start_date" value="{{ $startDate }}" onchange="this.form.submit()"></label>
                <label>Hasta <input type="date" name="end_date" value="{{ $endDate }}" onchange="this.form.submit()"></label>
                <a href="{{ route('admin.reports.sales') }}">LIMPIAR</a>
            </form>
            <div class="reportes-quick-filters" aria-label="Periodos rápidos">
                <span>PERIODOS:</span>
                <a href="{{ route('admin.reports.sales', ['period' => 'today']) }}" class="{{ request('period') === 'today' ? 'is-active' : '' }}">Hoy</a>
                <a href="{{ route('admin.reports.sales', ['period' => 'yesterday']) }}" class="{{ request('period') === 'yesterday' ? 'is-active' : '' }}">Ayer</a>
                <a href="{{ route('admin.reports.sales', ['period' => 'last_week']) }}" class="{{ request('period') === 'last_week' ? 'is-active' : '' }}">Semana pasada</a>
                <a href="{{ route('admin.reports.sales', ['period' => 'last_month']) }}" class="{{ request('period') === 'last_month' ? 'is-active' : '' }}">Último mes</a>
            </div>

            <section class="reportes-metrics" aria-label="Resumen de ventas">
                <article><span>INGRESOS TOTALES</span><strong>${{ number_format($report['total_revenue'], 2) }}</strong><small>MXN · ventas no canceladas</small></article>
                <article><span>VENTAS REALIZADAS</span><strong>{{ number_format($report['sales_count']) }}</strong><small>Pedidos en el periodo</small></article>
                <article><span>TICKET PROMEDIO</span><strong>${{ number_format($report['sales_count'] ? $report['total_revenue'] / $report['sales_count'] : 0, 2) }}</strong><small>Ingreso por venta</small></article>
            </section>

            <section class="reportes-card reportes-chart-card">
                <div class="reportes-card__heading"><div><span class="reportes-eyebrow">TENDENCIA</span><h2>Ventas diarias</h2></div><span>{{ $startDate }} — {{ $endDate }}</span></div>
                <div class="reportes-chart"><canvas id="sales-chart" aria-label="Gráfica de ventas diarias"></canvas></div>
            </section>

            <section class="reportes-card reportes-weekly-card">
                <div class="reportes-card__heading"><div><span class="reportes-eyebrow">PERIODICIDAD</span><h2>Resumen semanal</h2></div></div>
                <div class="reportes-table-wrap">
                    <table class="reportes-table">
                        <thead><tr><th>SEMANA DESDE</th><th>VENTAS</th><th>INGRESOS</th></tr></thead>
                        <tbody>
                            @forelse ($report['weekly'] as $week)
                                <tr><td>{{ \Carbon\Carbon::parse($week['label'])->format('d/m/Y') }}</td><td>{{ number_format($week['sales']) }}</td><td>${{ number_format($week['revenue'], 2) }}</td></tr>
                            @empty
                                <tr><td colspan="3" class="reportes-empty">Sin datos semanales.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="reportes-columns">
                <section class="reportes-card">
                    <div class="reportes-card__heading"><div><span class="reportes-eyebrow">PRODUCTOS</span><h2>Más vendidos</h2></div></div>
                    <div class="reportes-table-wrap">
                        <table class="reportes-table">
                            <thead><tr><th>PRODUCTO</th><th>SKU</th><th>UNIDADES</th><th>INGRESOS</th></tr></thead>
                            <tbody>
                                @forelse ($report['top_products'] as $product)
                                    <tr><td><strong>{{ $product['name'] }}</strong></td><td>{{ $product['sku'] }}</td><td>{{ number_format($product['quantity']) }}</td><td>${{ number_format($product['revenue'], 2) }}</td></tr>
                                @empty
                                    <tr><td colspan="4" class="reportes-empty">No hay ventas en este periodo.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="reportes-card">
                    <div class="reportes-card__heading"><div><span class="reportes-eyebrow">RESUMEN</span><h2>Por mes</h2></div></div>
                    <div class="reportes-table-wrap">
                        <table class="reportes-table">
                            <thead><tr><th>MES</th><th>VENTAS</th><th>INGRESOS</th></tr></thead>
                            <tbody>
                                @forelse ($report['monthly'] as $month)
                                    <tr><td>{{ \Carbon\Carbon::createFromFormat('!Y-m', $month['label'])->translatedFormat('F Y') }}</td><td>{{ number_format($month['sales']) }}</td><td>${{ number_format($month['revenue'], 2) }}</td></tr>
                                @empty
                                    <tr><td colspan="3" class="reportes-empty">Sin datos mensuales.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('sales-chart'), {
            type: 'line',
            data: {
                labels: @json(collect($report['daily'])->pluck('label')),
                datasets: [{
                    label: 'Ingresos',
                    data: @json(collect($report['daily'])->pluck('revenue')),
                    borderColor: '#b88a44',
                    backgroundColor: 'rgba(184, 138, 68, .12)',
                    fill: true,
                    tension: .35,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { callback: value => '$' + value.toLocaleString() } } }
            }
        });
    </script>
</x-app-layout>
