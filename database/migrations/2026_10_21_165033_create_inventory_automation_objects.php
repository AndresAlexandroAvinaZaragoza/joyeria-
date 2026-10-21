<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('movimientos_inventario')) {
            Schema::create('movimientos_inventario', function (Blueprint $table) {
                $table->id('id_movimiento');
                $table->string('tipo_movimiento', 20);
                $table->integer('cantidad');
                $table->string('motivo', 255)->nullable();
                $table->unsignedBigInteger('producto_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
                $table->foreign('producto_id')->references('id_productos')->on('productos')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('compras')) {
            Schema::create('compras', function (Blueprint $table) {
                $table->id('id_compra');
                $table->string('numero_compra', 30)->unique();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('compra_detalles')) {
            Schema::create('compra_detalles', function (Blueprint $table) {
                $table->id('id_compra_detalle');
                $table->integer('cantidad');
                $table->decimal('costo_unitario', 10, 2);
                $table->unsignedBigInteger('compra_id');
                $table->unsignedBigInteger('producto_id');
                $table->timestamps();
                $table->foreign('compra_id')->references('id_compra')->on('compras')->cascadeOnDelete();
                $table->foreign('producto_id')->references('id_productos')->on('productos')->restrictOnDelete();
            });
        }

        $this->dropObjects();
        $this->createViews();
        $this->createTriggers();
    }

    private function createViews(): void
    {
        DB::statement(<<<'SQL'
            CREATE VIEW vw_catalogo_joyeria AS
            SELECT p.id_productos AS producto_id, p.sku, p.nombre AS joya,
                c.nombre AS categoria, c.slug AS categoria_slug, p.material,
                p.peso_gr AS peso_gramos, p.talla_medida, p.precio_venta,
                COALESCE(i.stock_actual, 0) AS stock_disponible,
                pi.url_img AS imagen_principal, p.status AS activo
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categorias
            LEFT JOIN inventario i ON p.id_productos = i.producto_id
            LEFT JOIN producto_img pi ON p.id_productos = pi.producto_id AND pi.es_principal = 1
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW vw_alerta_reabastecimiento AS
            SELECT p.id_productos AS producto_id, p.sku, p.nombre AS joya,
                c.nombre AS categoria, i.stock_actual, i.stock_minimo,
                (i.stock_minimo - i.stock_actual) AS piezas_faltantes,
                i.updated_at AS ultima_actualizacion
            FROM inventario i
            INNER JOIN productos p ON i.producto_id = p.id_productos
            INNER JOIN categorias c ON p.id_categoria = c.id_categorias
            WHERE i.stock_actual <= i.stock_minimo AND p.status = 1
        SQL);

        $customerName = DB::connection()->getDriverName() === 'mysql'
            ? "TRIM(CONCAT(COALESCE(cli.name, ''), ' ', COALESCE(cli.apellidos, '')))"
            : "TRIM(COALESCE(cli.name, '') || ' ' || COALESCE(cli.apellidos, ''))";
        $responsibleName = DB::connection()->getDriverName() === 'mysql'
            ? "TRIM(CONCAT(COALESCE(u.name, ''), ' ', COALESCE(u.apellidos, '')))"
            : "TRIM(COALESCE(u.name, '') || ' ' || COALESCE(u.apellidos, ''))";

        DB::statement("
            CREATE VIEW vw_resumen_pedidos AS
            SELECT ped.id_pedidos AS pedido_id, ped.numero_pedido,
                {$customerName} AS cliente, cli.email AS cliente_email,
                ped.subtotal, ped.costo_envio, ped.total,
                ped.estado AS estado_pedido, pag.metodo_pago, pag.estado AS estado_pago,
                env.paqueteria, env.numero_guia, env.estado AS estado_envio,
                ped.created_at AS fecha_pedido
            FROM pedidos ped
            INNER JOIN users cli ON ped.user_id = cli.id
            LEFT JOIN pagos pag ON ped.id_pedidos = pag.pedido_id
            LEFT JOIN envios env ON ped.id_pedidos = env.pedido_id
        ");

        DB::statement(<<<'SQL'
            CREATE VIEW vw_productos_mas_vendidos AS
            SELECT p.id_productos AS producto_id, p.sku, p.nombre AS joya,
                c.nombre AS categoria, SUM(pd.cantidad) AS total_unidades_vendidas,
                SUM(pd.importe) AS ingresos_totales,
                SUM(pd.cantidad * (pd.precio_unitario - p.precio_costo)) AS utilidad_bruta_estimada
            FROM pedido_detalles pd
            INNER JOIN pedidos ped ON pd.pedido_id = ped.id_pedidos
            INNER JOIN productos p ON pd.producto_id = p.id_productos
            INNER JOIN categorias c ON p.id_categoria = c.id_categorias
            WHERE ped.estado <> 'cancelado'
            GROUP BY p.id_productos, p.sku, p.nombre, c.nombre
        SQL);

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

    private function createTriggers(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement(<<<'SQL'
                CREATE TRIGGER trg_crear_inventario_nuevo_producto
                AFTER INSERT ON productos FOR EACH ROW
                BEGIN
                    IF NOT EXISTS (SELECT 1 FROM inventario WHERE producto_id = NEW.id_productos) THEN
                        INSERT INTO inventario (producto_id, stock_actual, stock_minimo, updated_at)
                        VALUES (NEW.id_productos, 0, 5, CURRENT_TIMESTAMP);
                    END IF;
                END
            SQL);

            DB::statement(<<<'SQL'
                CREATE TRIGGER trg_validar_stock_antes_venta
                BEFORE INSERT ON pedido_detalles FOR EACH ROW
                BEGIN
                    IF COALESCE((SELECT stock_actual FROM inventario WHERE producto_id = NEW.producto_id), 0) < NEW.cantidad THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stock insuficiente para completar la venta';
                    END IF;
                END
            SQL);

            DB::statement(<<<'SQL'
                CREATE TRIGGER trg_descontar_stock_venta
                AFTER INSERT ON pedido_detalles FOR EACH ROW
                BEGIN
                    UPDATE inventario SET stock_actual = stock_actual - NEW.cantidad,
                        updated_at = CURRENT_TIMESTAMP WHERE producto_id = NEW.producto_id;
                END
            SQL);

            DB::statement(<<<'SQL'
                CREATE TRIGGER trg_aumentar_stock_por_compra
                AFTER INSERT ON compra_detalles FOR EACH ROW
                BEGIN
                    UPDATE inventario SET stock_actual = stock_actual + NEW.cantidad,
                        updated_at = CURRENT_TIMESTAMP WHERE producto_id = NEW.producto_id;
                    UPDATE productos SET precio_costo = NEW.costo_unitario,
                        updated_at = CURRENT_TIMESTAMP WHERE id_productos = NEW.producto_id;
                END
            SQL);

            DB::statement(<<<'SQL'
                CREATE TRIGGER trg_unica_direccion_predeterminada
                BEFORE INSERT ON direccion_clientes FOR EACH ROW
                BEGIN
                    IF NEW.predeterminada = 1 AND EXISTS (
                        SELECT 1 FROM direccion_clientes
                        WHERE user_id = NEW.user_id AND predeterminada = 1
                    ) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El usuario ya tiene una dirección predeterminada';
                    END IF;
                END
            SQL);

            return;
        }

        DB::statement(<<<'SQL'
            CREATE TRIGGER trg_crear_inventario_nuevo_producto
            AFTER INSERT ON productos FOR EACH ROW
            WHEN NOT EXISTS (SELECT 1 FROM inventario WHERE producto_id = NEW.id_productos)
            BEGIN
                INSERT INTO inventario (producto_id, stock_actual, stock_minimo, updated_at)
                VALUES (NEW.id_productos, 0, 5, CURRENT_TIMESTAMP);
            END
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER trg_validar_stock_antes_venta
            BEFORE INSERT ON pedido_detalles FOR EACH ROW
            WHEN COALESCE((SELECT stock_actual FROM inventario WHERE producto_id = NEW.producto_id), 0) < NEW.cantidad
            BEGIN
                SELECT RAISE(ABORT, 'Stock insuficiente para completar la venta');
            END
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER trg_descontar_stock_venta
            AFTER INSERT ON pedido_detalles FOR EACH ROW
            BEGIN
                UPDATE inventario SET stock_actual = stock_actual - NEW.cantidad,
                    updated_at = CURRENT_TIMESTAMP WHERE producto_id = NEW.producto_id;
            END
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER trg_aumentar_stock_por_compra
            AFTER INSERT ON compra_detalles FOR EACH ROW
            BEGIN
                UPDATE inventario SET stock_actual = stock_actual + NEW.cantidad,
                    updated_at = CURRENT_TIMESTAMP WHERE producto_id = NEW.producto_id;
                UPDATE productos SET precio_costo = NEW.costo_unitario,
                    updated_at = CURRENT_TIMESTAMP WHERE id_productos = NEW.producto_id;
            END
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER trg_unica_direccion_predeterminada
            BEFORE INSERT ON direccion_clientes FOR EACH ROW
            WHEN NEW.predeterminada = 1
            BEGIN
                UPDATE direccion_clientes SET predeterminada = 0 WHERE user_id = NEW.user_id;
            END
        SQL);
    }

    private function dropObjects(): void
    {
        foreach ([
            'vw_kardex_inventario',
            'vw_productos_mas_vendidos',
            'vw_resumen_pedidos',
            'vw_alerta_reabastecimiento',
            'vw_catalogo_joyeria',
        ] as $view) {
            DB::statement("DROP VIEW IF EXISTS {$view}");
        }

        foreach ([
            'trg_unica_direccion_predeterminada',
            'trg_aumentar_stock_por_compra',
            'trg_descontar_stock_venta',
            'trg_validar_stock_antes_venta',
            'trg_crear_inventario_nuevo_producto',
        ] as $trigger) {
            DB::statement("DROP TRIGGER IF EXISTS {$trigger}");
        }
    }

    public function down(): void
    {
        $this->dropObjects();
        Schema::dropIfExists('compra_detalles');
        Schema::dropIfExists('compras');
        Schema::dropIfExists('movimientos_inventario');
    }
};
