<?php
// Archivo: views/tienda_b2b.php
session_start();
if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Cliente_B2B'){ 
    header("Location: ../index.php?error=acceso_denegado"); 
    exit(); 
}
require_once '../config/conexion.php';

$database = new Conexion();
$db = $database->getConnection();

// Traemos los repuestos, su IMAGEN y calculamos su stock
$query = "SELECT a.id_articulo, a.sku_vin, a.nombre, a.categoria, a.precio_venta, a.imagen_url,
          COALESCE((SELECT saldo_actual FROM kardex k WHERE k.id_articulo = a.id_articulo ORDER BY id_movimiento DESC LIMIT 1), 0) as stock 
          FROM articulos a ORDER BY a.categoria, a.nombre ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Portal B2B - ComerXync AutoParts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; background-color: #f8fafc; } </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="bg-slate-900 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center">
                    <h1 class="text-2xl font-black tracking-tight">Comer<span class="text-amber-500">Xync</span> <span class="text-sm font-normal text-gray-400 ml-2">Portal B2B</span></h1>
                </div>
                <div class="flex items-center space-x-6">
                    <span class="text-sm font-medium text-gray-300">Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                    <a href="mis_pedidos.php" class="text-gray-300 hover:text-white font-medium text-sm transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Mi Carrito / Pedidos
                    </a>
                    <a href="../controllers/logoutController.php" class="bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="bg-blue-600 text-white py-12 bg-opacity-90 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?q=80&w=1974&auto=format&fit=crop'); background-blend-mode: multiply;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-extrabold sm:text-4xl">Catálogo Mayorista 24/7</h2>
            <p class="mt-4 text-lg text-blue-100">Repuestos originales y maquinaria pesada con entrega inmediata.</p>
        </div>
    </div>

    <!-- Catálogo de Productos (Grid) -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <?php if(isset($_GET['agregado']) && $_GET['agregado'] == 'ok'): ?>
            <div class="mb-8 bg-green-100 border border-green-200 text-green-800 px-6 py-4 rounded-xl shadow-sm flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium">¡Artículo reservado y agregado a tu carrito exitosamente!</span>
                </div>
                <a href="mis_pedidos.php" class="font-bold underline hover:text-green-900 transition-colors">Ver mi pedido</a>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            
            <?php foreach($articulos as $art): ?>
            <!-- Tarjeta con Fotografía -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 flex flex-col group">
                
                <!-- Imagen del Producto -->
                <div class="h-56 w-full bg-white flex items-center justify-center p-4 border-b border-gray-50 relative">
                    <?php if($art['stock'] <= 0): ?>
                        <div class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center backdrop-blur-sm">
                            <span class="bg-red-600 text-white font-black px-4 py-2 rounded-lg transform -rotate-12 border-2 border-white shadow-lg">AGOTADO</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($art['imagen_url'])): ?>
                        <img src="<?php echo htmlspecialchars($art['imagen_url']); ?>" alt="<?php echo htmlspecialchars($art['nombre']); ?>" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                        <!-- Fallback si no hay foto -->
                        <div class="text-6xl text-gray-300">📦</div>
                    <?php endif; ?>
                </div>

                <!-- Detalles -->
                <div class="p-5 flex-1 flex flex-col bg-white z-20">
                    <div class="flex justify-between items-start mb-2">
                        <div class="text-xs font-bold text-amber-500 uppercase tracking-wider bg-amber-50 px-2 py-1 rounded"><?php echo htmlspecialchars($art['categoria']); ?></div>
                        <span class="text-xs font-medium text-gray-400">SKU: <?php echo htmlspecialchars($art['sku_vin']); ?></span>
                    </div>
                    
                    <h3 class="text-lg font-bold text-slate-800 leading-tight mb-4 line-clamp-2" title="<?php echo htmlspecialchars($art['nombre']); ?>">
                        <?php echo htmlspecialchars($art['nombre']); ?>
                    </h3>
                    
                    <div class="mt-auto pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500 font-medium">Precio Mayorista</span>
                                <span class="text-2xl font-black text-blue-600">Bs. <?php echo number_format($art['precio_venta'], 2); ?></span>
                            </div>
                            <?php if($art['stock'] > 0): ?>
                                <span class="bg-green-50 border border-green-200 text-green-700 text-xs px-2.5 py-1.5 rounded-lg font-bold shadow-sm"><?php echo $art['stock']; ?> uds</span>
                            <?php endif; ?>
                        </div>

                        <!-- Formulario Pedido -->
                        <form action="../controllers/carritoController.php" method="POST" class="flex gap-2">
                            <input type="hidden" name="id_articulo" value="<?php echo $art['id_articulo']; ?>">
                            <div class="relative w-20">
                                <input type="number" name="cantidad" min="1" max="<?php echo $art['stock']; ?>" value="1" <?php echo $art['stock'] <= 0 ? 'disabled' : ''; ?>
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-center outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 font-medium text-slate-700 bg-gray-50">
                            </div>
                            <button type="submit" <?php echo $art['stock'] <= 0 ? 'disabled' : ''; ?>
                                class="flex-1 bg-slate-900 text-white font-semibold rounded-xl hover:bg-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Pedir
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

        </div>
    </main>
</body>
</html>