<?php
// Archivo: controllers/crearArticuloController.php
session_start();
require_once '../config/conexion.php';

// Verificamos que los datos hayan llegado por el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recibir y limpiar los datos para evitar inyección de código (XSS)
    $sku_vin = trim($_POST['sku_vin']);
    $nombre = trim($_POST['nombre']);
    $categoria = trim($_POST['categoria']);
    $id_proveedor = !empty($_POST['id_proveedor']) ? $_POST['id_proveedor'] : null;
    $precio_compra = trim($_POST['precio_compra']);
    $precio_venta = trim($_POST['precio_venta']);

    // Validar que los campos obligatorios no estén vacíos
    if(empty($sku_vin) || empty($nombre) || empty($categoria) || empty($precio_compra) || empty($precio_venta)) {
        die("Error: Faltan campos obligatorios por llenar.");
    }

    try {
        $database = new Conexion();
        $db = $database->getConnection();

        // Preparar la consulta SQL (INSERT)
        $query = "INSERT INTO articulos (sku_vin, nombre, categoria, id_proveedor, precio_compra, precio_venta) 
                  VALUES (:sku_vin, :nombre, :categoria, :id_proveedor, :precio_compra, :precio_venta)";
        
        $stmt = $db->prepare($query);

        // Vincular los parámetros de forma segura (Bind)
        $stmt->bindParam(':sku_vin', $sku_vin);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->bindParam(':id_proveedor', $id_proveedor);
        $stmt->bindParam(':precio_compra', $precio_compra);
        $stmt->bindParam(':precio_venta', $precio_venta);

        // Ejecutar la inserción
        if($stmt->execute()) {
            // Si guardó correctamente, redirigir a la lista de artículos
            header("Location: ../views/articulos.php?exito=creado");
            exit();
        } else {
            die("Error al guardar el artículo en la base de datos.");
        }

    } catch(PDOException $e) {
        // El error 23000 es cuando intentamos registrar un SKU/VIN que ya existe (duplicate entry)
        if($e->getCode() == 23000) {
            die("Error: El SKU / VIN '{$sku_vin}' ya está registrado en el sistema. Los números de serie deben ser únicos.");
        } else {
            die("Error de Base de Datos: " . $e->getMessage());
        }
    }
} else {
    // Si intentan entrar directo a la URL
    header("Location: ../views/articulos.php");
    exit();
}
?>