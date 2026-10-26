<?php
// Archivo: views/nuevo_articulo.php
session_start();
if(!isset($_SESSION['usuario_id'])){
    header("Location: ../index.php?error=nologin");
    exit();
}
require_once '../config/conexion.php';

// Conectar a la BD para cargar los proveedores en el menú desplegable (select)
$database = new Conexion();$db = $database->getConnection();$query = "SELECT id_proveedor, razon_social FROM proveedores ORDER BY razon_social ASC";
$stmt =$db->prepare($query);$stmt->execute();
$proveedores =$stmt->fetchAll(PDO::FETCH_ASSOC);
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
<body class="bg-gray-50 flex h-screen overflow-hidden">

    <!-- SIDEBAR MODULARIZADO -->
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        <!-- Topbar -->
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 shrink-0 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="bg-blue-100 text-blue-600 p-2 rounded-lg">⚙️</div>
                <h1 class="text-xl font-bold text-slate-800">Registrar Nuevo Artículo</h1>
            </div>
            <a href="articulos.php" class="text-sm font-medium text-gray-500 hover:text-slate-800 transition-colors">← Volver al Catálogo</a>
        </header>

        <!-- Área del Formulario -->
        <div class="p-8 flex-1 overflow-auto flex justify-center">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 w-full max-w-2xl p-8 h-fit">
                
                <h2 class="text-xl font-bold text-slate-800 mb-6 border-b border-gray-100 pb-4">Ficha Técnica del Repuesto / Vehículo</h2>
                
                <form action="../controllers/crearArticuloController.php" method="POST" class="space-y-5">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Campo SKU / VIN -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Código SKU / VIN</label>
                            <input type="text" name="sku_vin" required placeholder="Ej: FIL-TOY-002"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none uppercase font-mono text-sm">
                        </div>

                        <!-- Campo Nombre -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nombre / Descripción</label>
                            <input type="text" name="nombre" required placeholder="Ej: Pastillas de Freno Delanteras"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Selector Categoría -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Categoría</label>
                            <select name="categoria" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-white text-sm font-medium">
                                <option value="Repuesto">Repuesto</option>
                                <option value="Maquinaria">Maquinaria</option>
                                <option value="Vehiculo">Vehículo</option>
                                <option value="Accesorio">Accesorio</option>
                            </select>
                        </div>

                        <!-- Selector Proveedor -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Proveedor (Origen)</label>
                            <select name="id_proveedor" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-white text-sm font-medium">
                                <option value="">-- Seleccionar Proveedor --</option>
                                <?php foreach($proveedores as$prov): ?>
                                    <option value="<?php echo $prov['id_proveedor']; ?>"><?php echo htmlspecialchars($prov['razon_social']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Precio Compra -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Costo Compra (Bs.)</label>
                            <input type="number" step="0.01" name="precio_compra" required placeholder="0.00"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                        </div>

                        <!-- Precio Venta -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Precio Venta (Bs.)</label>
                            <input type="number" step="0.01" name="precio_venta" required placeholder="0.00"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-bold text-blue-600">
                        </div>

                        <!-- Stock Mínimo para Alertas -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Stock Mínimo (Alerta)</label>
                            <input type="number" name="stock_minimo" required value="5" min="1"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm text-center font-bold">
                        </div>
                    </div>

                    <!-- URL de la Imagen para la Tienda B2B -->
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">URL de Imagen (Opcional para Catálogo B2B)</label>
                        <input type="text" name="imagen_url" placeholder="Dejar vacío para generar imagen corporativa automática"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm text-gray-600">
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-4 pt-4 mt-6 border-t border-gray-100">
                        <a href="articulos.php" class="px-6 py-2.5 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl font-medium transition-colors text-sm">
                            Cancelar
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-md transition-colors text-sm">
                            + Guardar Artículo en Catálogo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>