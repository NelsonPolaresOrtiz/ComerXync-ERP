<?php
// Archivo: controllers/ventaController.php
session_start();
require_once '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(!isset($_SESSION['usuario_id'])){ die("Error: Sesión no válida."); }

    $id_articulo = $_POST['id_articulo'];
    $cantidad_vendida = (int)$_POST['cantidad'];
    $referencia = trim($_POST['referencia']);
    $id_usuario = $_SESSION['usuario_id'];

    if(empty($id_articulo) || $cantidad_vendida <= 0 || empty($referencia)) {
        die("Error: Datos inválidos. Revise la cantidad.");
    }

    try {
        $database = new Conexion();
        $db = $database->getConnection();
        
        // Iniciamos transacción para que no se arruine la base de datos si falla algo a medias
        $db->beginTransaction();

        // 1. Obtener el saldo actual desde el Kardex
        $query_saldo = "SELECT saldo_actual FROM kardex WHERE id_articulo = :id_articulo ORDER BY id_movimiento DESC LIMIT 1";
        $stmt_saldo = $db->prepare($query_saldo);
        $stmt_saldo->bindParam(':id_articulo', $id_articulo);
        $stmt_saldo->execute();
        
        $saldo_anterior = 0;
        if($stmt_saldo->rowCount() > 0) {
            $row = $stmt_saldo->fetch(PDO::FETCH_ASSOC);
            $saldo_anterior = (int)$row['saldo_actual'];
        }

        // --- REGLA DE NEGOCIO 01 (RN-01): VALIDACIÓN DE STOCK ---
        if($cantidad_vendida > $saldo_anterior) {
            $db->rollBack(); // Deshacemos todo
            die("ERROR DE NEGOCIO (RN-01): Operación rechazada. Estás intentando vender {$cantidad_vendida} unidades, pero el stock actual es de solo {$saldo_anterior} unidades.");
        }

        // 2. Calcular el nuevo saldo (RESTA MATEMÁTICA)
        $nuevo_saldo = $saldo_anterior - $cantidad_vendida;

        // 3. Insertar el movimiento de SALIDA en el historial
        $query_insert = "INSERT INTO kardex (id_articulo, tipo_movimiento, cantidad, saldo_actual, referencia, id_usuario) 
                         VALUES (:id_articulo, 'SALIDA', :cantidad, :nuevo_saldo, :referencia, :id_usuario)";
        
        $stmt_insert = $db->prepare($query_insert);
        $stmt_insert->bindParam(':id_articulo', $id_articulo);
        $stmt_insert->bindParam(':cantidad', $cantidad_vendida);
        $stmt_insert->bindParam(':nuevo_saldo', $nuevo_saldo);
        $stmt_insert->bindParam(':referencia', $referencia);
        $stmt_insert->bindParam(':id_usuario', $id_usuario);
        
        $stmt_insert->execute();

        // Confirmamos y cerramos transacción
        $db->commit();

        // Redirigir al historial para que vean la etiqueta roja de SALIDA
        header("Location: ../views/kardex.php?exito=venta_ok");
        exit();

    } catch(PDOException $e) {
        $db->rollBack();
        die("Error de Base de Datos: " . $e->getMessage());
    }
} else {
    header("Location: ../views/nueva_venta.php");
    exit();
}
?>