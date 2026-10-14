<?php
// Archivo: controllers/actualizarPedidoController.php
session_start();
require_once '../config/conexion.php';

// Validar que solo los empleados puedan cambiar los estados
if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] == 'Cliente_B2B'){ 
    die("Acceso denegado."); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pedido = $_POST['id_pedido'];
    $nuevo_estado = $_POST['estado'];

    try {
        $database = new Conexion();
        $db = $database->getConnection();
        
        $query = "UPDATE pedidos SET estado = :estado WHERE id_pedido = :id_pedido";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':estado', $nuevo_estado);
        $stmt->bindParam(':id_pedido', $id_pedido);
        
        if($stmt->execute()) {
            // Volvemos a la pantalla de gestión de pedidos
            header("Location: ../views/gestionar_pedidos.php?exito=actualizado");
            exit();
        } else {
            die("Error al actualizar el estado del pedido.");
        }

    } catch(PDOException $e) {
        die("Error de BD: " . $e->getMessage());
    }
} else {
    header("Location: ../views/gestionar_pedidos.php");
    exit();
}
?>