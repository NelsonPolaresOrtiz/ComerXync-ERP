<?php
// Archivo: views/usuarios.php
session_start();
if(!isset($_SESSION['usuario_id'])){ header("Location: ../index.php?error=nologin"); exit(); }
require_once '../config/conexion.php';

$database = new Conexion();
$db = $database->getConnection();
$mensaje = "";

// 1. CREAR NUEVO USUARIO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'crear') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $rol = $_POST['rol'];

    $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :password, :rol)");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':rol', $rol);
    
    if ($stmt->execute()) {
        header("Location: usuarios.php?exito=creado");
        exit();
    }
}

// 2. ELIMINAR USUARIO
if (isset($_GET['eliminar'])) {
    $id_borrar = intval($_GET['eliminar']);
    if ($id_borrar != $_SESSION['usuario_id']) { // Evita que el admin se borre a sí mismo
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id_usuario = :id");
        $stmt->bindParam(':id', $id_borrar);
        $stmt->execute();
        header("Location: usuarios.php?exito=eliminado");
        exit();
    }
}

// 3. LISTAR USUARIOS
$stmt = $db->query("SELECT * FROM usuarios ORDER BY id_usuario ASC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - ComerXync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 shrink-0 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="bg-slate-100 text-slate-600 p-2 rounded-lg">👥</div>
                <h1 class="text-xl font-bold text-slate-800">Control de Usuarios y Roles (RBAC)</h1>
            </div>
        </header>

        <div class="p-8 flex-1 overflow-auto">
            <?php if(isset($_GET['exito']) && $_GET['exito'] == 'creado'): ?>
                <div class="mb-6 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg">✅ Usuario registrado y credenciales activadas.</div>
            <?php endif; ?>
            <?php if(isset($_GET['exito']) && $_GET['exito'] == 'eliminado'): ?>
                <div class="mb-6 bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg">🗑️ Usuario eliminado del sistema.</div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Formulario de Nuevo Usuario -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 h-fit">
                    <h2 class="text-lg font-bold text-slate-800 mb-4 border-b pb-3">Registrar Nuevo Acceso</h2>
                    <form action="usuarios.php" method="POST" class="space-y-4">
                        <input type="hidden" name="accion" value="crear">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nombre Completo / Razón Social Taller</label>
                            <input type="text" name="nombre" required placeholder="Ej: Taller Automotriz Sucre" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Correo Electrónico (Login)</label>
                            <input type="email" name="email" required placeholder="correo@empresa.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Contraseña</label>
                            <input type="text" name="password" required value="123456" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none font-mono bg-gray-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Rol en el Sistema</label>
                            <select name="rol" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white font-medium">
                                <option value="Cliente_B2B">Cliente B2B (Taller Mayorista)</option>
                                <option value="Vendedor">Vendedor (Punto de Venta POS)</option>
                                <option value="Almacenero">Almacenero (Kardex e Ingresos)</option>
                                <option value="Administrador">Administrador (Acceso Total)</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors text-sm">
                            + Crear Usuario
                        </button>
                    </form>
                </div>

                <!-- Tabla de Usuarios -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden h-fit">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-gray-200 text-xs uppercase text-gray-500 font-bold">
                                <th class="px-6 py-4">ID</th>
                                <th class="px-6 py-4">Usuario / Entidad</th>
                                <th class="px-6 py-4">Rol Asignado</th>
                                <th class="px-6 py-4 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach($usuarios as $u): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm text-gray-500 font-bold">#<?php echo $u['id_usuario']; ?></td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($u['nombre']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($u['email']); ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold 
                                        <?php 
                                            if($u['rol'] == 'Administrador') echo 'bg-purple-100 text-purple-700';
                                            elseif($u['rol'] == 'Cliente_B2B') echo 'bg-amber-100 text-amber-800';
                                            elseif($u['rol'] == 'Vendedor') echo 'bg-blue-100 text-blue-700';
                                            else echo 'bg-emerald-100 text-emerald-700';
                                        ?>">
                                        <?php echo htmlspecialchars($u['rol']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if($u['id_usuario'] != $_SESSION['usuario_id']): ?>
                                        <a href="usuarios.php?eliminar=<?php echo $u['id_usuario']; ?>" onclick="return confirm('¿Revocar acceso a este usuario?');" class="text-red-500 hover:text-red-700 text-xs font-bold bg-red-50 px-3 py-1.5 rounded-lg">Eliminar</a>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 italic">Sesión actual</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>