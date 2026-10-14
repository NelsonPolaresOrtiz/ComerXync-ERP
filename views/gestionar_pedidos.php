<?php
// Archivo: views/gestionar_pedidos.php
session_start();
// Bloqueamos a los clientes para que no vean los pedidos de otros
if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] == 'Cliente_B2B'){ 
    header("Location: ../index.php?error=acceso_denegado"); 
    exit(); 
}
require_once '../config/conexion.php';

$database = new Conexion();
$db = $database->getConnection();

// Consulta avanzada: Trae el pedido, el nombre del cliente y calcula el TOTAL en dinero
$query = "SELECT p.id_pedido, p.fecha_pedido, p.estado, u.nombre as cliente,
                 (SELECT COALESCE(SUM(dp.cantidad * a.precio_venta), 0) 
                  FROM detalle_pedidos dp 
                  INNER JOIN articulos a ON dp.id_articulo = a.id_articulo 
                  WHERE dp.id_pedido = p.id_pedido) as total
          FROM pedidos p
          INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
          -- Solo mostramos pedidos que el cliente ya confirmó (No los que siguen en su carrito 'Pendiente')
          WHERE p.estado != 'Pendiente' 
          ORDER BY p.id_pedido DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Pedidos - ComerXync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    
    <!-- SIDEBAR MODULARIZADO -->
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden">
        
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 shrink-0 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="bg-slate-100 text-slate-600 p-2 rounded-lg">📦</div>
                <h1 class="text-xl font-bold text-slate-800">Gestión de Pedidos (E-commerce)</h1>
            </div>
            
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <input type="text" id="buscador" onkeyup="buscarPedido()" placeholder="Buscar cliente o pedido..." 
                           class="w-64 pl-10 pr-4 py-2 bg-gray-100 border-transparent rounded-lg text-sm focus:border-blue-500 focus:bg-white focus:ring-0 outline-none transition-all">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </header>

        <div class="p-8 flex-1 overflow-auto">
            
            <?php if(isset($_GET['exito']) && $_GET['exito'] == 'actualizado'): ?>
                <div class="mb-6 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">✅ El estado del pedido se ha actualizado y el cliente será notificado en su portal.</div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Bandeja de Entrada de Pedidos</h2>
                    <p class="text-sm text-gray-500 mt-1">Administra el despacho y entrega de las compras online.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full text-left border-collapse" id="tablaPedidos">
                    <thead>
                        <tr class="bg-slate-50 border-b border-gray-200 text-xs uppercase text-gray-500 font-bold tracking-wider">
                            <th class="px-6 py-4">ID Pedido</th>
                            <th class="px-6 py-4">Cliente (Taller)</th>
                            <th class="px-6 py-4">Fecha y Hora</th>
                            <th class="px-6 py-4 text-right">Total a Cobrar</th>
                            <th class="px-6 py-4 text-center">Estado y Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(count($pedidos) > 0): ?>
                            <?php foreach($pedidos as $ped): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-black text-slate-800">#<?php echo str_pad($ped['id_pedido'], 5, "0", STR_PAD_LEFT); ?></td>
                                    <td class="px-6 py-4 text-sm font-bold text-blue-600"><?php echo htmlspecialchars($ped['cliente']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?php echo date('d/m/Y', strtotime($ped['fecha_pedido'])); ?> <br>
                                        <span class="text-xs text-gray-400"><?php echo date('H:i:s', strtotime($ped['fecha_pedido'])); ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-black text-slate-800">
                                        Bs. <?php echo number_format($ped['total'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <!-- Formulario para cambiar el estado rápido -->
                                        <form action="../controllers/actualizarPedidoController.php" method="POST" class="flex items-center justify-center gap-2">
                                            <input type="hidden" name="id_pedido" value="<?php echo $ped['id_pedido']; ?>">
                                            <select name="estado" class="text-sm font-bold border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500
                                                <?php 
                                                    if($ped['estado'] == 'Aprobado') echo 'bg-blue-50 text-blue-700';
                                                    elseif($ped['estado'] == 'En Ruta') echo 'bg-orange-50 text-orange-700';
                                                    elseif($ped['estado'] == 'Entregado') echo 'bg-green-50 text-green-700';
                                                ?>">
                                                <option value="Aprobado" <?php if($ped['estado'] == 'Aprobado') echo 'selected'; ?>>APROBADO</option>
                                                <option value="En Ruta" <?php if($ped['estado'] == 'En Ruta') echo 'selected'; ?>>EN RUTA</option>
                                                <option value="Entregado" <?php if($ped['estado'] == 'Entregado') echo 'selected'; ?>>ENTREGADO</option>
                                            </select>
                                            <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                                Guardar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    <div class="text-4xl mb-3">📬</div>
                                    <p class="text-sm font-medium">Bandeja limpia. No hay pedidos confirmados por los clientes.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function buscarPedido() {
            var input = document.getElementById("buscador");
            var filter = input.value.toUpperCase();
            var table = document.getElementById("tablaPedidos");
            var tr = table.getElementsByTagName("tr");

            for (var i = 1; i < tr.length; i++) {
                var tdId = tr[i].getElementsByTagName("td")[0];
                var tdCliente = tr[i].getElementsByTagName("td")[1];
                if (tdId || tdCliente) {
                    var txtId = tdId.textContent || tdId.innerText;
                    var txtCliente = tdCliente.textContent || tdCliente.innerText;
                    if (txtId.toUpperCase().indexOf(filter) > -1 || txtCliente.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }       
            }
        }
    </script>
</body>
</html>