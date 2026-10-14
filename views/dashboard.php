<?php
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

    // 3. Alertas de Stock Bajo (Menor o igual al stock mínimo)
    $query_stock = "SELECT a.nombre, a.sku_vin, a.stock_minimo,
              COALESCE((SELECT saldo_actual FROM kardex k WHERE k.id_articulo = a.id_articulo ORDER BY id_movimiento DESC LIMIT 1), 0) as stock_actual
              FROM articulos a 
              HAVING stock_actual <= a.stock_minimo";
    $stmt_stock = $db->query($query_stock);
    $alertas_stock = $stmt_stock->fetchAll(PDO::FETCH_ASSOC);
    $total_alertas = count($alertas_stock);

    // 4. Stock Físico Total (Suma de todo el almacén)
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
    <title>Dashboard - ComerXync AutoParts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">

    <!-- SIDEBAR MODULARIZADO (AQUÍ ESTÁ LA MAGIA) -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col overflow-hidden bg-gray-50">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-8 shrink-0 border-b border-gray-200">
            <h1 class="text-xl font-bold text-slate-800">Panel de Control Gerencial</h1>
            <div class="text-sm text-gray-500 font-medium"><?php echo date('d/m/Y'); ?></div>
        </header>

        <div class="p-8 flex-1 overflow-auto">
            <!-- Mensajes de Éxito -->
            <?php if(isset($_GET['exito']) && $_GET['exito'] == 'ingreso_ok'): ?>
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">Ingreso registrado correctamente en el Kardex.</div>
            <?php endif; ?>

            <!-- Tarjetas de Indicadores (KPIs) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Repuestos en Catálogo</p>
                        <p class="text-3xl font-black text-slate-800 mt-1"><?php echo $total_articulos; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-2xl">⚙️</div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Volumen Total en Almacén</p>
                        <p class="text-3xl font-black text-slate-800 mt-1"><?php echo $stock_total; ?> <span class="text-sm font-normal text-gray-400">uds</span></p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 text-2xl">📦</div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Proveedores Activos</p>
                        <p class="text-3xl font-black text-slate-800 mt-1"><?php echo $total_proveedores; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 text-2xl">🏢</div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-500">Alertas Stock Bajo</p>
                        <p class="text-3xl font-black text-red-600 mt-1"><?php echo $total_alertas; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center text-red-600 text-2xl">⚠️</div>
                </div>
            </div>

            <!-- Tabla de Alertas -->
            <?php if($total_alertas > 0): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-red-100 overflow-hidden">
                <div class="bg-red-50 px-6 py-4 border-b border-red-100">
                    <h3 class="text-lg font-bold text-red-800">Atención: Repuestos que requieren reabastecimiento</h3>
                </div>
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">SKU</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Repuesto</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase">Stock Mínimo Permitido</th>
                            <th class="px-6 py-3 text-xs font-bold text-red-600 uppercase">Stock Actual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach($alertas_stock as $alerta): ?>
                        <tr class="hover:bg-red-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($alerta['sku_vin']); ?></td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-800"><?php echo htmlspecialchars($alerta['nombre']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo $alerta['stock_minimo']; ?> uds</td>
                            <td class="px-6 py-4 text-sm font-bold text-red-600"><?php echo $alerta['stock_actual']; ?> uds</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="bg-emerald-50 rounded-2xl p-8 text-center border border-emerald-100">
                <div class="text-4xl mb-3">✅</div>
                <h3 class="text-lg font-bold text-emerald-800">Inventario Saludable</h3>
                <p class="text-emerald-600 mt-1">Todos los repuestos están por encima de su stock mínimo.</p>
            </div>
            <?php endif; ?>

        </div>
    </main>
</body>
</html>