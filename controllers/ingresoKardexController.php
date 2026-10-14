<?php
// Archivo: controllers/ingresoKardexController.php
session_start();
require_once '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Verificamos que no sea un usuario fantasma
    if(!isset($_SESSION['usuario_id'])){
        die("Error: Sesión no válida.");
    }

    $id_articulo = $_POST['id_articulo'];
    $cantidad = (int)$_POST['cantidad'];
    $referencia = trim($_POST['referencia']);
    $id_usuario = $_SESSION['usuario_id']; // Quién hizo el ingreso

    if(empty($id_articulo) || $cantidad <= 0 || empty($referencia)) {
        die("Error: Todos los campos son obligatorios y la cantidad debe ser mayor a cero.");
    }

    try {
        $database = new Conexion();
        $db = $database->getConnection();
        
        // --- TRANSACCIÓN MATEMÁTICA DEL KARDEX ---
        // Iniciamos una transacción (Si algo falla, no se guarda nada a medias)
        $db->beginTransaction();

        // 1. Obtener el ÚLTIMO saldo de este artículo específico en el Kardex
        $query_saldo = "SELECT saldo_actual FROM kardex WHERE id_articulo = :id_articulo ORDER BY id_movimiento DESC LIMIT 1";
        $stmt_saldo = $db->prepare($query_saldo);
        $stmt_saldo->bindParam(':id_articulo', $id_articulo);
        $stmt_saldo->execute();
        
        $saldo_anterior = 0;
        if($stmt_saldo->rowCount() > 0) {
            $row = $stmt_saldo->fetch(PDO::FETCH_ASSOC);
            $saldo_anterior = (int)$row['saldo_actual'];
        }

        // 2. Calcular el NUEVO SALDO
        // Como es una ENTRADA, sumamos la cantidad que llegó hoy
        $nuevo_saldo = $saldo_anterior + $cantidad;

        // 3. Insertar el nuevo movimiento en el historial del Kardex
        $query_insert = "INSERT INTO kardex (id_articulo, tipo_movimiento, cantidad, saldo_actual, referencia, id_usuario) 
                         VALUES (:id_articulo, 'ENTRADA', :cantidad, :nuevo_saldo, :referencia, :id_usuario)";
        
        $stmt_insert = $db->prepare($query_insert);
        $stmt_insert->bindParam(':id_articulo', $id_articulo);
        $stmt_insert->bindParam(':cantidad', $cantidad);
        $stmt_insert->bindParam(':nuevo_saldo', $nuevo_saldo);
        $stmt_insert->bindParam(':referencia', $referencia);
        $stmt_insert->bindParam(':id_usuario', $id_usuario);
        
        $stmt_insert->execute();

        // Confirmamos la transacción para que se guarde en la BD
        $db->commit();

        // Redirigimos con éxito
        header("Location: ../views/dashboard.php?exito=ingreso_ok");
        exit();

    } catch(PDOException $e) {
        $db->rollBack(); // Si hay error matemático, deshacemos todo
        die("Error de Kardex: " . $e->getMessage());
    }
} else {
    header("Location: ../views/nuevo_ingreso.php");
    exit();
}
?>