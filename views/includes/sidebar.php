<?php
// Archivo: views/includes/sidebar.php
$page = basename($_SERVER['PHP_SELF']);

// Helper: determina si el ítem está activo
function isActive(string $page, array $pages): bool {
    return in_array($page, $pages);
}
?>
<style>
    /* ── Sidebar scrollbar ultra-thin ── */
    #sidebar-nav::-webkit-scrollbar { width: 3px; }
    #sidebar-nav::-webkit-scrollbar-track { background: transparent; }
    #sidebar-nav::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 99px; }

    /* ── Nav item base ── */
    .nav-item {
        position: relative;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
        text-decoration: none;
        transition: color 160ms ease, background 160ms ease;
        letter-spacing: 0.01em;
    }
    .nav-item:hover {
        color: #cbd5e1;
        background: rgba(255,255,255,0.04);
    }
    /* Indicador lateral activo */
    .nav-item.active {
        color: #f8fafc;
        background: rgba(255,255,255,0.06);
        font-weight: 600;
    }
    .nav-item.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 3px;
        height: 18px;
        background: #3b82f6;
        border-radius: 0 3px 3px 0;
    }
    /* Ítem con acento de color propio (mantiene color del grupo) */
    .nav-item.active-emerald::before { background: #10b981; }
    .nav-item.active-blue::before    { background: #3b82f6; }
    .nav-item.active-amber::before   { background: #f59e0b; }
    .nav-item.active-slate::before   { background: #94a3b8; }

    /* Icono SVG dentro del nav */
    .nav-icon {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        opacity: 0.6;
        transition: opacity 160ms ease;
    }
    .nav-item:hover .nav-icon,
    .nav-item.active .nav-icon {
        opacity: 1;
    }

    /* Avatar inicial */
    .user-avatar {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        background: #1e293b;
        border: 1px solid #334155;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        color: #94a3b8;
        flex-shrink: 0;
        letter-spacing: 0;
    }
</style>

<aside class="w-[228px] bg-[#080c14] flex flex-col h-screen shrink-0 border-r border-[#0f172a] print:hidden select-none">

    <!-- ══ LOGOTIPO ══ -->
    <div class="h-[60px] flex items-center px-5 border-b border-[#0f172a] shrink-0">
        <div class="flex items-center gap-2.5">
            <!-- Icono minimalista -->
            <div class="w-7 h-7 rounded-[7px] bg-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
            </div>
            <div class="leading-tight">
                <p class="text-[13.5px] font-bold text-slate-100 tracking-tight leading-none">
                    Comer<span class="text-blue-400">Xync</span>
                </p>
                <p class="text-[9px] text-slate-600 font-medium tracking-[0.12em] uppercase mt-0.5">Enterprise ERP</p>
            </div>
        </div>
    </div>

    <!-- ══ NAVEGACIÓN ══ -->
    <div id="sidebar-nav" class="flex-1 overflow-y-auto px-3 py-5 space-y-5">

        <!-- ─ PRINCIPAL ─ -->
        <div>
            <p class="px-3 mb-1.5 text-[9.5px] font-semibold text-slate-600 uppercase tracking-[0.14em]">Principal</p>
            <nav class="space-y-0.5">
                <a href="dashboard.php"
                   class="nav-item <?php echo isActive($page, ['dashboard.php']) ? 'active active-blue' : ''; ?>">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10-3a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/>
                    </svg>
                    Dashboard
                </a>
            </nav>
        </div>

        <!-- ─ INVENTARIO ─ -->
        <div>
            <p class="px-3 mb-1.5 text-[9.5px] font-semibold text-slate-600 uppercase tracking-[0.14em]">Inventario</p>
            <nav class="space-y-0.5">
                <a href="articulos.php"
                   class="nav-item <?php echo isActive($page, ['articulos.php','nuevo_articulo.php','editar_articulo.php']) ? 'active active-slate' : ''; ?>">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Catálogo de Artículos
                </a>
                <a href="proveedores.php"
                   class="nav-item <?php echo isActive($page, ['proveedores.php','nuevo_proveedor.php']) ? 'active active-slate' : ''; ?>">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Proveedores
                </a>
            </nav>
        </div>

        <!-- ─ OPERACIONES ─ -->
        <div>
            <p class="px-3 mb-1.5 text-[9.5px] font-semibold text-slate-600 uppercase tracking-[0.14em]">Operaciones</p>
            <nav class="space-y-0.5">
                <a href="nuevo_ingreso.php"
                   class="nav-item <?php echo isActive($page, ['nuevo_ingreso.php']) ? 'active active-emerald' : ''; ?>">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 8v4m0 0l4-4m-4 4l-4-4"/>
                    </svg>
                    Registrar Ingreso
                </a>
                <a href="nueva_venta.php"
                   class="nav-item <?php echo isActive($page, ['nueva_venta.php']) ? 'active active-blue' : ''; ?>">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Punto de Venta
                </a>
                <a href="kardex.php"
                   class="nav-item <?php echo isActive($page, ['kardex.php']) ? 'active active-slate' : ''; ?>">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Libro Mayor Kardex
                </a>
                <a href="gestionar_pedidos.php"
                   class="nav-item <?php echo isActive($page, ['gestionar_pedidos.php']) ? 'active active-amber' : ''; ?>">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    Pedidos B2B
                </a>
            </nav>
        </div>

        <!-- ─ SISTEMA ─ -->
        <div>
            <p class="px-3 mb-1.5 text-[9.5px] font-semibold text-slate-600 uppercase tracking-[0.14em]">Sistema</p>
            <nav class="space-y-0.5">
                <a href="reportes.php"
                   class="nav-item <?php echo isActive($page, ['reportes.php']) ? 'active active-slate' : ''; ?>">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Reportes
                </a>
                <a href="usuarios.php"
                   class="nav-item <?php echo isActive($page, ['usuarios.php']) ? 'active active-slate' : ''; ?>">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Accesos y Roles
                </a>
            </nav>
        </div>

    </div>

    <!-- ══ PERFIL DE USUARIO & LOGOUT ══ -->
    <div class="px-3 py-3 border-t border-[#0f172a] shrink-0">
        <!-- Info del usuario -->
        <div class="flex items-center gap-2.5 px-2 py-2 rounded-lg mb-1">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['usuario_nombre'], 0, 1)); ?>
            </div>
            <div class="overflow-hidden flex-1 min-w-0">
                <p class="text-[12px] font-semibold text-slate-300 truncate leading-tight">
                    <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                </p>
                <p class="text-[10px] text-slate-600 truncate mt-0.5 leading-tight">
                    <?php echo htmlspecialchars($_SESSION['usuario_rol']); ?>
                </p>
            </div>
        </div>

        <!-- Botón logout minimalista -->
        <a href="../controllers/logoutController.php"
           class="flex items-center gap-2 w-full px-3 py-2 rounded-lg text-[12px] font-medium text-slate-600 hover:text-red-400 hover:bg-red-500/5 transition-all duration-150 group">
            <svg class="w-3.5 h-3.5 opacity-60 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Cerrar sesión
        </a>
    </div>

</aside>
