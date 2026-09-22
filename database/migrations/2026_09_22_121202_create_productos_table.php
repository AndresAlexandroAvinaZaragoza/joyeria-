<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id('id_productos');

            $table->string('sku', 50)->unique();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->string('material', 100)->nullable();

            $table->decimal('peso_gr', 10, 2)->nullable();
            $table->string('talla_medida', 30)->nullable();

            $table->decimal('precio_costo', 10, 2);
            $table->decimal('precio_venta', 10, 2);

            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->unsignedBigInteger('id_categoria');

            $table->foreign('id_categoria')
                ->references('id_categorias')
                ->on('categorias')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
