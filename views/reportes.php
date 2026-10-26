<?php
// Archivo: views/reportes.php
session_start();
if(!isset($_SESSION['usuario_id'])){ header("Location: ../index.php?error=nologin"); exit(); }
require_once '../config/conexion.php';

$database = new Conexion();
$db = $database->getConnection();

// Consulta de Inventario Valorizado
$query = "SELECT a.sku_vin, a.nombre, a.categoria, a.precio_compra, a.precio_venta, a.stock_minimo,
          COALESCE((SELECT saldo_actual FROM kardex k WHERE k.id_articulo = a.id_articulo ORDER BY id_movimiento DESC LIMIT 1), 0) as stock
          FROM articulos a ORDER BY a.categoria, a.nombre ASC";
$articulos = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

$valor_total_costo = 0;
$valor_total_venta = 0;
foreach($articulos as $a) {
    $valor_total_costo += ($a['stock'] * $a['precio_compra']);
    $valor_total_venta += ($a['stock'] * $a['precio_venta']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario Valorizado - ComerXync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden print:bg-white print:h-auto print:overflow-visible">

    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden print:overflow-visible">
        <!-- Barra superior (se oculta al imprimir PDF) -->
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-8 shrink-0 border-b border-gray-200 print:hidden">
            <h1 class="text-xl font-bold text-slate-800">📄 Reportes Ejecutivos e Inventario Valorizado</h1>
            <button onclick="window.print()" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-bold shadow-md flex items-center gap-2 transition-colors">
                🖨️ Exportar a PDF / Imprimir
            </button>
        </header>

        <!-- Hoja de Reporte -->
        <div class="p-8 flex-1 overflow-auto print:p-0 print:overflow-visible">
            <div class="bg-white max-w-5xl mx-auto p-10 rounded-2xl shadow-sm border border-gray-200 print:shadow-none print:border-none">
                
                <!-- Membrete Oficial -->
                <div class="flex justify-between items-start border-b-2 border-slate-800 pb-6 mb-6">
                    <div>
                        <h2 class="text-3xl font-black text-slate-900">COMER<span class="text-amber-500">XYNC</span> AUTOPARTS</h2>
                        <p class="text-sm text-gray-500">Sistema ERP de Gestión Comercial e Importaciones</p>
                        <p class="text-xs text-gray-400 mt-1">Sucre, Bolivia | NIT: 1029384021</p>
                    </div>
                    <div class="text-right">
                        <span class="bg-slate-900 text-white text-xs font-bold px-3 py-1 rounded uppercase">Documento Oficial</span>
                        <p class="text-sm font-bold text-slate-700 mt-2">Fecha: <?php echo date('d/m/Y H:i'); ?></p>
                        <p class="text-xs text-gray-500">Emitido por: <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></p>
                    </div>
                </div>

                <!-- Resumen Financiero -->
                <div class="grid grid-cols-3 gap-4 mb-8 bg-slate-50 p-4 rounded-xl border border-gray-200">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold">Total Ítems Registrados</p>
                        <p class="text-xl font-black text-slate-800"><?php echo count($articulos); ?> artículos</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold">Capital en Almacén (Costo)</p>
                        <p class="text-xl font-black text-slate-800">Bs. <?php echo number_format($valor_total_costo, 2); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold">Proyección Comercial (Venta)</p>
                        <p class="text-xl font-black text-blue-600">Bs. <?php echo number_format($valor_total_venta, 2); ?></p>
                    </div>
                </div>

                <!-- Tabla Detallada -->
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b-2 border-slate-300 text-xs uppercase text-slate-600 font-bold">
                            <th class="py-2">SKU</th>
                            <th class="py-2">Descripción del Artículo</th>
                            <th class="py-2">Categoría</th>
                            <th class="py-2 text-right">Stock</th>
                            <th class="py-2 text-right">P. Venta (Bs.)</th>
                            <th class="py-2 text-right">Subtotal Valorizado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach($articulos as $item): ?>
                        <tr>
                            <td class="py-2.5 font-mono text-xs"><?php echo htmlspecialchars($item['sku_vin']); ?></td>
                            <td class="py-2.5 font-semibold text-slate-800"><?php echo htmlspecialchars($item['nombre']); ?></td>
                            <td class="py-2.5 text-gray-600"><?php echo htmlspecialchars($item['categoria']); ?></td>
                            <td class="py-2.5 text-right font-bold <?php echo ($item['stock'] <= $item['stock_minimo']) ? 'text-red-600' : 'text-slate-800'; ?>">
                                <?php echo $item['stock']; ?>
                            </td>
                            <td class="py-2.5 text-right"><?php echo number_format($item['precio_venta'], 2); ?></td>
                            <td class="py-2.5 text-right font-bold text-slate-900">Bs. <?php echo number_format($item['stock'] * $item['precio_venta'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pie de Firmas para Impresión -->
                <div class="mt-16 pt-8 grid grid-cols-2 gap-16 text-center text-xs text-gray-500">
                    <div class="border-t border-gray-400 pt-2">
                        <p class="font-bold text-slate-700"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></p>
                        <p>Responsable de Emisión</p>
                    </div>
                    <div class="border-t border-gray-400 pt-2">
                        <p class="font-bold text-slate-700">Gerencia General - ComerXync</p>
                        <p>Sello y Conformidad</p>
                    </div>
                </div>

            </div>
        </div>
    </main>
</body>
</html>