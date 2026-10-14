<?php
// Archivo: controllers/logoutController.php
session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la sesión completamente
session_destroy();

// Redirigir al login
header("Location: ../index.php");
exit();
?>