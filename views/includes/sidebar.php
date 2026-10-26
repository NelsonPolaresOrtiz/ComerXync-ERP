<?php
// Archivo: views/includes/sidebar.php
$page = basename($_SERVER['PHP_SELF']);
?>
<!-- SIDEBAR CORPORATIVO MODERNO -->
<aside class="w-70 bg-slate-950 text-slate-300 flex flex-col transition-all duration-300 h-screen shrink-0 border-r border-slate-900 shadow-2xl print:hidden">
    
    <!-- Logo con Efecto Luminoso -->
    <div class="h-20 flex items-center px-6 border-b border-slate-900/80 bg-slate-950 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white font-black text-lg shadow-[0_0_15px_rgba(37,99,235,0.4)]">
                ⚙️
            </div>
            <div>
                <h2 class="text-xl font-black text-white tracking-tight">Comer<span class="text-amber-500">Xync</span></h2>
                <p class="text-[10px] text-slate-500 font-semibold tracking-wider uppercase">Enterprise ERP</p>
            </div>
        </div>
    </div>
    
    <!-- Navegación Desplazable Estilizada -->
    <div class="flex-1 overflow-y-auto px-4 py-6 space-y-6 scrollbar-thin scrollbar-thumb-slate-800">
        
        <!-- Bloque 1: General -->
        <div>
            <p class="px-3 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2">Principal</p>
            <nav class="space-y-1">
                <a href="dashboard.php" class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-medium transition-all duration-200 <?php echo ($page == 'dashboard.php') ? 'bg-blue-600 text-white shadow-[0_0_20px_rgba(37,99,235,0.4)] font-semibold' : 'text-slate-400 hover:bg-slate-900 hover:text-white'; ?>">
                    <span class="text-base">📊</span> Dashboard Analítico
                </a>
            </nav>
        </div>

        <!-- Bloque 2: Catálogo -->
        <div>
            <p class="px-3 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2">Inventario & Catálogo</p>
            <nav class="space-y-1">
                <a href="articulos.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?php echo ($page == 'articulos.php' || $page == 'nuevo_articulo.php' || $page == 'editar_articulo.php') ? 'bg-slate-900 text-white border border-slate-800 font-semibold' : 'text-slate-400 hover:bg-slate-900 hover:text-white'; ?>">
                    <span class="text-base">⚙️</span> Repuestos y Vehículos
                </a>
                <a href="proveedores.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?php echo ($page == 'proveedores.php' || $page == 'nuevo_proveedor.php') ? 'bg-slate-900 text-white border border-slate-800 font-semibold' : 'text-slate-400 hover:bg-slate-900 hover:text-white'; ?>">
                    <span class="text-base">🏢</span> Proveedores Globales
                </a>
            </nav>
        </div>

        <!-- Bloque 3: Operaciones -->
        <div>
            <p class="px-3 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2">Operaciones & Logística</p>
            <nav class="space-y-1.5">
                <a href="nuevo_ingreso.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?php echo ($page == 'nuevo_ingreso.php') ? 'bg-slate-900 text-emerald-400 border border-slate-800 font-semibold' : 'text-emerald-400/90 hover:bg-slate-900 hover:text-emerald-300'; ?>">
                    <span class="text-base">📥</span> Registrar Compra (Ingreso)
                </a>
                <a href="nueva_venta.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?php echo ($page == 'nueva_venta.php') ? 'bg-slate-900 text-blue-400 border border-slate-800 font-semibold' : 'text-blue-400/90 hover:bg-slate-900 hover:text-blue-300'; ?>">
                    <span class="text-base">🛒</span> Punto de Venta (POS)
                </a>
                <a href="kardex.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?php echo ($page == 'kardex.php') ? 'bg-slate-900 text-white border border-slate-800 font-semibold' : 'text-slate-400 hover:bg-slate-900 hover:text-white'; ?>">
                    <span class="text-base">📋</span> Libro Mayor Kardex
                </a>
                <a href="gestionar_pedidos.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?php echo ($page == 'gestionar_pedidos.php') ? 'bg-slate-900 text-amber-400 border border-slate-800 font-semibold' : 'text-amber-400/90 hover:bg-slate-900 hover:text-amber-300'; ?>">
                    <span class="text-base">📦</span> Bandeja Pedidos B2B
                </a>
            </nav>
        </div>

        <!-- Bloque 4: Administración -->
        <div>
            <p class="px-3 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-2">Sistema & BI</p>
            <nav class="space-y-1.5">
                <a href="reportes.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?php echo ($page == 'reportes.php') ? 'bg-slate-900 text-white border border-slate-800 font-semibold' : 'text-slate-400 hover:bg-slate-900 hover:text-white'; ?>">
                    <span class="text-base">📄</span> Reportes e Inventario PDF
                </a>
                <a href="usuarios.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?php echo ($page == 'usuarios.php') ? 'bg-slate-900 text-white border border-slate-800 font-semibold' : 'text-slate-400 hover:bg-slate-900 hover:text-white'; ?>">
                    <span class="text-base">👥</span> Control de Accesos (RBAC)
                </a>
            </nav>
        </div>
    </div>

    <!-- Perfil del Usuario Activo & Logout -->
    <div class="p-4 border-t border-slate-900 bg-slate-950 shrink-0">
        <div class="flex items-center gap-3 mb-3 px-2">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-slate-800 to-slate-700 flex items-center justify-center text-white font-bold text-sm border border-slate-700">
                <?php echo substr($_SESSION['usuario_nombre'], 0, 1); ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-bold text-white truncate"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></p>
                <p class="text-[10px] text-slate-400 font-medium"><?php echo htmlspecialchars($_SESSION['usuario_rol']); ?></p>
            </div>
        </div>
        <a href="../controllers/logoutController.php" class="flex items-center justify-center gap-2 w-full py-2.5 bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white rounded-xl text-xs font-bold transition-all duration-200 border border-red-500/20">
            Cerrar Sesión
        </a>
    </div>
</aside>