<?php
session_start();
if(!isset($_SESSION['usuario_id'])){
    header("Location: ../index.php?error=nologin");
    exit();
}
require_once '../config/conexion.php';

// Conectar a la BD para cargar los proveedores en el menú desplegable (select)
$database = new Conexion();
$db = $database->getConnection();
$query = "SELECT id_proveedor, razon_social FROM proveedores ORDER BY razon_social ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Artículo - ComerXync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex h-screen">

    <!-- El mismo Sidebar de siempre -->
    <aside class="w-64 bg-slate-800 text-white flex flex-col">
        <div class="h-16 flex items-center justify-center border-b border-slate-700">
            <h2 class="text-xl font-bold">Comer<span class="text-amber-500">Xync</span></h2>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="dashboard.php" class="block px-4 py-2 hover:bg-slate-700 rounded text-sm transition-colors">Dashboard</a>
            <a href="articulos.php" class="block px-4 py-2 bg-slate-700 rounded text-sm">Catálogo (Almacén)</a>
        </nav>
        <div class="p-4 border-t border-slate-700">
            <a href="../controllers/logoutController.php" class="block text-center px-4 py-2 text-sm text-red-400 hover:text-red-300">Cerrar Sesión</a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col overflow-hidden">
        <!-- Topbar -->
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 shrink-0">
            <h1 class="text-lg font-semibold text-gray-700">Registrar Nuevo Artículo</h1>
        </header>

        <!-- Área del Formulario -->
        <div class="p-8 flex-1 overflow-auto flex justify-center">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 w-full max-w-2xl p-8 h-fit">
                
                <h2 class="text-xl font-bold text-slate-800 mb-6">Detalles del Artículo (Repuesto/Vehículo)</h2>
                
                <!-- El action apunta al controlador que guardará los datos -->
                <form action="../controllers/crearArticuloController.php" method="POST" class="space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Campo SKU / VIN -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Código SKU / VIN</label>
                            <input type="text" name="sku_vin" required placeholder="Ej: FILT-001 o 1VWAB2..."
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-800 outline-none">
                        </div>

                        <!-- Campo Nombre -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre / Descripción</label>
                            <input type="text" name="nombre" required placeholder="Filtro de Aceite"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-800 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Selector Categoría -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Categoría</label>
                            <select name="categoria" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-800 outline-none bg-white">
                                <option value="Repuesto">Repuesto</option>
                                <option value="Maquinaria">Maquinaria</option>
                                <option value="Vehiculo">Vehículo</option>
                                <option value="Accesorio">Accesorio</option>
                            </select>
                        </div>

                        <!-- Selector Proveedor (Dinámico desde PHP) -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Proveedor (Origen)</label>
                            <select name="id_proveedor" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-800 outline-none bg-white">
                                <option value="">-- Seleccionar Proveedor --</option>
                                <?php foreach($proveedores as $prov): ?>
                                    <option value="<?php echo $prov['id_proveedor']; ?>"><?php echo htmlspecialchars($prov['razon_social']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Precio Compra -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Precio de Compra (Bs.)</label>
                            <input type="number" step="0.01" name="precio_compra" required placeholder="0.00"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-800 outline-none">
                        </div>

                        <!-- Precio Venta -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Precio de Venta (Bs.)</label>
                            <input type="number" step="0.01" name="precio_venta" required placeholder="0.00"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-800 outline-none">
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-4 pt-4 mt-6 border-t border-gray-100">
                        <a href="articulos.php" class="px-6 py-2.5 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium shadow-sm transition-colors">
                            Guardar Artículo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>