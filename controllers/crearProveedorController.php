<?php
// Archivo: controllers/crearArticuloController.php
session_start();
require_once '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sku_vin = strtoupper(trim($_POST['sku_vin']));
    $nombre = trim($_POST['nombre']);
    $categoria = $_POST['categoria'];
    $id_proveedor = !empty($_POST['id_proveedor']) ? $_POST['id_proveedor'] : null;
    $precio_compra = $_POST['precio_compra'];
    $precio_venta = $_POST['precio_venta'];
    $stock_minimo = !empty($_POST['stock_minimo']) ? $_POST['stock_minimo'] : 5;
    $imagen_url = trim($_POST['imagen_url'] ?? '');

    // Si dejan la imagen vacía, generamos un placeholder corporativo automático con el nombre del producto
    if (empty($imagen_url)) {
        $texto_url = urlencode(substr($nombre, 0, 18));
        $imagen_url = "https://placehold.co/400x400/f8fafc/1e293b?text=" . $texto_url;
    }

    try {
        $database = new Conexion();
        $db = $database->getConnection();

        $query = "INSERT INTO articulos (sku_vin, nombre, categoria, imagen_url, id_proveedor, precio_compra, precio_venta, stock_minimo) 
                  VALUES (:sku_vin, :nombre, :categoria, :imagen_url, :id_proveedor, :precio_compra, :precio_venta, :stock_minimo)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':sku_vin', $sku_vin);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->bindParam(':imagen_url', $imagen_url);
        $stmt->bindParam(':id_proveedor', $id_proveedor);
        $stmt->bindParam(':precio_compra', $precio_compra);
        $stmt->bindParam(':precio_venta', $precio_venta);
        $stmt->bindParam(':stock_minimo', $stock_minimo);

        if ($stmt->execute()) {
            header("Location: ../views/articulos.php?exito=creado");
            exit();
        }
    } catch (PDOException $e) {
        die("Error al registrar el artículo: " . $e->getMessage());
    }
}
?>