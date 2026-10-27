<?php
// Archivo: views/dashboard.php
session_start();
if(!isset($_SESSION['usuario_id'])){ header("Location: ../index.php?error=nologin"); exit(); }
require_once '../config/conexion.php';

try {
    $database = new Conexion();
    $db = $database->getConnection();

    // 1. Total Artículos
    $stmt = $db->query("SELECT COUNT(*) as total FROM articulos");
    $total_articulos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 2. Total Proveedores
    $stmt = $db->query("SELECT COUNT(*) as total FROM proveedores");
    $total_proveedores = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 3. Alertas de Stock Bajo
    $query_stock = "SELECT a.nombre, a.sku_vin, a.stock_minimo, a.categoria,
              COALESCE((SELECT saldo_actual FROM kardex k WHERE k.id_articulo = a.id_articulo ORDER BY id_movimiento DESC LIMIT 1), 0) as stock_actual
              FROM articulos a 
              HAVING stock_actual <= a.stock_minimo";
    $stmt_stock = $db->query($query_stock);
    $alertas_stock = $stmt_stock->fetchAll(PDO::FETCH_ASSOC);
    $total_alertas = count($alertas_stock);

    // 4. Stock Físico Total
    $query_suma = "SELECT SUM(stock_actual) as suma_total FROM (SELECT COALESCE((SELECT saldo_actual FROM kardex k WHERE k.id_articulo = a.id_articulo ORDER BY id_movimiento DESC LIMIT 1), 0) as stock_actual FROM articulos a) as inventario";
    $stmt_suma = $db->query($query_suma);
    $stock_total = $stmt_suma->fetch(PDO::FETCH_ASSOC)['suma_total'] ?? 0;

    // 5. Total Movimientos Kardex (ENTRADAS y SALIDAS)
    $stmt = $db->query("SELECT COUNT(*) as total, tipo_movimiento FROM kardex GROUP BY tipo_movimiento");
    $mov_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_entradas = 0; $total_salidas = 0;
    foreach($mov_raw as $m) {
        if($m['tipo_movimiento'] === 'ENTRADA') $total_entradas = (int)$m['total'];
        if($m['tipo_movimiento'] === 'SALIDA')  $total_salidas  = (int)$m['total'];
    }
    $total_movimientos = $total_entradas + $total_salidas;

    // 6. Distribución por Categoría
    $stmt = $db->query("SELECT categoria, COUNT(*) as cantidad FROM articulos GROUP BY categoria ORDER BY cantidad DESC");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 7. Últimos 5 movimientos del Kardex
    $stmt = $db->query("SELECT k.tipo_movimiento, k.cantidad, k.saldo_actual, k.referencia, k.fecha_movimiento,
                               a.nombre as articulo_nombre, a.sku_vin
                        FROM kardex k
                        INNER JOIN articulos a ON k.id_articulo = a.id_articulo
                        ORDER BY k.id_movimiento DESC LIMIT 5");
    $ultimos_movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 8. Valor estimado del inventario (suma precio_venta * stock_actual)
    $query_valor = "SELECT SUM(a.precio_venta * COALESCE((SELECT saldo_actual FROM kardex k WHERE k.id_articulo = a.id_articulo ORDER BY id_movimiento DESC LIMIT 1), 0)) as valor_total FROM articulos a";
    $stmt_val = $db->query($query_valor);
    $valor_inventario = $stmt_val->fetch(PDO::FETCH_ASSOC)['valor_total'] ?? 0;

    // 9. Pedidos pendientes
    $stmt = $db->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'Pendiente'");
    $pedidos_pendientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 10. Salud del inventario (% artículos sin alerta)
    $porc_saludable = $total_articulos > 0 ? round((($total_articulos - $total_alertas) / $total_articulos) * 100) : 100;

} catch(PDOException $e) {
    die("Error cargando el Dashboard: " . $e->getMessage());
}

// Mapa de colores por categoría
$cat_colors = [
    'Repuesto'   => ['bg' => 'bg-blue-500',   'light' => 'bg-blue-50',   'text' => 'text-blue-700',   'hex' => '#3b82f6'],
    'Accesorio'  => ['bg' => 'bg-violet-500',  'light' => 'bg-violet-50', 'text' => 'text-violet-700', 'hex' => '#8b5cf6'],
    'Maquinaria' => ['bg' => 'bg-amber-500',   'light' => 'bg-amber-50',  'text' => 'text-amber-700',  'hex' => '#f59e0b'],
    'Vehiculo'   => ['bg' => 'bg-emerald-500', 'light' => 'bg-emerald-50','text' => 'text-emerald-700','hex' => '#10b981'],
];

// Preparamos datos para Chart.js
$cat_labels = [];
$cat_data = [];
$cat_hex = [];
foreach($categorias as $cat) {
    $cat_labels[] = $cat['categoria'];
    $cat_data[] = (int)$cat['cantidad'];
    $cat_hex[] = $cat_colors[$cat['categoria']]['hex'] ?? '#64748b';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Gerencial - ComerXync AutoParts</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            transform: translateY(-2px);
            border-color: rgba(203, 213, 225, 1);
        }

        /* Subtle scrollbar */
        .scrollbar-saas::-webkit-scrollbar { width: 5px; height: 5px; }
        .scrollbar-saas::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-saas::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        .scrollbar-saas::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Animation utilities */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 flex h-screen overflow-hidden antialiased">

    <!-- TOAST NOTIFICATION FLOTANTE (Auto-dismiss en 3 segundos) -->
    <?php if(isset($_GET['exito']) && $_GET['exito'] == 'ingreso_ok'): ?>
    <div id="toast-notification" class="fixed top-6 right-6 z-50 flex items-center gap-3.5 px-5 py-4 bg-white/95 backdrop-blur-xl border border-emerald-200/90 text-slate-800 rounded-2xl shadow-[0_12px_36px_-6px_rgba(16,185,129,0.3)] transition-all duration-500 transform translate-y-0 opacity-100">
        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <p class="text-[11px] font-black uppercase tracking-wider text-emerald-600">Actualización Exitosa</p>
            <p class="text-xs font-semibold text-slate-700">Ingreso registrado en Kardex y stock recalculado.</p>
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

    <!-- SIDEBAR MODULARIZADO -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- MAIN CONTENT VIEWPORT -->
    <main class="flex-1 flex flex-col overflow-hidden bg-[#f8fafc]">
        
        <!-- ═══ HEADER SUPERIOR SAAS ═══ -->
        <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-8 flex items-center justify-between shrink-0 z-10">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">Panel de Control Gerencial</h1>
                </div>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Analítica operativa, métricas de inventario y estado en tiempo real.</p>
            </div>
            
            <div class="flex items-center gap-3.5">
                <?php if($pedidos_pendientes > 0): ?>
                <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-700 text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    <span><?php echo $pedidos_pendientes; ?> Pedido<?php echo $pedidos_pendientes > 1 ? 's' : ''; ?> Pendiente<?php echo $pedidos_pendientes > 1 ? 's' : ''; ?></span>
                </div>
                <?php endif; ?>

                <div class="flex items-center gap-2.5 px-4 py-2 bg-slate-100/80 border border-slate-200/90 rounded-xl">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs font-semibold text-slate-700">Sistema Conectado &bull; <?php echo date('d/m/Y H:i'); ?></span>
                </div>
            </div>
        </header>

        <!-- ═══ CONTENEDOR PRINCIPAL CON SCROLL ═══ -->
        <div class="p-8 flex-1 overflow-auto space-y-7 scrollbar-saas">

            <!-- ═══════════════════════════════════════════════════ -->
            <!--  SECCIÓN 1: 6 TARJETAS KPI (ESTILO SAAS STRIPE)     -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">

                <!-- KPI 1: Total Artículos -->
                <div class="glass-card rounded-2xl p-5 relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Catálogo Activo</span>
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-slate-900 tracking-tight"><?php echo $total_articulos; ?></p>
                    <p class="text-[11px] font-semibold text-blue-600 mt-1 flex items-center gap-1">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-blue-500"></span> SKUs registrados
                    </p>
                </div>

                <!-- KPI 2: Stock Físico -->
                <div class="glass-card rounded-2xl p-5 relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Volumen Físico</span>
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-slate-900 tracking-tight"><?php echo number_format($stock_total); ?></p>
                    <p class="text-[11px] font-semibold text-indigo-600 mt-1">Unidades en almacén</p>
                </div>

                <!-- KPI 3: Valoración Total -->
                <div class="glass-card rounded-2xl p-5 relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Valor Estimado</span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-xl font-black text-slate-900 tracking-tight truncate">S/ <?php echo number_format($valor_inventario, 0, '.', ','); ?></p>
                    <p class="text-[11px] font-semibold text-emerald-600 mt-1">Precio venta total</p>
                </div>

                <!-- KPI 4: Proveedores -->
                <div class="glass-card rounded-2xl p-5 relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Proveedores</span>
                        <div class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-slate-900 tracking-tight"><?php echo $total_proveedores; ?></p>
                    <p class="text-[11px] font-semibold text-cyan-600 mt-1">Socios homologados</p>
                </div>

                <!-- KPI 5: Movimientos Kardex -->
                <div class="glass-card rounded-2xl p-5 relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Transacciones</span>
                        <div class="w-8 h-8 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-slate-900 tracking-tight"><?php echo $total_movimientos; ?></p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-[11px] font-bold text-emerald-600">↑ <?php echo $total_entradas; ?></span>
                        <span class="text-[11px] font-bold text-rose-500">↓ <?php echo $total_salidas; ?></span>
                    </div>
                </div>

                <!-- KPI 6: Alertas Críticas -->
                <div class="<?php echo $total_alertas > 0 ? 'bg-gradient-to-br from-rose-600 to-red-600 text-white shadow-lg shadow-rose-500/20' : 'glass-card'; ?> rounded-2xl p-5 relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider <?php echo $total_alertas > 0 ? 'text-rose-100' : 'text-slate-400'; ?>">Stock Crítico</span>
                        <div class="w-8 h-8 rounded-lg <?php echo $total_alertas > 0 ? 'bg-white/20 text-white' : 'bg-rose-50 text-rose-600'; ?> flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-black tracking-tight <?php echo $total_alertas > 0 ? 'text-white' : 'text-slate-900'; ?>"><?php echo $total_alertas; ?></p>
                    <p class="text-[11px] font-semibold mt-1 <?php echo $total_alertas > 0 ? 'text-rose-100' : 'text-emerald-600'; ?>">
                        <?php echo $total_alertas > 0 ? 'Reposición requerida' : 'Inventario óptimo'; ?>
                    </p>
                </div>

            </div>

            <!-- ═══════════════════════════════════════════════════ -->
            <!--  SECCIÓN 2: GRÁFICOS INTERACTIVOS (CHART.JS CDN)    -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Gráfico 1 (Bar Chart): Balance Operativo Kardex -->
                <div class="glass-card lg:col-span-2 rounded-2xl p-6 flex flex-col justify-between">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide">Flujo Operativo de Almacén</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Comparativa volumétrica de compras (entradas) vs despachos (salidas).</p>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-semibold">
                            <span class="flex items-center gap-1.5 text-slate-600">
                                <span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span> Entradas
                            </span>
                            <span class="flex items-center gap-1.5 text-slate-600">
                                <span class="w-2.5 h-2.5 rounded-sm bg-rose-500"></span> Salidas
                            </span>
                        </div>
                    </div>
                    
                    <!-- Canvas Chart -->
                    <div class="relative w-full h-[240px]">
                        <canvas id="chartKardexFlow"></canvas>
                    </div>

                    <div class="pt-4 border-t border-slate-100 mt-4 flex items-center justify-between text-xs text-slate-500 font-medium">
                        <span>Total movimientos auditados: <strong class="text-slate-800"><?php echo $total_movimientos; ?></strong></span>
                        <a href="kardex.php" class="text-blue-600 font-bold hover:underline">Consultar Libro Kardex &rarr;</a>
                    </div>
                </div>

                <!-- Gráfico 2 (Doughnut Chart): Distribución por Categoría -->
                <div class="glass-card rounded-2xl p-6 flex flex-col justify-between">
                    <div class="pb-4 border-b border-slate-100 mb-4">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide">Composición por Categoría</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Porcentaje de SKUs activos en catálogo.</p>
                    </div>

                    <!-- Canvas Doughnut -->
                    <div class="relative w-full h-[210px] flex items-center justify-center">
                        <canvas id="chartCategories"></canvas>
                    </div>

                    <div class="pt-4 border-t border-slate-100 mt-4 flex items-center justify-between text-xs text-slate-500 font-medium">
                        <span>Total de categorías: <strong class="text-slate-800"><?php echo count($categorias); ?></strong></span>
                        <a href="articulos.php" class="text-blue-600 font-bold hover:underline">Ver catálogo &rarr;</a>
                    </div>
                </div>

            </div>

            <!-- ═══════════════════════════════════════════════════ -->
            <!--  SECCIÓN 3: SALUD DEL INVENTARIO Y RECIENTES        -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Salud del inventario (Ring circular) -->
                <div class="glass-card rounded-2xl p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide">Índice de Salud de Stock</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Porcentaje de artículos sin quiebre de stock.</p>
                    </div>

                    <div class="flex items-center justify-center my-6">
                        <div class="relative w-36 h-36 flex items-center justify-center">
                            <svg class="w-36 h-36 -rotate-90" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" r="50" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                                <circle cx="60" cy="60" r="50" fill="none"
                                    stroke="<?php echo $porc_saludable >= 80 ? '#10b981' : ($porc_saludable >= 50 ? '#f59e0b' : '#ef4444'); ?>"
                                    stroke-width="10"
                                    stroke-linecap="round"
                                    stroke-dasharray="<?php echo round(314 * $porc_saludable / 100); ?> 314"
                                    class="transition-all duration-1000 ease-out"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-3xl font-black text-slate-900"><?php echo $porc_saludable; ?>%</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Saludable</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 pt-4 border-t border-slate-100 text-xs">
                        <div class="flex justify-between items-center text-slate-600 font-medium">
                            <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> SKUs Abastecidos</span>
                            <span class="font-bold text-slate-900"><?php echo $total_articulos - $total_alertas; ?></span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600 font-medium">
                            <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-rose-500"></span> En Nivel Crítico</span>
                            <span class="font-bold text-rose-600"><?php echo $total_alertas; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Actividad Reciente Kardex -->
                <div class="glass-card lg:col-span-2 rounded-2xl p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                            <div>
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide">Últimas Transacciones Kardex</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Auditoría en tiempo real de entradas y salidas de almacén.</p>
                            </div>
                            <a href="kardex.php" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all">Ver Kardex</a>
                        </div>

                        <div class="divide-y divide-slate-100">
                            <?php if(empty($ultimos_movimientos)): ?>
                                <p class="text-xs text-slate-400 py-6 text-center">No se registran movimientos recientes.</p>
                            <?php else: ?>
                                <?php foreach($ultimos_movimientos as $mov): ?>
                                <div class="py-3 flex items-center justify-between hover:bg-slate-50/70 px-2 rounded-xl transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg <?php echo $mov['tipo_movimiento'] === 'ENTRADA' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'; ?> flex items-center justify-center font-bold text-xs shrink-0">
                                            <?php echo $mov['tipo_movimiento'] === 'ENTRADA' ? 'IN' : 'OUT'; ?>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-800 leading-tight"><?php echo htmlspecialchars($mov['articulo_nombre']); ?></p>
                                            <p class="text-[10px] text-slate-400 font-mono mt-0.5">SKU: <?php echo htmlspecialchars($mov['sku_vin']); ?> &bull; Ref: <?php echo htmlspecialchars($mov['referencia'] ?? 'S/R'); ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-black <?php echo $mov['tipo_movimiento'] === 'ENTRADA' ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                            <?php echo $mov['tipo_movimiento'] === 'ENTRADA' ? '+' : '-'; ?><?php echo $mov['cantidad']; ?> uds
                                        </span>
                                        <p class="text-[10px] text-slate-400 font-medium">Saldo: <?php echo $mov['saldo_actual']; ?> uds</p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ═══════════════════════════════════════════════════ -->
            <!--  SECCIÓN 4: TABLA DE CONTROL CRÍTICO DE STOCK       -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div class="glass-card rounded-2xl overflow-hidden border border-slate-200/80">
                <div class="px-7 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white/50">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-4 rounded-full <?php echo $total_alertas > 0 ? 'bg-rose-500' : 'bg-emerald-500'; ?>"></span>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide">Control Crítico de Inventario</h3>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5 ml-4">Artículos con stock inferior o igual al umbral mínimo de seguridad establecido.</p>
                    </div>

                    <div class="flex items-center gap-3 ml-4 sm:ml-0">
                        <?php if($total_alertas > 0): ?>
                            <span class="px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-black flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                <?php echo $total_alertas; ?> Alertas Críticas
                            </span>
                            <a href="nuevo_ingreso.php" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold transition-all shadow-sm flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Reponer Stock
                            </a>
                        <?php else: ?>
                            <span class="px-3.5 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-black flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Inventario en Rango Normal
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if($total_alertas > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200/80 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
                                <th class="px-7 py-3.5">SKU / VIN</th>
                                <th class="px-7 py-3.5">Repuesto / Maquinaria</th>
                                <th class="px-7 py-3.5">Categoría</th>
                                <th class="px-7 py-3.5 text-center">Umbral Mínimo</th>
                                <th class="px-7 py-3.5 text-center">Stock Actual</th>
                                <th class="px-7 py-3.5 text-center">Criticidad</th>
                                <th class="px-7 py-3.5 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php foreach($alertas_stock as $alerta):
                                $stock_a = (int)$alerta['stock_actual'];
                                $stock_m = (int)$alerta['stock_minimo'];
                                if($stock_a == 0) {
                                    $crit_label = 'SIN STOCK';
                                    $crit_badge = 'bg-rose-100 text-rose-700 border-rose-200';
                                    $bar_pct = 0;
                                    $bar_color = 'bg-rose-500';
                                } elseif($stock_a <= round($stock_m * 0.5)) {
                                    $crit_label = 'CRÍTICO';
                                    $crit_badge = 'bg-rose-50 text-rose-600 border-rose-100';
                                    $bar_pct = max(10, round($stock_a / $stock_m * 100));
                                    $bar_color = 'bg-rose-500';
                                } else {
                                    $crit_label = 'BAJO';
                                    $crit_badge = 'bg-amber-50 text-amber-700 border-amber-200';
                                    $bar_pct = round($stock_a / $stock_m * 100);
                                    $bar_color = 'bg-amber-400';
                                }
                                $c = $cat_colors[$alerta['categoria']] ?? ['light'=>'bg-slate-100','text'=>'text-slate-600'];
                            ?>
                            <tr class="hover:bg-slate-50/80 transition-colors duration-150 group">
                                <td class="px-7 py-4 font-mono font-bold text-slate-600">
                                    <span class="px-2 py-1 bg-slate-100 rounded-lg text-[11px]"><?php echo htmlspecialchars($alerta['sku_vin']); ?></span>
                                </td>
                                <td class="px-7 py-4 font-bold text-slate-800 text-xs">
                                    <?php echo htmlspecialchars($alerta['nombre']); ?>
                                </td>
                                <td class="px-7 py-4">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border <?php echo $c['light'].' '.$c['text']; ?>">
                                        <?php echo htmlspecialchars($alerta['categoria']); ?>
                                    </span>
                                </td>
                                <td class="px-7 py-4 text-center font-semibold text-slate-500">
                                    <?php echo $stock_m; ?> uds
                                </td>
                                <td class="px-7 py-4 text-center font-black text-rose-600 text-sm">
                                    <?php echo $stock_a; ?> uds
                                </td>
                                <td class="px-7 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded-full border text-[10px] font-black <?php echo $crit_badge; ?>">
                                            <?php echo $crit_label; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-7 py-4 text-right">
                                    <a href="nuevo_ingreso.php" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 font-bold text-[11px] transition-all">
                                        <span>Abastecer</span>
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="py-14 text-center">
                    <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-inner">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h4 class="text-sm font-black text-slate-800">Inventario Totalmente Suministrado</h4>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">No existen artículos por debajo de la reserva mínima de seguridad.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- ═══════════════════════════════════════════════════ -->
            <!--  SECCIÓN 5: ACCESOS DIRECTOS EMPRESARIALES          -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-3.5">Módulos Rápidos de Operación</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <a href="nuevo_ingreso.php" class="glass-card rounded-2xl p-4 flex items-center gap-3.5 hover:border-emerald-300 transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 group-hover:bg-emerald-500 text-emerald-600 group-hover:text-white flex items-center justify-center transition-all shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-900 group-hover:text-emerald-700">Registrar Compra</p>
                            <p class="text-[10px] text-slate-400">Entrada a Kardex</p>
                        </div>
                    </a>

                    <a href="nueva_venta.php" class="glass-card rounded-2xl p-4 flex items-center gap-3.5 hover:border-blue-300 transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 group-hover:bg-blue-600 text-blue-600 group-hover:text-white flex items-center justify-center transition-all shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-900 group-hover:text-blue-700">Punto de Venta</p>
                            <p class="text-[10px] text-slate-400">Despacho y factura</p>
                        </div>
                    </a>

                    <a href="nuevo_articulo.php" class="glass-card rounded-2xl p-4 flex items-center gap-3.5 hover:border-violet-300 transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-violet-50 group-hover:bg-violet-600 text-violet-600 group-hover:text-white flex items-center justify-center transition-all shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-900 group-hover:text-violet-700">Crear Artículo</p>
                            <p class="text-[10px] text-slate-400">Nuevo SKU catálogo</p>
                        </div>
                    </a>

                    <a href="reportes.php" class="glass-card rounded-2xl p-4 flex items-center gap-3.5 hover:border-amber-300 transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 group-hover:bg-amber-600 text-amber-600 group-hover:text-white flex items-center justify-center transition-all shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-900 group-hover:text-amber-700">Generar Reporte</p>
                            <p class="text-[10px] text-slate-400">Exportar balance PDF</p>
                        </div>
                    </a>
                </div>
            </div>

        </div>
    </main>

    <!-- ═══════════════════════════════════════════════════ -->
    <!--  INICIALIZACIÓN DE GRÁFICOS CHART.JS                -->
    <!-- ═══════════════════════════════════════════════════ -->
    <script>
        // Configuración global de fuentes para Chart.js
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#64748b';

        // 1. Gráfico de Barras: Flujo Kardex (Entradas vs Salidas)
        const ctxKardex = document.getElementById('chartKardexFlow').getContext('2d');
        new Chart(ctxKardex, {
            type: 'bar',
            data: {
                labels: ['Compras (Entradas)', 'Despachos (Salidas)'],
                datasets: [{
                    label: 'Movimientos Totales',
                    data: [<?php echo $total_entradas; ?>, <?php echo $total_salidas; ?>],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.85)', // Emerald
                        'rgba(244, 63, 94, 0.85)'   // Rose
                    ],
                    borderColor: [
                        '#10b981',
                        '#f43f5e'
                    ],
                    borderWidth: 1.5,
                    borderRadius: 8,
                    borderSkipped: false,
                    barThickness: 54
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 12, weight: '700' },
                        bodyFont: { size: 12 },
                        padding: 12,
                        cornerRadius: 10,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(241, 245, 249, 1)' },
                        ticks: { font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '600' }, color: '#334155' }
                    }
                }
            }
        });

        // 2. Gráfico de Dona: Distribución por Categorías
        const ctxCat = document.getElementById('chartCategories').getContext('2d');
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($cat_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($cat_data); ?>,
                    backgroundColor: <?php echo json_encode($cat_hex); ?>,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            boxHeight: 10,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { size: 11, weight: '600' },
                            padding: 14
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 12, weight: '700' },
                        bodyFont: { size: 12 },
                        padding: 12,
                        cornerRadius: 10
                    }
                }
            }
        });
    </script>
</body>
</html>