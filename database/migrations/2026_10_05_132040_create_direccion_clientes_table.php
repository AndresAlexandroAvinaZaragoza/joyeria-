<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direccion_clientes', function (Blueprint $table) {
            $table->id('id_direccion_clientes');

            $table->string('calle_numero', 50);
            $table->string('colonia', 50);
            $table->string('ciudad', 50);
            $table->string('estado', 50);
            $table->string('cp', 20);
            $table->string('referencias', 100)->nullable();
            $table->boolean('predeterminada')->default(false);

            $table->unsignedBigInteger('user_id');

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direccion_clientes');
    }
};
