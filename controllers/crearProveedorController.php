<?php
session_start();
require_once '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $razon_social = trim($_POST['razon_social']);
    $pais = trim($_POST['pais']);
    $contacto = trim($_POST['contacto']);
    $telefono = trim($_POST['telefono']);
    $tiempo_entrega = isset($_POST['tiempo_entrega_dias']) ? (int)$_POST['tiempo_entrega_dias'] : 0;

    if(empty($razon_social) || empty($pais)) {
        die("Error: Razón Social y País son obligatorios.");
    }

    try {
        $database = new Conexion();
        $db = $database->getConnection();

        $query = "INSERT INTO proveedores (razon_social, pais, contacto, telefono, tiempo_entrega_dias) 
                  VALUES (:razon_social, :pais, :contacto, :telefono, :tiempo_entrega)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':razon_social', $razon_social);
        $stmt->bindParam(':pais', $pais);
        $stmt->bindParam(':contacto', $contacto);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':tiempo_entrega', $tiempo_entrega);

        if($stmt->execute()) {
            header("Location: ../views/proveedores.php?exito=creado");
            exit();
        } else {
            die("Error al guardar el proveedor.");
        }
    } catch(PDOException $e) {
        die("Error de Base de Datos: " . $e->getMessage());
    }
} else {
    header("Location: ../views/proveedores.php");
    exit();
}
?>