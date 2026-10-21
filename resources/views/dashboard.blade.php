<x-app-layout>
    @vite(['resources/css/dashboard.css'])

    <div class="dashboard-page">
        <div class="dashboard-container">
            <header class="dashboard-header">
                <div>
                    <span class="dashboard-eyebrow">PANEL DE CONTROL</span>
                    <h1>Buenos días, {{ \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') }}.</h1>
                    <p>Este es el resumen de tu joyería para hoy.</p>
                </div>
                <div class="dashboard-date">
                    <span>HOY</span>
                    <strong>{{ now()->translatedFormat('d M, Y') }}</strong>
                </div>
            </header>

            <section class="dashboard-metrics" aria-label="Resumen de la tienda">
                <article class="dashboard-metric">
                    <div class="dashboard-metric__icon dashboard-metric__icon--gold">✦</div>
                    <div>
                        <span>PRODUCTOS ACTIVOS</span>
                        <strong>{{ number_format($activeProducts) }}</strong>
                        <small>En tu catálogo</small>
                    </div>
                    <a href="{{ route('productos.index') }}" aria-label="Ver productos">↗</a>
                </article>
                <article class="dashboard-metric">
                    <div class="dashboard-metric__icon dashboard-metric__icon--rose">⌁</div>
                    <div>
                        <span>PEDIDOS POR ATENDER</span>
                        <strong>{{ number_format($pendingOrders) }}</strong>
                        <small>Requieren seguimiento</small>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" aria-label="Ver pedidos">↗</a>
                </article>
                <article class="dashboard-metric">
                    <div class="dashboard-metric__icon dashboard-metric__icon--sand">◌</div>
                    <div>
                        <span>INGRESOS DEL MES</span>
                        <strong>${{ number_format($monthlyRevenue, 0) }}</strong>
                        <small>Ventas no canceladas</small>
                    </div>
                    <span class="dashboard-metric__trend">MXN</span>
                </article>
                <article class="dashboard-metric">
                    <div class="dashboard-metric__icon dashboard-metric__icon--dark">!</div>
                    <div>
                        <span>STOCK BAJO</span>
                        <strong>{{ number_format($lowStockProducts) }}</strong>
                        <small>Productos por reabastecer</small>
                    </div>
                    <a href="{{ route('inventario.index') }}" aria-label="Ver inventario">↗</a>
                </article>
            </section>

            <div class="dashboard-grid">
                <section class="dashboard-card dashboard-orders">
                    <div class="dashboard-card__header">
                        <div>
                            <span class="dashboard-eyebrow">ACTIVIDAD RECIENTE</span>
                            <h2>Últimos pedidos</h2>
                        </div>
                        <a href="{{ route('admin.orders.index') }}">VER TODOS <span>→</span></a>
                    </div>
                    @if ($recentOrders->isEmpty())
                        <div class="dashboard-empty">Aún no hay pedidos registrados.</div>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead>
                                    <tr><th>PEDIDO</th><th>CLIENTE</th><th>FECHA</th><th>ESTADO</th><th>TOTAL</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentOrders as $order)
                                        <tr>
                                            <td><strong>#{{ $order->numero_pedido }}</strong></td>
                                            <td>{{ $order->usuario?->name ?? 'Cliente' }}</td>
                                            <td>{{ $order->created_at?->format('d/m/Y') }}</td>
                                            <td><span class="dashboard-status dashboard-status--{{ $order->estado }}">{{ str_replace('_', ' ', $order->estado) }}</span></td>
                                            <td class="dashboard-total">${{ number_format($order->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

                <section class="dashboard-card dashboard-stock">
                    <div class="dashboard-card__header">
                        <div>
                            <span class="dashboard-eyebrow">INVENTARIO</span>
                            <h2>Stock bajo</h2>
                        </div>
                        <a href="{{ route('inventario.index') }}">GESTIONAR <span>→</span></a>
                    </div>
                    @if ($lowStockItems->isEmpty())
                        <div class="dashboard-empty">Todo el inventario está en buen nivel.</div>
                    @else
                        <div class="dashboard-stock-list">
                            @foreach ($lowStockItems as $item)
                                <div class="dashboard-stock-item">
                                    <div class="dashboard-stock-item__mark">✦</div>
                                    <div>
                                        <strong>{{ $item->producto?->nombre ?? 'Producto eliminado' }}</strong>
                                        <small>SKU {{ $item->producto?->sku ?? '—' }}</small>
                                    </div>
                                    <span class="dashboard-stock-item__count">{{ $item->stock_actual }} <small>/ {{ $item->stock_minimo }}</small></span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <section class="dashboard-quick-actions">
                <span class="dashboard-eyebrow">ACCESOS RÁPIDOS</span>
                <div>
                    <a href="{{ route('productos.index') }}"><span>＋</span> Nuevo producto</a>
                    <a href="{{ route('inventario.index') }}"><span>◫</span> Actualizar inventario</a>
                    <a href="{{ route('admin.shipments.index') }}"><span>⌁</span> Revisar envíos</a>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
