<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de ventas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #282421; font-size: 11px; }
        h1 { color: #8d672f; margin-bottom: 4px; }
        .periodo { color: #777; margin-bottom: 20px; }
        .metricas { width: 100%; margin-bottom: 22px; }
        .metricas td { background: #f5efe7; padding: 12px; width: 50%; }
        .etiqueta { color: #8d672f; font-size: 9px; text-transform: uppercase; }
        .valor { display: block; font-size: 18px; font-weight: bold; margin-top: 5px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #8d672f; color: white; text-align: left; }
        th, td { border: 1px solid #ddd4c8; padding: 8px; }
        tr:nth-child(even) { background: #faf8f5; }
    </style>
</head>
<body>
    <h1>Reporte de ventas</h1>
    <p class="periodo">Periodo: {{ $report['from']->format('d/m/Y') }} al {{ $report['to']->format('d/m/Y') }}</p>
    <table class="metricas">
        <tr><td><span class="etiqueta">Ingresos totales</span><span class="valor">${{ number_format($report['total_revenue'], 2) }} MXN</span></td><td><span class="etiqueta">Ventas realizadas</span><span class="valor">{{ number_format($report['sales_count']) }}</span></td></tr>
    </table>
    <h2>Productos más vendidos</h2>
    <table>
        <thead><tr><th>Producto</th><th>SKU</th><th>Unidades</th><th>Ingresos</th></tr></thead>
        <tbody>
            @forelse ($report['top_products'] as $product)
                <tr><td>{{ $product['name'] }}</td><td>{{ $product['sku'] }}</td><td>{{ $product['quantity'] }}</td><td>${{ number_format($product['revenue'], 2) }}</td></tr>
            @empty
                <tr><td colspan="4">No hay ventas en este periodo.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
