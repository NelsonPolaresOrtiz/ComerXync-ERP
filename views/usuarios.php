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

// Métricas de resumen de cuentas
$totalUsuarios = count($usuarios);
$totalAdmins = 0;
$totalOperadores = 0;
$totalClientes = 0;
foreach($usuarios as $u) {
    if($u['rol'] === 'Administrador') $totalAdmins++;
    elseif($u['rol'] === 'Cliente_B2B') $totalClientes++;
    else $totalOperadores++;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Accesos (RBAC) - ComerXync AutoParts</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }

        /* Glassmorphism Vercel/Stripe style */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .glass-card:hover {
            box-shadow: 0 12px 30px -4px rgba(15, 23, 42, 0.08), 0 4px 12px -2px rgba(15, 23, 42, 0.04);
            border-color: rgba(203, 213, 225, 1);
        }

        /* Subtle scrollbar */
        .scrollbar-saas::-webkit-scrollbar { width: 5px; height: 5px; }
        .scrollbar-saas::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-saas::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        .scrollbar-saas::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 flex h-screen overflow-hidden antialiased">

    <!-- TOAST NOTIFICATION FLOTANTE (Auto-dismiss en 3 segundos) -->
    <?php if(isset($_GET['exito']) && ($_GET['exito'] == 'creado' || $_GET['exito'] == 'eliminado')): ?>
    <div id="toast-notification" class="fixed top-6 right-6 z-50 flex items-center gap-3.5 px-5 py-4 bg-white/95 backdrop-blur-xl border <?php echo $_GET['exito'] == 'creado' ? 'border-emerald-200/90 text-slate-800 shadow-[0_12px_36px_-6px_rgba(16,185,129,0.3)]' : 'border-rose-200/90 text-slate-800 shadow-[0_12px_36px_-6px_rgba(244,63,94,0.3)]'; ?> rounded-2xl transition-all duration-500 transform translate-y-0 opacity-100">
        <div class="w-9 h-9 rounded-xl <?php echo $_GET['exito'] == 'creado' ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-600' : 'bg-rose-500/10 border border-rose-500/20 text-rose-600'; ?> flex items-center justify-center shrink-0">
            <?php if($_GET['exito'] == 'creado'): ?>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <?php else: ?>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            <?php endif; ?>
        </div>
        <div>
            <p class="text-[11px] font-black uppercase tracking-wider <?php echo $_GET['exito'] == 'creado' ? 'text-emerald-600' : 'text-rose-600'; ?>">
                <?php echo $_GET['exito'] == 'creado' ? 'Acceso Habilitado' : 'Acceso Revocado'; ?>
            </p>
            <p class="text-xs font-semibold text-slate-700">
                <?php echo $_GET['exito'] == 'creado' ? 'Usuario registrado y credenciales activadas en el sistema.' : 'El usuario y sus credenciales fueron eliminados exitosamente.'; ?>
            </p>
        </div>
        <button onclick="dismissToast()" class="ml-2 text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <script>
        setTimeout(() => { dismissToast(); }, 3000);
        function dismissToast() {
            const toast = document.getElementById('toast-notification');
            if(toast) {
                toast.classList.add('opacity-0', '-translate-y-4', 'scale-95');
                setTimeout(() => toast.remove(), 400);
            }
        }
    </script>
    <?php endif; ?>

    <!-- SIDEBAR MODULARIZADO (Menú Maestro) -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-1 flex flex-col overflow-hidden bg-[#f8fafc]">

        <!-- ═══════════════ HEADER SUPERIOR SAAS ═══════════════ -->
        <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-8 flex items-center justify-between shrink-0 z-10">
            <!-- Left: Título y descripción -->
            <div class="flex items-center gap-3.5">
                <div class="w-9 h-9 rounded-xl bg-violet-600/10 border border-violet-600/20 flex items-center justify-center text-violet-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">Control de Usuarios y Roles (RBAC)</h1>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Gestión de identidades, credenciales y niveles de autorización corporativa.</p>
                </div>
            </div>

            <!-- Right: Indicador de sesión activa -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2.5 px-4 py-2 bg-slate-100/80 border border-slate-200/80 rounded-xl">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-semibold text-slate-700">
                        Admin: <strong class="text-slate-900 font-bold"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong>
                    </span>
                </div>
            </div>
        </header>

        <!-- ═══════════════ ÁREA DE TRABAJO SCROLLABLE ═══════════════ -->
        <div class="flex-1 overflow-y-auto px-8 py-7 space-y-6 scrollbar-saas">

            <!-- ── SECCIÓN 1: TARJETAS KPI RESUMEN DE ACCESOS ── -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Total Usuarios -->
                <div class="glass-card rounded-2xl p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Total Cuentas</p>
                        <p class="text-2xl font-black text-slate-900 leading-tight mt-0.5"><?php echo $totalUsuarios; ?></p>
                        <p class="text-[10px] text-slate-400 font-medium">Usuarios registrados</p>
                    </div>
                </div>

                <!-- Administradores -->
                <div class="glass-card rounded-2xl p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Administradores</p>
                        <p class="text-2xl font-black text-purple-600 leading-tight mt-0.5"><?php echo $totalAdmins; ?></p>
                        <p class="text-[10px] text-purple-600 font-medium">Control total RBAC</p>
                    </div>
                </div>

                <!-- Operadores (Vendedor / Almacén) -->
                <div class="glass-card rounded-2xl p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Operadores</p>
                        <p class="text-2xl font-black text-blue-600 leading-tight mt-0.5"><?php echo $totalOperadores; ?></p>
                        <p class="text-[10px] text-blue-600 font-medium">Caja, POS y Almacén</p>
                    </div>
                </div>

                <!-- Clientes B2B -->
                <div class="glass-card rounded-2xl p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Talleres B2B</p>
                        <p class="text-2xl font-black text-amber-600 leading-tight mt-0.5"><?php echo $totalClientes; ?></p>
                        <p class="text-[10px] text-amber-600 font-medium">Portal mayorista</p>
                    </div>
                </div>

            </div>

            <!-- ── SECCIÓN 2: FORMULARIO Y TABLA ── -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-7 items-start">
                
                <!-- Formulario de Registro con Floating Labels -->
                <div class="glass-card rounded-2xl overflow-hidden border border-slate-200/80">
                    <div class="px-7 py-4 border-b border-slate-100 bg-white/50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-4 rounded-full bg-blue-600"></span>
                            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">Crear Nueva Cuenta</h2>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 font-mono">RBAC</span>
                    </div>

                    <div class="p-7">
                        <form action="usuarios.php" method="POST" class="space-y-5">
                            <input type="hidden" name="accion" value="crear">

                            <!-- Nombre con Floating Label -->
                            <div class="relative group">
                                <input type="text" name="nombre" id="input_nombre" required placeholder=" "
                                       class="peer w-full px-4 pt-5 pb-2 bg-white/90 border border-slate-200/90 rounded-xl text-sm font-semibold text-slate-800 placeholder-transparent focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:shadow-[0_0_20px_rgba(59,130,246,0.15)] transition-all duration-200">
                                <label for="input_nombre"
                                       class="absolute left-4 top-1.5 text-[9px] font-bold text-slate-400 uppercase tracking-wider transition-all duration-200 pointer-events-none peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-xs peer-placeholder-shown:font-medium peer-placeholder-shown:text-slate-400 peer-placeholder-shown:normal-case peer-focus:top-1.5 peer-focus:text-[9px] peer-focus:font-bold peer-focus:text-blue-600 peer-focus:uppercase peer-focus:tracking-wider">
                                    Nombre Completo / Razón Social
                                </label>
                            </div>

                            <!-- Email con Floating Label -->
                            <div class="relative group">
                                <input type="email" name="email" id="input_email" required placeholder=" "
                                       class="peer w-full px-4 pt-5 pb-2 bg-white/90 border border-slate-200/90 rounded-xl text-sm font-semibold text-slate-800 placeholder-transparent focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:shadow-[0_0_20px_rgba(59,130,246,0.15)] transition-all duration-200">
                                <label for="input_email"
                                       class="absolute left-4 top-1.5 text-[9px] font-bold text-slate-400 uppercase tracking-wider transition-all duration-200 pointer-events-none peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-xs peer-placeholder-shown:font-medium peer-placeholder-shown:text-slate-400 peer-placeholder-shown:normal-case peer-focus:top-1.5 peer-focus:text-[9px] peer-focus:font-bold peer-focus:text-blue-600 peer-focus:uppercase peer-focus:tracking-wider">
                                    Correo Electrónico (Login)
                                </label>
                            </div>

                            <!-- Contraseña con Floating Label -->
                            <div class="relative group">
                                <input type="text" name="password" id="input_password" required value="123456" placeholder=" "
                                       class="peer w-full px-4 pt-5 pb-2 bg-slate-50 border border-slate-200/90 rounded-xl text-sm font-mono font-bold text-slate-800 placeholder-transparent focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:shadow-[0_0_20px_rgba(59,130,246,0.15)] transition-all duration-200">
                                <label for="input_password"
                                       class="absolute left-4 top-1.5 text-[9px] font-bold text-slate-400 uppercase tracking-wider transition-all duration-200 pointer-events-none peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-xs peer-placeholder-shown:font-medium peer-placeholder-shown:text-slate-400 peer-placeholder-shown:normal-case peer-focus:top-1.5 peer-focus:text-[9px] peer-focus:font-bold peer-focus:text-blue-600 peer-focus:uppercase peer-focus:tracking-wider">
                                    Contraseña Inicial
                                </label>
                            </div>

                            <!-- Selector de Rol con Floating Label -->
                            <div class="relative group">
                                <select name="rol" id="select_rol" required
                                        class="peer w-full px-4 pt-5 pb-2.5 bg-white/90 border border-slate-200/90 rounded-xl text-sm font-semibold text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:shadow-[0_0_20px_rgba(59,130,246,0.15)] transition-all duration-200 appearance-none cursor-pointer">
                                    <option value="Cliente_B2B">Cliente B2B (Taller Mayorista)</option>
                                    <option value="Vendedor">Vendedor (Punto de Venta POS)</option>
                                    <option value="Almacenero">Almacenero (Kardex e Ingresos)</option>
                                    <option value="Administrador">Administrador (Acceso Total)</option>
                                </select>
                                <label for="select_rol"
                                       class="absolute left-4 top-1.5 text-[9px] font-bold text-slate-400 uppercase tracking-wider transition-all duration-200 pointer-events-none peer-focus:text-blue-600">
                                    Rol y Nivel de Privilegios
                                </label>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>

                            <!-- Botón de Envío -->
                            <button type="submit" 
                                    class="w-full py-3 bg-slate-900 hover:bg-blue-600 active:scale-95 text-white font-bold rounded-xl shadow-sm hover:shadow-md transition-all duration-200 text-xs flex items-center justify-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Registrar y Activar Cuenta
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tabla de Usuarios -->
                <div class="lg:col-span-2 glass-card rounded-2xl overflow-hidden border border-slate-200/80">
                    <div class="px-7 py-4 border-b border-slate-100 bg-white/50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-4 rounded-full bg-emerald-500"></span>
                            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">Directorio de Credenciales Activas</h2>
                        </div>
                        <span class="text-xs text-slate-400 font-semibold"><?php echo $totalUsuarios; ?> cuentas registradas</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-200/80 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
                                    <th class="px-7 py-3.5">ID</th>
                                    <th class="px-7 py-3.5">Usuario / Identidad</th>
                                    <th class="px-7 py-3.5">Rol Asignado</th>
                                    <th class="px-7 py-3.5 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                <?php foreach($usuarios as $u): ?>
                                <?php 
                                    // Mapeo visual de roles
                                    $rolColors = [
                                        'Administrador' => ['badge' => 'bg-purple-50 text-purple-700 border-purple-200/90', 'dot' => 'bg-purple-500'],
                                        'Cliente_B2B'   => ['badge' => 'bg-amber-50 text-amber-700 border-amber-200/90',    'dot' => 'bg-amber-500'],
                                        'Vendedor'      => ['badge' => 'bg-blue-50 text-blue-700 border-blue-200/90',       'dot' => 'bg-blue-500'],
                                        'Almacenero'    => ['badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200/90', 'dot' => 'bg-emerald-500']
                                    ];
                                    $r = $rolColors[$u['rol']] ?? ['badge' => 'bg-slate-100 text-slate-700 border-slate-200', 'dot' => 'bg-slate-400'];
                                    $esSesionActual = ($u['id_usuario'] == $_SESSION['usuario_id']);
                                ?>
                                <tr class="hover:bg-slate-50/80 transition-colors duration-150 group">
                                    <td class="px-7 py-4 font-mono font-bold text-slate-400">
                                        #<?php echo $u['id_usuario']; ?>
                                    </td>
                                    <td class="px-7 py-4">
                                        <p class="text-sm font-bold text-slate-800 leading-snug group-hover:text-blue-600 transition-colors">
                                            <?php echo htmlspecialchars($u['nombre']); ?>
                                        </p>
                                        <p class="text-[11px] text-slate-400 font-mono mt-0.5">
                                            <?php echo htmlspecialchars($u['email']); ?>
                                        </p>
                                    </td>
                                    <td class="px-7 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border <?php echo $r['badge']; ?>">
                                            <span class="w-1.5 h-1.5 rounded-full <?php echo $r['dot']; ?>"></span>
                                            <?php echo htmlspecialchars($u['rol']); ?>
                                        </span>
                                    </td>
                                    <td class="px-7 py-4 text-center">
                                        <?php if(!$esSesionActual): ?>
                                            <div class="inline-flex items-center p-1 bg-slate-100/80 group-hover:bg-slate-200/70 rounded-xl border border-slate-200/80 transition-all">
                                                <a href="usuarios.php?eliminar=<?php echo $u['id_usuario']; ?>" 
                                                   onclick="return confirm('¿Revocar acceso y eliminar permanentemente a este usuario?');" 
                                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-white hover:shadow-xs transition-all"
                                                   title="Revocar acceso">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                Sesión Activa
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer -->
                    <div class="px-7 py-3.5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <p class="text-xs text-slate-400 font-medium">
                            Seguridad RBAC habilitada &bull; Permisos jerárquicos por módulo
                        </p>
                        <p class="text-xs text-slate-400 font-medium hidden sm:block">
                            Protegido contra auto-eliminación de cuenta activa
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </main>
</body>
</html>
