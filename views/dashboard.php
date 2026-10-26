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
    $query_stock = "SELECT a.nombre, a.sku_vin, a.stock_minimo,
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

} catch(PDOException $e) {
    die("Error cargando el Dashboard: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Gerencial - ComerXync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-100 flex h-screen overflow-hidden">

    <!-- SIDEBAR MODULARIZADO -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col overflow-hidden bg-slate-50">
        
        <!-- Header Superior -->
        <header class="h-20 bg-white shadow-xs flex items-center justify-between px-8 shrink-0 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Panel de Control Gerencial</h1>
                <p class="text-xs text-slate-500 font-medium">Resumen general de operaciones y métricas logísticas en tiempo real.</p>
            </div>
            <div class="flex items-center gap-3 bg-slate-100 px-4 py-2 rounded-xl border border-slate-200">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-bold text-slate-700">Sistema Conectado &bull; <?php echo date('d/m/Y'); ?></span>
            </div>
        </header>

        <!-- Contenedor con Scroll -->
        <div class="p-8 flex-1 overflow-auto space-y-8">
            
            <?php if(isset($_GET['exito']) && $_GET['exito'] == 'ingreso_ok'): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl shadow-xs font-medium text-sm flex items-center gap-3">
                    <span class="text-lg">✅</span> Ingreso registrado correctamente en el Kardex y stock actualizado.
                </div>
            <?php endif; ?>

            <!-- Tarjetas de Indicadores KPI (Estilo Enterprise) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- KPI 1 -->
                <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 hover:shadow-md transition-all relative overflow-hidden group">
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-blue-50 rounded-full group-hover:scale-125 transition-transform"></div>
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Catálogo Activo</p>
                            <p class="text-4xl font-black text-slate-900 mt-2"><?php echo $total_articulos; ?></p>
                            <p class="text-xs text-blue-600 font-semibold mt-2 flex items-center gap-1">SKUs registrados</p>
                        </div>
                        <div class="w-14 h-14 bg-blue-600/10 rounded-2xl flex items-center justify-center text-blue-600 text-2xl shadow-inner">⚙️</div>
                    </div>
                </div>

                <!-- KPI 2 -->
                <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 hover:shadow-md transition-all relative overflow-hidden group">
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-indigo-50 rounded-full group-hover:scale-125 transition-transform"></div>
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Volumen en Almacén</p>
                            <p class="text-4xl font-black text-slate-900 mt-2"><?php echo number_format($stock_total); ?></p>
                            <p class="text-xs text-indigo-600 font-semibold mt-2">Unidades físicas totales</p>
                        </div>
                        <div class="w-14 h-14 bg-indigo-600/10 rounded-2xl flex items-center justify-center text-indigo-600 text-2xl shadow-inner">📦</div>
                    </div>
                </div>

                <!-- KPI 3 -->
                <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 hover:shadow-md transition-all relative overflow-hidden group">
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-50 rounded-full group-hover:scale-125 transition-transform"></div>
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Socios de Origen</p>
                            <p class="text-4xl font-black text-slate-900 mt-2"><?php echo $total_proveedores; ?></p>
                            <p class="text-xs text-emerald-600 font-semibold mt-2">Proveedores globales</p>
                        </div>
                        <div class="w-14 h-14 bg-emerald-600/10 rounded-2xl flex items-center justify-center text-emerald-600 text-2xl shadow-inner">🏢</div>
                    </div>
                </div>

                <!-- KPI 4 -->
                <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 hover:shadow-md transition-all relative overflow-hidden group">
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-red-50 rounded-full group-hover:scale-125 transition-transform"></div>
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-red-500 uppercase tracking-wider">Alertas de Stock</p>
                            <p class="text-4xl font-black text-red-600 mt-2"><?php echo $total_alertas; ?></p>
                            <p class="text-xs text-red-500 font-semibold mt-2">Requieren reabastecimiento</p>
                        </div>
                        <div class="w-14 h-14 bg-red-600/10 rounded-2xl flex items-center justify-center text-red-600 text-2xl shadow-inner">⚠️</div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Alertas de Stock Bajo -->
            <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Control Crítico de Inventario</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Listado de repuestos que se encuentran por debajo del umbral mínimo de seguridad.</p>
                    </div>
                    <?php if($total_alertas > 0): ?>
                        <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-xl"><?php echo $total_alertas; ?> Alertas Activas</span>
                    <?php endif; ?>
                </div>

                <?php if($total_alertas > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase text-slate-500 font-extrabold tracking-wider">
                                <th class="px-8 py-4">Código SKU / VIN</th>
                                <th class="px-8 py-4">Repuesto / Maquinaria</th>
                                <th class="px-8 py-4 text-center">Umbral Mínimo</th>
                                <th class="px-8 py-4 text-right">Stock Actual en Almacén</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php foreach($alertas_stock as $alerta): ?>
                            <tr class="hover:bg-red-50/30 transition-colors">
                                <td class="px-8 py-4 font-mono font-bold text-slate-600 text-xs"><?php echo htmlspecialchars($alerta['sku_vin']); ?></td>
                                <td class="px-8 py-4 font-bold text-slate-800"><?php echo htmlspecialchars($alerta['nombre']); ?></td>
                                <td class="px-8 py-4 text-center text-slate-500 font-medium"><?php echo $alerta['stock_minimo']; ?> uds</td>
                                <td class="px-8 py-4 text-right font-black text-red-600 text-base"><?php echo $alerta['stock_actual']; ?> uds</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="p-12 text-center">
                    <div class="text-5xl mb-3">🛡️</div>
                    <h4 class="text-base font-bold text-slate-800">Inventario 100% Saludable</h4>
                    <p class="text-xs text-slate-500 mt-1">No hay alertas de stock bajo en este momento. Todos los niveles son óptimos.</p>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</body>
</html>