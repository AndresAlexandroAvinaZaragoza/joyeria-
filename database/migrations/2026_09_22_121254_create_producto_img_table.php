<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_img', function (Blueprint $table) {
            $table->id('id_img');

            $table->string('url_img', 255);
            $table->boolean('es_principal')->default(false);
            $table->integer('orden')->default(0);

            $table->timestamp('created_at')->useCurrent();

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
        Schema::dropIfExists('producto_img');
    }
};
