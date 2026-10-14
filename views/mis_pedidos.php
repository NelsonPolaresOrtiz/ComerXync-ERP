<?php
// Archivo: views/mis_pedidos.php
session_start();
if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Cliente_B2B'){ 
    header("Location: ../index.php?error=acceso_denegado"); 
    exit(); 
}
require_once '../config/conexion.php';

$database = new Conexion();
$db = $database->getConnection();
$id_cliente = $_SESSION['usuario_id'];

// 1. Buscar si el cliente tiene un carrito activo (Pedido 'Pendiente')
$query_carrito = "SELECT id_pedido, fecha_pedido FROM pedidos WHERE id_usuario = :id_cliente AND estado = 'Pendiente' LIMIT 1";
$stmt_carrito = $db->prepare($query_carrito);
$stmt_carrito->bindParam(':id_cliente', $id_cliente);
$stmt_carrito->execute();

$carrito = null;
$detalles = [];
$total_carrito = 0;

if($stmt_carrito->rowCount() > 0) {
    $carrito = $stmt_carrito->fetch(PDO::FETCH_ASSOC);
    
    // Traer los repuestos que están dentro de este pedido
    $query_det = "SELECT d.id_detalle, d.cantidad, a.nombre, a.sku_vin, a.precio_venta, (d.cantidad * a.precio_venta) as subtotal 
                  FROM detalle_pedidos d 
                  INNER JOIN articulos a ON d.id_articulo = a.id_articulo 
                  WHERE d.id_pedido = :id_pedido";
    $stmt_det = $db->prepare($query_det);
    $stmt_det->bindParam(':id_pedido', $carrito['id_pedido']);
    $stmt_det->execute();
    $detalles = $stmt_det->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular el total a pagar sumando los subtotales
    foreach($detalles as $det) {
        $total_carrito += $det['subtotal'];
    }
}

// 2. Traer el historial de pedidos pasados (Aprobados, En Ruta, Entregados)
$query_historial = "SELECT id_pedido, fecha_pedido, estado FROM pedidos WHERE id_usuario = :id_cliente AND estado != 'Pendiente' ORDER BY id_pedido DESC";
$stmt_hist = $db->prepare($query_historial);
$stmt_hist->bindParam(':id_cliente', $id_cliente);
$stmt_hist->execute();
$historial = $stmt_hist->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Carrito - ComerXync AutoParts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; background-color: #f8fafc; } </style>
</head>
<body>
    <!-- Navbar de la Tienda -->
    <nav class="bg-slate-900 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center">
                    <h1 class="text-2xl font-black tracking-tight">Comer<span class="text-amber-500">Xync</span></h1>
                </div>
                <div class="flex items-center space-x-6">
                    <span class="text-sm font-medium text-gray-300">Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                    <a href="tienda_b2b.php" class="text-gray-300 hover:text-white font-medium text-sm transition-colors">🏪 Volver a la Tienda</a>
                    <a href="../controllers/logoutController.php" class="bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <?php if(isset($_GET['exito']) && $_GET['exito'] == 'confirmado'): ?>
            <div class="mb-8 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                ✅ ¡Tu pedido ha sido confirmado y enviado a la importadora! Pronto lo despacharán.
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- SECCIÓN: CARRITO ACTUAL -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-slate-50 flex justify-between items-center">
                        <h2 class="text-xl font-bold text-slate-800">🛒 Mi Carrito de Compras</h2>
                        <?php if($carrito): ?>
                            <span class="text-xs font-bold bg-amber-100 text-amber-700 px-2 py-1 rounded">ESTADO: PENDIENTE</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-6">
                        <?php if($carrito && count($detalles) > 0): ?>
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-200 text-xs uppercase text-gray-500 font-semibold">
                                        <th class="py-3">Repuesto</th>
                                        <th class="py-3 text-center">Cant.</th>
                                        <th class="py-3 text-right">Precio Unit.</th>
                                        <th class="py-3 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach($detalles as $item): ?>
                                        <tr>
                                            <td class="py-4">
                                                <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($item['nombre']); ?></p>
                                                <p class="text-xs text-gray-400">SKU: <?php echo htmlspecialchars($item['sku_vin']); ?></p>
                                            </td>
                                            <td class="py-4 text-center text-sm font-medium text-gray-700"><?php echo $item['cantidad']; ?></td>
                                            <td class="py-4 text-right text-sm text-gray-600">Bs. <?php echo number_format($item['precio_venta'], 2); ?></td>
                                            <td class="py-4 text-right text-sm font-bold text-blue-600">Bs. <?php echo number_format($item['subtotal'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <div class="mt-6 pt-6 border-t border-gray-200 flex justify-between items-center">
                                <span class="text-lg font-bold text-gray-700">Total a Pagar:</span>
                                <span class="text-2xl font-black text-slate-800">Bs. <?php echo number_format($total_carrito, 2); ?></span>
                            </div>

                            <!-- Botón para confirmar y enviar el pedido -->
                            <form action="../controllers/confirmarPedidoController.php" method="POST" class="mt-6 text-right">
                                <input type="hidden" name="id_pedido" value="<?php echo $carrito['id_pedido']; ?>">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg shadow-md transition-colors text-lg">
                                    Confirmar y Enviar Pedido
                                </button>
                            </form>

                        <?php else: ?>
                            <div class="text-center py-12">
                                <div class="text-5xl mb-4">🛒</div>
                                <h3 class="text-lg font-bold text-gray-700">Tu carrito está vacío</h3>
                                <p class="text-gray-500 mt-2 mb-6">Aún no has agregado repuestos a tu pedido.</p>
                                <a href="tienda_b2b.php" class="bg-slate-800 text-white px-6 py-2 rounded-lg font-medium hover:bg-slate-700">Ir al Catálogo</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN: HISTORIAL DE PEDIDOS -->
            <div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-slate-50">
                        <h2 class="text-lg font-bold text-slate-800">📋 Historial de Compras</h2>
                    </div>
                    <div class="p-6">
                        <?php if(count($historial) > 0): ?>
                            <div class="space-y-4">
                                <?php foreach($historial as $hist): ?>
                                    <div class="border border-gray-100 p-4 rounded-lg hover:bg-gray-50 transition-colors">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm font-bold text-slate-800">Pedido #<?php echo $hist['id_pedido']; ?></span>
                                            
                                            <?php if($hist['estado'] == 'Aprobado'): ?>
                                                <span class="text-xs font-bold bg-blue-100 text-blue-700 px-2 py-1 rounded">APROBADO</span>
                                            <?php elseif($hist['estado'] == 'En Ruta'): ?>
                                                <span class="text-xs font-bold bg-orange-100 text-orange-700 px-2 py-1 rounded">EN RUTA</span>
                                            <?php elseif($hist['estado'] == 'Entregado'): ?>
                                                <span class="text-xs font-bold bg-green-100 text-green-700 px-2 py-1 rounded">ENTREGADO</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-xs text-gray-500">Fecha: <?php echo date('d/m/Y H:i', strtotime($hist['fecha_pedido'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 text-center py-4">No tienes pedidos anteriores.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </main>
</body>
</html>