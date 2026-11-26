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
                    <div class="dashboard-metric__icon dashboard-metric__icon--gold" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 7.5 12 3l6 4.5-1.5 9h-9z"/>
                            <path d="M6 7.5h12M9 12.5h6"/>
                        </svg>
                    </div>
                    <div>
                        <span>PRODUCTOS ACTIVOS</span>
                        <strong>{{ number_format($activeProducts) }}</strong>
                        <small>En tu catálogo</small>
                    </div>
                    <a href="{{ route('productos.index') }}" aria-label="Ver productos">↗</a>
                </article>
                <article class="dashboard-metric">
                    <div class="dashboard-metric__icon dashboard-metric__icon--rose" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 7h16M4 12h16M4 17h16"/>
                            <path d="M7 4v16M17 4v16"/>
                        </svg>
                    </div>
                    <div>
                        <span>PEDIDOS POR ATENDER</span>
                        <strong>{{ number_format($pendingOrders) }}</strong>
                        <small>Requieren seguimiento</small>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" aria-label="Ver pedidos">↗</a>
                </article>
                <article class="dashboard-metric">
                    <div class="dashboard-metric__icon dashboard-metric__icon--sand" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="7"/>
                            <path d="M12 8v8M9.5 10.5c.4-1.2 1.3-1.8 2.5-1.8 1.4 0 2.5.8 2.5 2 0 1.3-1.1 1.8-2.5 2.3-1.4.5-2.5 1-2.5 2.2 0 1.1 1 1.8 2.5 1.8 1.2 0 2.1-.6 2.5-1.6"/>
                        </svg>
                    </div>
                    <div>
                        <span>INGRESOS DEL MES</span>
                        <strong>${{ number_format($monthlyRevenue, 0) }}</strong>
                        <small>Ventas no canceladas</small>
                    </div>
                    <span class="dashboard-metric__trend">MXN</span>
                </article>
                <article class="dashboard-metric">
                    <div class="dashboard-metric__icon dashboard-metric__icon--dark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3 4.5 6v5c0 4.8 3 8.2 7.5 10 4.5-1.8 7.5-5.2 7.5-10V6z"/>
                            <path d="M12 8v5M12 16.5v.1"/>
                        </svg>
                    </div>
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
                                @php($stockImage = $item->producto?->imagenes->first()?->url_img)
                                <div class="dashboard-stock-item">
                                    <div class="dashboard-stock-item__mark">
                                        @if ($stockImage)
                                            <img
                                                src="{{ str_starts_with($stockImage, 'http') ? $stockImage : Storage::disk('public')->url($stockImage) }}"
                                                alt="{{ $item->producto?->nombre ?? 'Producto' }}"
                                            >
                                        @else
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <circle cx="12" cy="12" r="6"/>
                                                <circle cx="12" cy="12" r="2"/>
                                            </svg>
                                        @endif
                                    </div>
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
                    <a href="{{ route('productos.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="8"/><path d="M12 8v8M8 12h8"/>
                        </svg>
                        Nuevo producto
                    </a>
                    <a href="{{ route('inventario.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 7.5 12 4l8 3.5-8 3.5z"/><path d="M4 12l8 3.5 8-3.5M4 16.5l8 3.5 8-3.5"/>
                        </svg>
                        Actualizar inventario
                    </a>
                    <a href="{{ route('admin.shipments.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 7h11v10H3zM14 10h4l3 3v4h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>
                        </svg>
                        Revisar envíos
                    </a>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
