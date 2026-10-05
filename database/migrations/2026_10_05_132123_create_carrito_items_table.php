<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrito_items', function (Blueprint $table) {
            $table->id('id_items');

            $table->integer('cantidad')->default(1);

            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('carrito_id');

            $table->timestamps();

            $table->foreign('producto_id')
                ->references('id_productos')
                ->on('productos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('carrito_id')
                ->references('id_carrito')
                ->on('carrito')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unique(['carrito_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrito_items');
    }
};
