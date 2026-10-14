<?php
// Archivo: controllers/eliminarArticuloController.php
session_start();
require_once '../config/conexion.php';

// Validar que el usuario esté logueado y que sea Administrador
// (Por seguridad, un vendedor no debería poder borrar artículos del catálogo)
if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador'){
    die("Error de seguridad: No tienes permisos de Administrador para eliminar artículos.");
}

// Verificar que se haya enviado un ID por la URL (método GET)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_articulo = $_GET['id'];

    try {
        $database = new Conexion();
        $db = $database->getConnection();

        // 1. Primero verificamos si el artículo existe
        $check_query = "SELECT nombre FROM articulos WHERE id_articulo = :id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id', $id_articulo);
        $check_stmt->execute();

        if ($check_stmt->rowCount() == 1) {
            
            // 2. Preparamos el DELETE
            $delete_query = "DELETE FROM articulos WHERE id_articulo = :id";
            $delete_stmt = $db->prepare($delete_query);
            $delete_stmt->bindParam(':id', $id_articulo);

            // 3. Ejecutamos el DELETE
            if ($delete_stmt->execute()) {
                // Si se borró con éxito, devolvemos a la tabla con un mensaje
                header("Location: ../views/articulos.php?exito=eliminado");
                exit();
            } else {
                die("Error al intentar eliminar el artículo.");
            }

        } else {
            die("Error: El artículo que intentas eliminar no existe.");
        }

    } catch(PDOException $e) {
        // ERROR CLAVE: Integridad Referencial
        // Si el artículo ya tiene entradas o salidas en el Kardex, MySQL bloqueará el DELETE por la FK.
        if($e->getCode() == 23000) {
            die("Error Crítico: No se puede eliminar este repuesto porque ya tiene movimientos registrados en el Kardex. (Violación de Llave Foránea).");
        } else {
            die("Error de Base de Datos: " . $e->getMessage());
        }
    }
} else {
    // Si entran al archivo sin pasar un ID en la URL
    header("Location: ../views/articulos.php");
    exit();
}
?>