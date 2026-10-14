<?php
// Archivo: controllers/loginController.php
session_start();
require_once '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if(empty($email) || empty($password)){
        header("Location: ../index.php?error=empty");
        exit();
    }

    try {
        $database = new Conexion();
        $db = $database->getConnection();
        
        if($db === null) {
            die("Error crítico: No se pudo conectar a la base de datos.");
        }

        $query = "SELECT id_usuario, nombre, email, password, rol FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($password === $row['password']) {
                
                $_SESSION['usuario_id'] = $row['id_usuario'];
                $_SESSION['usuario_nombre'] = $row['nombre'];
                $_SESSION['usuario_rol'] = $row['rol'];
                
                // --- REDIRECCIÓN INTELIGENTE ---
                if($row['rol'] === 'Cliente_B2B') {
                    // Si es el cliente, se va a su tienda
                    header("Location: ../views/tienda_b2b.php");
                } else {
                    // Si es Admin, Vendedor o Almacenero, se van al panel de control
                    header("Location: ../views/dashboard.php");
                }
                exit();
                
            } else {
                header("Location: ../index.php?error=credenciales");
                exit();
            }
        } else {
            header("Location: ../index.php?error=credenciales");
            exit();
        }

    } catch(PDOException $e) {
        die("Error de Base de Datos: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>