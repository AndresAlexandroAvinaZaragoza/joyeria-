<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedido {{ $pedido->numero_pedido }} | Lesa Joyería</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f2f1ef; color: #252525; font-family: Arial, sans-serif; }
        .sheet { width: 210mm; min-height: 297mm; margin: 24px auto; padding: 18mm; background: white; }
        .toolbar { width: 210mm; margin: 20px auto 0; display: flex; justify-content: flex-end; gap: 8px; }
        button, a { border: 0; padding: 10px 15px; background: #9b741d; color: white; cursor: pointer; font-size: 11px; text-decoration: none; }
        .brand { color: #9b741d; font-size: 13px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; }
        header { display: flex; justify-content: space-between; gap: 20px; padding-bottom: 22px; border-bottom: 2px solid #9b741d; }
        h1 { margin: 8px 0 0; font: 500 30px Georgia, serif; }
        h2 { margin: 0 0 6px; font-size: 14px; }
        p { margin: 4px 0; color: #666; font-size: 12px; }
        .order-number { text-align: right; }
        .section { margin-top: 24px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 14px 0; border-bottom: 1px solid #ddd; }
        .info-grid strong, .totals strong { color: #222; font-size: 12px; }
        table { width: 100%; margin-top: 14px; border-collapse: collapse; font-size: 12px; }
        th { color: #9b741d; font-size: 10px; letter-spacing: 1px; text-align: left; text-transform: uppercase; }
        th, td { padding: 10px 7px; border-bottom: 1px solid #e4e4e4; }
        td:last-child, th:last-child { text-align: right; }
        .totals { width: 240px; margin: 24px 0 0 auto; }
        .totals p { display: flex; justify-content: space-between; gap: 20px; }
        .total { margin-top: 8px; padding-top: 10px; border-top: 2px solid #9b741d; font-size: 15px !important; }
        .footer { margin-top: 60px; padding-top: 14px; border-top: 1px solid #ddd; text-align: center; }
        @media print {
            body { background: white; }
            .toolbar { display: none; }
            .sheet { width: auto; min-height: auto; margin: 0; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">IMPRIMIR / GUARDAR PDF</button>
        <button type="button" onclick="window.close()">CERRAR</button>
    </div>
    <main class="sheet">
        <header>
            <div><div class="brand">Lesa Joyería</div><h1>Comprobante de pedido</h1></div>
            <div class="order-number"><strong>{{ $pedido->numero_pedido }}</strong><p>{{ $pedido->created_at->format('d/m/Y H:i') }}</p></div>
        </header>
        <section class="info-grid section">
            <div><h2>Cliente</h2><p>{{ $pedido->usuario->name }}</p><p>{{ $pedido->usuario->email }}</p><p>{{ $pedido->usuario->telefono ?? 'Sin teléfono registrado' }}</p></div>
            <div><h2>Entrega y pago</h2><p>Envío: {{ ucfirst(str_replace('_', ' ', $pedido->envio?->estado ?? 'pendiente')) }}</p><p>Pago: {{ ucfirst($pedido->pago?->estado ?? 'pendiente') }}</p><p>{{ $pedido->envio?->direccion_destino ?? 'Sin dirección registrada' }}</p></div>
        </section>
        <section class="section">
            <h2>Productos del pedido</h2>
            <table>
                <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio unitario</th><th>Importe</th></tr></thead>
                <tbody>
                    @foreach ($pedido->detalles as $detalle)
                        <tr><td>{{ $detalle->producto?->nombre ?? 'Producto no disponible' }}</td><td>{{ $detalle->cantidad }}</td><td>${{ number_format($detalle->precio_unitario, 2) }}</td><td>${{ number_format($detalle->importe, 2) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </section>
        <div class="totals">
            <p><span>Subtotal</span><strong>${{ number_format($pedido->subtotal, 2) }}</strong></p>
            <p><span>Costo de envío</span><strong>${{ number_format($pedido->costo_envio, 2) }}</strong></p>
            <p class="total"><span>Total</span><strong>${{ number_format($pedido->total, 2) }}</strong></p>
        </div>
        <div class="footer"><p>Documento generado para control interno de Lesa Joyería.</p></div>
    </main>
</body>
</html>
