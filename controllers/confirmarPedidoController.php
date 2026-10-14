<?php
// Archivo: controllers/confirmarPedidoController.php
session_start();
require_once '../config/conexion.php';

// Validar que sea un cliente
if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Cliente_B2B'){ 
    die("Acceso denegado."); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pedido = $_POST['id_pedido'];
    $id_cliente = $_SESSION['usuario_id'];

    try {
        $database = new Conexion();
        $db = $database->getConnection();
        
        // Cambiamos el estado del pedido de 'Pendiente' a 'Aprobado' (o podría ser 'En Preparación')
        // Al dejar de ser 'Pendiente', desaparece del carrito y pasa al Historial
        $query = "UPDATE pedidos SET estado = 'Aprobado' WHERE id_pedido = :id_pedido AND id_usuario = :id_cliente";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_pedido', $id_pedido);
        $stmt->bindParam(':id_cliente', $id_cliente);
        
        if($stmt->execute()) {
            header("Location: ../views/mis_pedidos.php?exito=confirmado");
            exit();
        } else {
            die("Error al confirmar el pedido.");
        }

    } catch(PDOException $e) {
        die("Error de BD: " . $e->getMessage());
    }
} else {
    header("Location: ../views/mis_pedidos.php");
    exit();
}
?>