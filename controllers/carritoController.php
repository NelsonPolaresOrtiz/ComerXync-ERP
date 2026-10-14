<?php
// Archivo: controllers/carritoController.php
session_start();
require_once '../config/conexion.php';

// Validar acceso solo a clientes
if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Cliente_B2B'){ 
    header("Location: ../index.php?error=acceso_denegado"); 
    exit(); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_articulo = $_POST['id_articulo'];
    $cantidad = (int)$_POST['cantidad'];
    $id_cliente = $_SESSION['usuario_id'];

    if(empty($id_articulo) || $cantidad <= 0) {
        die("Error: Cantidad inválida.");
    }

    try {
        $database = new Conexion();
        $db = $database->getConnection();
        
        $db->beginTransaction();

        // 1. Validar Stock (Regla de Negocio RN-01)
        $query_stock = "SELECT saldo_actual FROM kardex WHERE id_articulo = :id ORDER BY id_movimiento DESC LIMIT 1";
        $stmt_stock = $db->prepare($query_stock);
        $stmt_stock->bindParam(':id', $id_articulo);
        $stmt_stock->execute();
        
        $stock_disponible = 0;
        if($stmt_stock->rowCount() > 0) {
            $stock_disponible = (int)$stmt_stock->fetch(PDO::FETCH_ASSOC)['saldo_actual'];
        }

        if($cantidad > $stock_disponible) {
            $db->rollBack();
            die("Error: Stock insuficiente para procesar el pedido.");
        }

        // 2. Buscar si el cliente ya tiene un pedido "Pendiente" (su carrito activo)
        $query_pedido = "SELECT id_pedido FROM pedidos WHERE id_usuario = :id_cliente AND estado = 'Pendiente' LIMIT 1";
        $stmt_pedido = $db->prepare($query_pedido);
        $stmt_pedido->bindParam(':id_cliente', $id_cliente);
        $stmt_pedido->execute();

        if($stmt_pedido->rowCount() > 0) {
            // Ya tiene un carrito abierto, usamos ese ID
            $id_pedido = $stmt_pedido->fetch(PDO::FETCH_ASSOC)['id_pedido'];
        } else {
            // No tiene carrito, creamos un nuevo pedido Pendiente
            $query_nuevo = "INSERT INTO pedidos (id_usuario, estado) VALUES (:id_cliente, 'Pendiente')";
            $stmt_nuevo = $db->prepare($query_nuevo);
            $stmt_nuevo->bindParam(':id_cliente', $id_cliente);
            $stmt_nuevo->execute();
            $id_pedido = $db->lastInsertId();
        }

        // 3. Añadir el artículo al Detalle del Pedido
        $query_detalle = "INSERT INTO detalle_pedidos (id_pedido, id_articulo, cantidad) VALUES (:id_pedido, :id_articulo, :cantidad)";
        $stmt_detalle = $db->prepare($query_detalle);
        $stmt_detalle->bindParam(':id_pedido', $id_pedido);
        $stmt_detalle->bindParam(':id_articulo', $id_articulo);
        $stmt_detalle->bindParam(':cantidad', $cantidad);
        $stmt_detalle->execute();

        // 4. RESERVAR EL STOCK (Descontar lógicamente del Kardex)
        $nuevo_saldo = $stock_disponible - $cantidad;
        $ref_pedido = "Reserva Web - Pedido #" . $id_pedido;
        
        $query_kardex = "INSERT INTO kardex (id_articulo, tipo_movimiento, cantidad, saldo_actual, referencia, id_usuario) 
                         VALUES (:id_articulo, 'SALIDA', :cantidad, :nuevo_saldo, :referencia, :id_cliente)";
        $stmt_kardex = $db->prepare($query_kardex);
        $stmt_kardex->bindParam(':id_articulo', $id_articulo);
        $stmt_kardex->bindParam(':cantidad', $cantidad);
        $stmt_kardex->bindParam(':nuevo_saldo', $nuevo_saldo);
        $stmt_kardex->bindParam(':referencia', $ref_pedido);
        $stmt_kardex->bindParam(':id_cliente', $id_cliente);
        $stmt_kardex->execute();

        $db->commit();

        // Redirigir de vuelta a la tienda con mensaje de éxito
        header("Location: ../views/tienda_b2b.php?agregado=ok");
        exit();

    } catch(PDOException $e) {
        $db->rollBack();
        die("Error de Carrito: " . $e->getMessage());
    }
} else {
    header("Location: ../views/tienda_b2b.php");
    exit();
}
?>