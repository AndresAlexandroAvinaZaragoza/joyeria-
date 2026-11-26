<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de clientes</title>
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
    <h1>Reporte de clientes</h1>
    <table class="metricas">
        <tr>
            <td><span class="etiqueta">Clientes registrados</span><span class="valor">{{ number_format($report['registered']) }}</span></td>
            <td><span class="etiqueta">Clientes frecuentes</span><span class="valor">{{ number_format($report['frequent']) }}</span></td>
            <td><span class="etiqueta">Con mayores compras</span><span class="valor">{{ number_format(count($report['top_customers'])) }}</span></td>
        </tr>
    </table>
    <h2>Historial de compras</h2>
    <table>
        <thead><tr><th>Cliente</th><th>Email</th><th>Pedidos</th><th>Total comprado</th><th>Registro</th></tr></thead>
        <tbody>
            @forelse ($report['history'] as $customer)
                <tr><td>{{ $customer['name'] }}</td><td>{{ $customer['email'] }}</td><td>{{ $customer['orders'] }}</td><td>${{ number_format($customer['total'], 2) }}</td><td>{{ $customer['registered_at']?->format('d/m/Y') }}</td></tr>
            @empty
                <tr><td colspan="5">No hay clientes registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
