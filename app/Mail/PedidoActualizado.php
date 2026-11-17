<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoActualizado extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Pedido $pedido,
        public string $estado,
        public string $tipo,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->tipo === 'pago'
                ? "Compra confirmada: {$this->pedido->numero_pedido}"
                : "Actualización de tu pedido {$this->pedido->numero_pedido}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pedido-actualizado',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
