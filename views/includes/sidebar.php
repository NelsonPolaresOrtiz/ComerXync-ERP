<?php
// Archivo: views/includes/sidebar.php
// Obtenemos el nombre del archivo actual para saber qué botón pintar de azul
$page = basename($_SERVER['PHP_SELF']);
?>
<!-- SIDEBAR (Navegación Modular Unificada) -->
<aside class="w-64 bg-slate-900 text-gray-300 flex flex-col transition-all duration-300 h-screen shrink-0">
    <div class="h-16 flex items-center justify-center border-b border-slate-800 bg-slate-950 shrink-0">
        <h2 class="text-2xl font-black text-white tracking-tight">Comer<span class="text-amber-500">Xync</span></h2>
    </div>
    
    <div class="flex-1 overflow-y-auto py-4">
        <nav class="px-4 space-y-1">
            <a href="dashboard.php" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-colors <?php echo ($page == 'dashboard.php') ? 'bg-blue-600 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white'; ?>">📊 Dashboard Principal</a>
            
            <div class="pt-6 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Gestión de Catálogo</div>
            <a href="articulos.php" class="block px-4 py-2 rounded-lg text-sm transition-colors <?php echo ($page == 'articulos.php' || $page == 'nuevo_articulo.php' || $page == 'editar_articulo.php') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 hover:text-white'; ?>">⚙️ Repuestos y Vehículos</a>
            <a href="proveedores.php" class="block px-4 py-2 rounded-lg text-sm transition-colors <?php echo ($page == 'proveedores.php' || $page == 'nuevo_proveedor.php') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 hover:text-white'; ?>">🏢 Proveedores (Origen)</a>

            <div class="pt-6 pb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Operaciones de Kardex</div>
            <a href="nuevo_ingreso.php" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-colors border border-transparent <?php echo ($page == 'nuevo_ingreso.php') ? 'bg-slate-800 text-green-400 border-slate-700' : 'text-green-400 hover:bg-slate-800 hover:text-green-300 hover:border-slate-700'; ?>">📥 Registrar Ingreso (Compra)</a>
            <a href="nueva_venta.php" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-colors border border-transparent mt-2 <?php echo ($page == 'nueva_venta.php') ? 'bg-slate-800 text-blue-400 border-slate-700' : 'text-blue-400 hover:bg-slate-800 hover:text-blue-300 hover:border-slate-700'; ?>">🛒 Punto de Venta (Salida)</a>
            <a href="kardex.php" class="block px-4 py-2 rounded-lg text-sm transition-colors mt-2 <?php echo ($page == 'kardex.php') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 hover:text-white'; ?>">📋 Historial Kardex</a>
            
            <!-- NUEVO BOTÓN: GESTIÓN DE PEDIDOS WEB -->
            <a href="gestionar_pedidos.php" class="block px-4 py-2 rounded-lg text-sm transition-colors mt-2 <?php echo ($page == 'gestionar_pedidos.php') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 hover:text-white'; ?>">📦 Gestión de Pedidos Web</a>
        </nav>
    </div>

    <div class="p-4 border-t border-slate-800 bg-slate-950 shrink-0">
        <div class="flex items-center mb-3 px-2">
            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-white font-bold mr-3">
                <?php echo substr($_SESSION['usuario_nombre'], 0, 1); ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-medium text-white truncate" title="<?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></p>
                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($_SESSION['usuario_rol']); ?></p>
            </div>
        </div>
        <a href="../controllers/logoutController.php" class="block w-full text-center px-4 py-2 bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white rounded-lg text-sm font-medium transition-colors">Cerrar Sesión</a>
    </div>
</aside>