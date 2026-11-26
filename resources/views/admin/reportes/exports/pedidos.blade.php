<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de pedidos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #282421; }
        h1 { color: #8d672f; }
        .metricas { width: 100%; margin-bottom: 20px; }
        .metricas td { background: #f5efe7; padding: 12px; width: 33.333%; }
        .etiqueta { color: #8d672f; font-size: 8px; text-transform: uppercase; }
        .valor { display: block; font-size: 16px; font-weight: bold; margin-top: 5px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #8d672f; color: #fff; text-align: left; }
        th, td { border: 1px solid #ddd4c8; padding: 7px; }
        tr:nth-child(even) { background: #faf8f5; }
    </style>
</head>
<body>
    <h1>Reporte de pedidos</h1>
    <table class="metricas">
        <tr>
            <td><span class="etiqueta">Pedidos pendientes</span><span class="valor">{{ number_format($report['pending']) }}</span></td>
            <td><span class="etiqueta">Pedidos completados</span><span class="valor">{{ number_format($report['completed']) }}</span></td>
            <td><span class="etiqueta">Pedidos cancelados</span><span class="valor">{{ number_format($report['cancelled']) }}</span></td>
        </tr>
    </table>
    <table>
        <thead><tr><th>Cliente</th><th>Email</th><th>Pedidos</th><th>Total</th><th>Último pedido</th></tr></thead>
        <tbody>
            @forelse ($report['customers'] as $customer)
                <tr><td>{{ trim($customer['name']) }}</td><td>{{ $customer['email'] }}</td><td>{{ $customer['orders'] }}</td><td>${{ number_format($customer['total'], 2) }}</td><td>{{ $customer['last_order']?->format('d/m/Y') ?? '—' }}</td></tr>
            @empty
                <tr><td colspan="5">No hay pedidos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
