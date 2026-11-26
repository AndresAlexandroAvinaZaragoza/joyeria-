<?php

namespace App\Services;

use App\Mail\PedidoActualizado;
use App\Models\Pedido;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PedidoEmailService
{
    public function sendStatusUpdate(Pedido $pedido, string $estado, string $tipo): void
    {
        $pedido->loadMissing(['usuario', 'detalles.producto', 'envio']);

        $email = $pedido->usuario?->email;
        if (! is_string($email) || $email === '') {
            report(new \RuntimeException("El pedido {$pedido->numero_pedido} no tiene un correo de cliente."));

            return;
        }

        try {
            Mail::to($email)->send(new PedidoActualizado($pedido, $estado, $tipo));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
