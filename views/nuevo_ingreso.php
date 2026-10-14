<?php
session_start();
if(!isset($_SESSION['usuario_id'])){ header("Location: ../index.php?error=nologin"); exit(); }
require_once '../config/conexion.php';

// Cargamos TODOS los artículos para que el almacenero elija a cuál ingresarle stock
$database = new Conexion();
$db = $database->getConnection();
$query = "SELECT id_articulo, sku_vin, nombre FROM articulos ORDER BY nombre ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingreso a Almacén - ComerXync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    
    <!-- SIDEBAR MODULARIZADO (AQUÍ ESTÁ LA MAGIA) -->
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 shrink-0 border-b border-gray-200">
            <h1 class="text-lg font-semibold text-gray-700">Registrar Entrada (Compra)</h1>
            <div class="flex items-center space-x-3">
                <span class="text-sm font-medium text-gray-600">Almacenero: <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
            </div>
        </header>

        <div class="p-8 flex-1 overflow-auto flex justify-center">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 w-full max-w-2xl p-8 h-fit">
                
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-green-100 text-green-600 p-2 rounded-lg">📥</div>
                    <h2 class="text-2xl font-bold text-slate-800">Póliza de Ingreso a Kardex</h2>
                </div>
                
                <form action="../controllers/ingresoKardexController.php" method="POST" class="space-y-6">
                    <!-- Selector de Artículo -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Seleccionar Repuesto / Vehículo</label>
                        <select name="id_articulo" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none bg-white font-medium text-slate-700">
                            <option value="">-- Buscar Artículo por Nombre o SKU --</option>
                            <?php foreach($articulos as $art): ?>
                                <option value="<?php echo $art['id_articulo']; ?>">
                                    [<?php echo htmlspecialchars($art['sku_vin']); ?>] - <?php echo htmlspecialchars($art['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Cantidad a Ingresar -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Cantidad Entrante</label>
                            <input type="number" name="cantidad" required min="1" placeholder="Ej: 50"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none text-center text-xl font-bold text-slate-800">
                        </div>
                        
                        <!-- Nro de Referencia -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nro. de Póliza o Factura</label>
                            <input type="text" name="referencia" required placeholder="Ej: POL-2026-890"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none font-medium text-slate-700 uppercase">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 mt-6 border-t border-gray-100">
                        <button type="submit" class="px-6 py-4 bg-green-600 hover:bg-green-500 text-white rounded-xl text-lg font-bold shadow-[0_0_15px_rgba(22,163,74,0.4)] transition-all duration-300 w-full transform hover:scale-[1.02] flex justify-center items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Confirmar Ingreso y Sumar al Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>