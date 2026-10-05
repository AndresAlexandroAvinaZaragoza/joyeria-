<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_detalles', function (Blueprint $table) {
            $table->id('id_pedido_detalle');

            $table->decimal('precio_unitario', 10, 2);
            $table->integer('cantidad');
            $table->decimal('importe', 10, 2);

            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('producto_id');

            $table->timestamps();

            $table->foreign('pedido_id')
                ->references('id_pedidos')
                ->on('pedidos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('producto_id')
                ->references('id_productos')
                ->on('productos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_detalles');
    }
};
