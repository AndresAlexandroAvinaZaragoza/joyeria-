<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException('Las funciones y procedimientos almacenados requieren una conexión MySQL.');
        }

        $this->dropRoutines();

        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_calcular_subtotal_carrito(p_carrito_id BIGINT UNSIGNED)
            RETURNS DECIMAL(10, 2)
            DETERMINISTIC
            READS SQL DATA
            BEGIN
                DECLARE v_subtotal DECIMAL(10, 2);

                SELECT COALESCE(SUM(ci.cantidad * p.precio_venta), 0.00)
                INTO v_subtotal
                FROM carrito_items ci
                INNER JOIN productos p ON ci.producto_id = p.id_productos
                WHERE ci.carrito_id = p_carrito_id;

                RETURN v_subtotal;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_margen_ganancia_producto(p_producto_id BIGINT UNSIGNED)
            RETURNS DECIMAL(5, 2)
            DETERMINISTIC
            READS SQL DATA
            BEGIN
                DECLARE v_costo DECIMAL(10, 2);
                DECLARE v_venta DECIMAL(10, 2);

                SELECT precio_costo, precio_venta
                INTO v_costo, v_venta
                FROM productos
                WHERE id_productos = p_producto_id;

                IF v_venta IS NULL OR v_venta = 0 THEN
                    RETURN 0.00;
                END IF;

                RETURN ROUND(((v_venta - v_costo) / v_venta) * 100, 2);
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_obtener_stock_disponible(p_producto_id BIGINT UNSIGNED)
            RETURNS INT
            DETERMINISTIC
            READS SQL DATA
            BEGIN
                DECLARE v_stock INT;

                SELECT COALESCE(stock_actual, 0)
                INTO v_stock
                FROM inventario
                WHERE producto_id = p_producto_id;

                RETURN COALESCE(v_stock, 0);
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_total_compras_cliente(p_user_id BIGINT UNSIGNED)
            RETURNS DECIMAL(12, 2)
            DETERMINISTIC
            READS SQL DATA
            BEGIN
                DECLARE v_total DECIMAL(12, 2);

                SELECT COALESCE(SUM(total), 0.00)
                INTO v_total
                FROM pedidos
                WHERE user_id = p_user_id AND estado <> 'cancelado';

                RETURN v_total;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_generar_numero_pedido()
            RETURNS VARCHAR(20)
            NOT DETERMINISTIC
            READS SQL DATA
            BEGIN
                DECLARE v_consecutivo INT;

                SELECT COUNT(*) + 1
                INTO v_consecutivo
                FROM pedidos
                WHERE DATE(created_at) = CURDATE();

                RETURN CONCAT(
                    'LS-', DATE_FORMAT(CURDATE(), '%y%m%d'), '-',
                    LPAD(v_consecutivo, 4, '0')
                );
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_agregar_item_carrito(
                IN p_carrito_id BIGINT UNSIGNED,
                IN p_producto_id BIGINT UNSIGNED,
                IN p_cantidad INT
            )
            BEGIN
                DECLARE v_stock INT DEFAULT 0;
                DECLARE v_cantidad_actual INT DEFAULT 0;
                DECLARE v_activo BOOLEAN DEFAULT FALSE;

                IF p_cantidad <= 0 THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'La cantidad debe ser mayor que cero.';
                END IF;

                SELECT status
                INTO v_activo
                FROM productos
                WHERE id_productos = p_producto_id;

                IF NOT v_activo THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'El producto no está disponible.';
                END IF;

                SET v_stock = fn_obtener_stock_disponible(p_producto_id);

                SELECT COALESCE(cantidad, 0)
                INTO v_cantidad_actual
                FROM carrito_items
                WHERE carrito_id = p_carrito_id AND producto_id = p_producto_id;

                IF v_cantidad_actual + p_cantidad > v_stock THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Stock insuficiente para agregar la cantidad solicitada.';
                END IF;

                INSERT INTO carrito_items (carrito_id, producto_id, cantidad, created_at, updated_at)
                VALUES (p_carrito_id, p_producto_id, p_cantidad, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    cantidad = cantidad + p_cantidad,
                    updated_at = CURRENT_TIMESTAMP;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_procesar_checkout(
                IN p_carrito_id BIGINT UNSIGNED,
                IN p_user_id BIGINT UNSIGNED,
                IN p_costo_envio DECIMAL(10, 2),
                IN p_direccion_destino TEXT,
                IN p_metodo_pago VARCHAR(50),
                OUT p_pedido_id BIGINT UNSIGNED
            )
            BEGIN
                DECLARE v_subtotal DECIMAL(10, 2);
                DECLARE v_total DECIMAL(10, 2);
                DECLARE v_numero_pedido VARCHAR(20);
                DECLARE v_items INT DEFAULT 0;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;

                START TRANSACTION;

                SELECT COUNT(*), fn_calcular_subtotal_carrito(p_carrito_id)
                INTO v_items, v_subtotal
                FROM carrito_items
                WHERE carrito_id = p_carrito_id;

                IF v_items = 0 OR v_subtotal <= 0 THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'El carrito está vacío.';
                END IF;

                SET v_total = v_subtotal + COALESCE(p_costo_envio, 0);
                SET v_numero_pedido = fn_generar_numero_pedido();

                INSERT INTO pedidos (
                    numero_pedido, subtotal, costo_envio, total, estado, user_id, created_at, updated_at
                )
                VALUES (
                    v_numero_pedido, v_subtotal, COALESCE(p_costo_envio, 0),
                    v_total, 'pendiente', p_user_id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                );

                SET p_pedido_id = LAST_INSERT_ID();

                INSERT INTO pedido_detalles (
                    precio_unitario, cantidad, importe, pedido_id, producto_id, created_at, updated_at
                )
                SELECT p.precio_venta, ci.cantidad, ci.cantidad * p.precio_venta,
                    p_pedido_id, ci.producto_id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                FROM carrito_items ci
                INNER JOIN productos p ON ci.producto_id = p.id_productos
                WHERE ci.carrito_id = p_carrito_id;

                INSERT INTO pagos (
                    metodo_pago, transaccion_id, monto, estado, fecha_pago, created_at, pedido_id
                )
                VALUES (
                    p_metodo_pago,
                    CONCAT('SP-', UPPER(SUBSTRING(REPLACE(UUID(), '-', ''), 1, 12))),
                    v_total, 'completado', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, p_pedido_id
                );

                INSERT INTO envios (
                    direccion_destino, estado, created_at, pedido_id
                )
                VALUES (p_direccion_destino, 'pendiente', CURRENT_TIMESTAMP, p_pedido_id);

                DELETE FROM carrito_items WHERE carrito_id = p_carrito_id;

                COMMIT;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_registrar_ajuste_inventario(
                IN p_producto_id BIGINT UNSIGNED,
                IN p_user_id BIGINT UNSIGNED,
                IN p_tipo_movimiento VARCHAR(20),
                IN p_cantidad INT
            )
            BEGIN
                DECLARE v_stock INT DEFAULT NULL;
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;

                IF p_cantidad < 0 OR p_tipo_movimiento NOT IN ('ENTRADA', 'SALIDA', 'AJUSTE') THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'El tipo o la cantidad del movimiento no son válidos.';
                END IF;

                START TRANSACTION;

                SELECT stock_actual
                INTO v_stock
                FROM inventario
                WHERE producto_id = p_producto_id
                FOR UPDATE;

                IF v_stock IS NULL THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'El producto no tiene un registro de inventario.';
                END IF;

                IF p_tipo_movimiento = 'ENTRADA' THEN
                    UPDATE inventario SET stock_actual = stock_actual + p_cantidad,
                        updated_at = CURRENT_TIMESTAMP WHERE producto_id = p_producto_id;
                ELSEIF p_tipo_movimiento = 'SALIDA' THEN
                    IF v_stock < p_cantidad THEN
                        SIGNAL SQLSTATE '45000'
                            SET MESSAGE_TEXT = 'No hay stock suficiente para la salida.';
                    END IF;
                    UPDATE inventario SET stock_actual = stock_actual - p_cantidad,
                        updated_at = CURRENT_TIMESTAMP WHERE producto_id = p_producto_id;
                ELSE
                    UPDATE inventario SET stock_actual = p_cantidad,
                        updated_at = CURRENT_TIMESTAMP WHERE producto_id = p_producto_id;
                END IF;

                INSERT INTO movimientos_inventario (
                    tipo_movimiento, cantidad, created_at, producto_id, user_id
                )
                VALUES (p_tipo_movimiento, p_cantidad, CURRENT_TIMESTAMP, p_producto_id, p_user_id);

                COMMIT;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_confirmar_pago_pedido(
                IN p_pedido_id BIGINT UNSIGNED,
                IN p_metodo_pago VARCHAR(50),
                IN p_transaccion_id VARCHAR(255),
                IN p_monto DECIMAL(10, 2),
                IN p_comprobante_url VARCHAR(255)
            )
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pedidos WHERE id_pedidos = p_pedido_id) THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'El pedido no existe.';
                END IF;

                INSERT INTO pagos (
                    pedido_id, metodo_pago, transaccion_id, monto, estado,
                    comprobante_url, fecha_pago, created_at
                )
                VALUES (
                    p_pedido_id, p_metodo_pago, p_transaccion_id, p_monto, 'completado',
                    p_comprobante_url, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                );

                UPDATE pedidos
                SET estado = 'preparado', updated_at = CURRENT_TIMESTAMP
                WHERE id_pedidos = p_pedido_id;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_cancelar_pedido(
                IN p_pedido_id BIGINT UNSIGNED,
                IN p_user_id BIGINT UNSIGNED
            )
            BEGIN
                DECLARE v_estado VARCHAR(20);
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    RESIGNAL;
                END;

                START TRANSACTION;

                SELECT estado
                INTO v_estado
                FROM pedidos
                WHERE id_pedidos = p_pedido_id
                FOR UPDATE;

                IF v_estado IS NULL THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'El pedido no existe.';
                END IF;

                IF v_estado IN ('enviado', 'entregado', 'cancelado') THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'No es posible cancelar este pedido.';
                END IF;

                UPDATE inventario i
                INNER JOIN pedido_detalles pd ON i.producto_id = pd.producto_id
                SET i.stock_actual = i.stock_actual + pd.cantidad,
                    i.updated_at = CURRENT_TIMESTAMP
                WHERE pd.pedido_id = p_pedido_id;

                INSERT INTO movimientos_inventario (
                    tipo_movimiento, cantidad, created_at, producto_id, user_id
                )
                SELECT 'ENTRADA', pd.cantidad, CURRENT_TIMESTAMP, pd.producto_id, p_user_id
                FROM pedido_detalles pd
                WHERE pd.pedido_id = p_pedido_id;

                UPDATE pedidos
                SET estado = 'cancelado', updated_at = CURRENT_TIMESTAMP
                WHERE id_pedidos = p_pedido_id;

                COMMIT;
            END
        SQL);
    }

    private function dropRoutines(): void
    {
        foreach ([
            'fn_calcular_subtotal_carrito',
            'fn_margen_ganancia_producto',
            'fn_obtener_stock_disponible',
            'fn_total_compras_cliente',
            'fn_generar_numero_pedido',
        ] as $function) {
            DB::unprepared("DROP FUNCTION IF EXISTS {$function}");
        }

        foreach ([
            'sp_agregar_item_carrito',
            'sp_procesar_checkout',
            'sp_registrar_ajuste_inventario',
            'sp_confirmar_pago_pedido',
            'sp_cancelar_pedido',
        ] as $procedure) {
            DB::unprepared("DROP PROCEDURE IF EXISTS {$procedure}");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $this->dropRoutines();
        }
    }
};
