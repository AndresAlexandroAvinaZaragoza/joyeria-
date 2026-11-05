<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_historial', function (Blueprint $table) {
            $table->id('id_historial');
            $table->unsignedBigInteger('pedido_id');
            $table->string('estado', 20);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('motivo')->nullable();
            $table->timestamps();

            $table->foreign('pedido_id')
                ->references('id_pedidos')
                ->on('pedidos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->unique(['pedido_id', 'estado']);
        });

        DB::table('pedidos')
            ->whereIn('estado', ['cancelado', 'entregado'])
            ->orderBy('id_pedidos')
            ->eachById(function (object $pedido): void {
                DB::table('pedido_historial')->insert([
                    'pedido_id' => $pedido->id_pedidos,
                    'estado' => $pedido->estado,
                    'motivo' => 'Registro histórico existente',
                    'created_at' => $pedido->updated_at,
                    'updated_at' => $pedido->updated_at,
                ]);
            }, column: 'id_pedidos');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_historial');
    }
};
