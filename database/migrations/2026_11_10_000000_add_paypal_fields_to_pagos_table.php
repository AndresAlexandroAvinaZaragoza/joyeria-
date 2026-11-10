<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table): void {
            $table->string('paypal_order_id', 255)->nullable()->unique()->after('transaccion_id');
            $table->string('paypal_capture_id', 255)->nullable()->unique()->after('paypal_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table): void {
            $table->dropUnique(['paypal_order_id']);
            $table->dropUnique(['paypal_capture_id']);
            $table->dropColumn(['paypal_order_id', 'paypal_capture_id']);
        });
    }
};
