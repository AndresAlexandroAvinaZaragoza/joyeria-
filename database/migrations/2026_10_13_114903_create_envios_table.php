<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('envios', function (Blueprint $table) {
            $table->id('id_envios');

            $table->text('direccion_destino');

            $table->string('paqueteria', 50)->nullable();

            $table->string('numero_guia', 100)->nullable();

            $table->enum('estado', [
                'pendiente',
                'preparando',
                'enviado',
                'en_transito',
                'entregado',
                'cancelado',
            ])->default('pendiente');

            $table->dateTime('fecha_envio')->nullable();

            $table->dateTime('fecha_entrega')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->unsignedBigInteger('pedido_id');

            $table->foreign('pedido_id')
                ->references('id_pedidos')
                ->on('pedidos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envios');
    }
};
