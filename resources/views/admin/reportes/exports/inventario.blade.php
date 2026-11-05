<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de inventario</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #282421; font-size: 10px; }
        h1 { color: #8d672f; margin-bottom: 4px; }
        .periodo { color: #777; margin-bottom: 20px; }
        .metricas { width: 100%; margin-bottom: 22px; }
        .metricas td { background: #f5efe7; padding: 10px; width: 25%; }
        .etiqueta { color: #8d672f; font-size: 8px; text-transform: uppercase; }
        .valor { display: block; font-size: 16px; font-weight: bold; margin-top: 5px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #8d672f; color: white; text-align: left; }
        th, td { border: 1px solid #ddd4c8; padding: 6px; }
        tr:nth-child(even) { background: #faf8f5; }
    </style>
</head>
<body>
    <h1>Reporte de inventario</h1>
    <p class="periodo">Generado el {{ now()->format('d/m/Y H:i') }}</p>
    <table class="metricas">
        <tr>
            <td><span class="etiqueta">Productos</span><span class="valor">{{ number_format($report['total_products']) }}</span></td>
            <td><span class="etiqueta">Existencias</span><span class="valor">{{ number_format($report['total_units']) }}</span></td>
            <td><span class="etiqueta">Valor total</span><span class="valor">${{ number_format($report['total_value'], 2) }}</span></td>
            <td><span class="etiqueta">Agotados</span><span class="valor">{{ number_format($report['out_of_stock_count']) }}</span></td>
        </tr>
    </table>
    <table>
        <thead><tr><th>Producto</th><th>SKU</th><th>Categoría</th><th>Existencias</th><th>Mínimo</th><th>Costo unitario</th><th>Valor</th><th>Estado</th></tr></thead>
        <tbody>
            @forelse ($report['items'] as $item)
                <tr><td>{{ $item['name'] }}</td><td>{{ $item['sku'] }}</td><td>{{ $item['category'] }}</td><td>{{ $item['stock'] }}</td><td>{{ $item['minimum'] }}</td><td>${{ number_format($item['unit_cost'], 2) }}</td><td>${{ number_format($item['value'], 2) }}</td><td>{{ match ($item['status']) { 'agotado' => 'Agotado', 'stock_minimo' => 'Stock mínimo', default => 'Normal' } }}</td></tr>
            @empty
                <tr><td colspan="8">No hay inventario registrado.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
