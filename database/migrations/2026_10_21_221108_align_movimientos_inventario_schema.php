<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_kardex_inventario');

        if (Schema::hasColumn('movimientos_inventario', 'motivo')) {
            Schema::table('movimientos_inventario', function ($table): void {
                $table->dropColumn('motivo');
            });
        }

        if (Schema::hasColumn('movimientos_inventario', 'updated_at')) {
            Schema::table('movimientos_inventario', function ($table): void {
                $table->dropColumn('updated_at');
            });
        }

        $responsibleName = DB::connection()->getDriverName() === 'mysql'
            ? "TRIM(CONCAT(COALESCE(u.name, ''), ' ', COALESCE(u.apellidos, '')))"
            : "TRIM(COALESCE(u.name, '') || ' ' || COALESCE(u.apellidos, ''))";

        DB::statement("
            CREATE VIEW vw_kardex_inventario AS
            SELECT mi.id_movimiento AS movimiento_id, mi.created_at AS fecha_movimiento,
                p.sku, p.nombre AS joya, mi.tipo_movimiento, mi.cantidad,
                {$responsibleName} AS responsable, r.nombre AS rol_responsable
            FROM movimientos_inventario mi
            INNER JOIN productos p ON mi.producto_id = p.id_productos
            LEFT JOIN users u ON mi.user_id = u.id
            LEFT JOIN rol r ON u.rol_id = r.id_rol
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_kardex_inventario');

        Schema::table('movimientos_inventario', function ($table): void {
            $table->string('motivo', 255)->nullable();
            $table->timestamps();
        });

        $responsibleName = DB::connection()->getDriverName() === 'mysql'
            ? "TRIM(CONCAT(COALESCE(u.name, ''), ' ', COALESCE(u.apellidos, '')))"
            : "TRIM(COALESCE(u.name, '') || ' ' || COALESCE(u.apellidos, ''))";

        DB::statement("
            CREATE VIEW vw_kardex_inventario AS
            SELECT mi.id_movimiento AS movimiento_id, mi.created_at AS fecha_movimiento,
                p.sku, p.nombre AS joya, mi.tipo_movimiento, mi.cantidad, mi.motivo,
                {$responsibleName} AS responsable, r.nombre AS rol_responsable
            FROM movimientos_inventario mi
            INNER JOIN productos p ON mi.producto_id = p.id_productos
            LEFT JOIN users u ON mi.user_id = u.id
            LEFT JOIN rol r ON u.rol_id = r.id_rol
        ");
    }
};
