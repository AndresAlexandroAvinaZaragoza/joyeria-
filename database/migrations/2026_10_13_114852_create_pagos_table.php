<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id('id_pagos');

            $table->string('metodo_pago', 50);

            $table->string('transaccion_id', 255)->nullable();

            $table->decimal('monto', 10, 2);

            $table->enum('estado', [
                'pendiente',
                'completado',
                'fallido',
                'reembolsado',
            ])->default('pendiente');

            $table->string('comprobante_url', 255)->nullable();

            $table->dateTime('fecha_pago')->nullable();

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
        Schema::dropIfExists('pagos');
    }
};
