<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario', function (Blueprint $table) {
            $table->id('id_inventario');

            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(0);

            $table->timestamp('updated_at')->nullable();

            $table->unsignedBigInteger('producto_id');

            $table->foreign('producto_id')
                ->references('id_productos')
                ->on('productos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};