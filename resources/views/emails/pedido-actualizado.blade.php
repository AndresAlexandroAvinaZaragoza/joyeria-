<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tipo === 'pago' ? 'Compra confirmada' : 'Actualización de pedido' }}</title>
</head>
<body style="margin:0;background:#f7f5f2;color:#1c1b18;font-family:Arial,sans-serif;">
    @php
        $labels = [
            'confirmado' => 'Pago confirmado',
            'pendiente' => 'Pendiente',
            'preparando' => 'Preparando',
            'enviado' => 'Enviado',
            'en_transito' => 'En tránsito',
            'entregado' => 'Entregado',
        ];
        $statusLabel = $labels[$estado] ?? ucfirst(str_replace('_', ' ', $estado));
    @endphp
    <div style="max-width:620px;margin:0 auto;padding:32px 20px;">
        <div style="padding:24px;background:#252522;color:#fff;">
            <p style="margin:0;color:#d4ad68;font-size:12px;letter-spacing:2px;text-transform:uppercase;">Lesa Joyería</p>
            <h1 style="margin:18px 0 0;font-size:28px;font-weight:400;">
                {{ $tipo === 'pago' ? '¡Gracias por tu compra!' : 'Actualización de tu pedido' }}
            </h1>
        </div>
        <div style="padding:28px;background:#fff;">
            <p style="margin-top:0;">Hola {{ $pedido->usuario?->name }},</p>
            <p>
                {{ $tipo === 'pago'
                    ? 'Hemos confirmado tu pago y comenzaremos a preparar tus piezas.'
                    : 'El estado de tu envío ha cambiado.' }}
            </p>
            <p style="margin:24px 0 8px;color:#756f66;font-size:12px;text-transform:uppercase;">Pedido</p>
            <p style="margin:0;font-size:20px;font-weight:bold;">{{ $pedido->numero_pedido }}</p>
            <p style="margin:16px 0 8px;color:#756f66;font-size:12px;text-transform:uppercase;">Estado</p>
            <p style="margin:0;color:#9b7534;font-size:18px;font-weight:bold;">{{ $statusLabel }}</p>
            <div style="margin-top:24px;border-top:1px solid #e2e2e2;">
                @foreach ($pedido->detalles as $detalle)
                    <p style="margin:14px 0 0;">
                        {{ $detalle->producto?->nombre ?? 'Producto' }} · {{ $detalle->cantidad }} pieza(s)
                    </p>
                @endforeach
            </div>
            <p style="margin:24px 0 0;font-size:18px;font-weight:bold;">
                Total: ${{ number_format((float) $pedido->total, 2) }}
            </p>
        </div>
    </div>
</body>
</html>
