<?php
session_start();
if(!isset($_SESSION['usuario_id'])){ header("Location: ../index.php?error=nologin"); exit(); }
require_once '../config/conexion.php';

$database = new Conexion();
$db = $database->getConnection();

// Consulta Avanzada: Trae todo el historial cruzando datos con Artículos y Usuarios
$query = "SELECT k.id_movimiento, k.tipo_movimiento, k.cantidad, k.saldo_actual, k.referencia, k.fecha_movimiento, 
                 a.nombre as articulo_nombre, a.sku_vin, 
                 u.nombre as usuario_nombre 
          FROM kardex k 
          INNER JOIN articulos a ON k.id_articulo = a.id_articulo 
          INNER JOIN usuarios u ON k.id_usuario = u.id_usuario 
          ORDER BY k.id_movimiento DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial Kardex - ComerXync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    <!-- SIDEBAR MODULARIZADO -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Contenido Principal -->
    <main class="flex-1 flex flex-col overflow-hidden">
        
        <!-- Topbar -->
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 shrink-0 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="bg-slate-100 text-slate-600 p-2 rounded-lg">📋</div>
                <h1 class="text-xl font-bold text-slate-800">Libro Mayor de Inventario</h1>
            </div>
            
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <input type="text" id="buscador" onkeyup="buscarKardex()" placeholder="Buscar por Repuesto o Fecha..." 
                           class="w-64 pl-10 pr-4 py-2 bg-gray-100 border-transparent rounded-lg text-sm focus:border-blue-500 focus:bg-white focus:ring-0 outline-none transition-all">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </header>

        <!-- Área de Trabajo -->
        <div class="p-8 flex-1 overflow-auto">
            
            <?php if(isset($_GET['exito']) && $_GET['exito'] == 'venta_ok'): ?>
                <div class="mb-6 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">✅ Salida de almacén registrada correctamente. El Kardex ha sido actualizado.</div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Historial de Movimientos</h2>
                    <p class="text-sm text-gray-500 mt-1">Registro inmutable de todas las transacciones de entrada y salida.</p>
                </div>
                <a href="nuevo_ingreso.php" class="bg-slate-800 hover:bg-slate-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-md flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Registrar Entrada
                </a>
            </div>

            <!-- Tabla del Kardex -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full text-left border-collapse" id="tablaKardex">
                    <thead>
                        <tr class="bg-slate-50 border-b border-gray-200 text-xs uppercase text-gray-500 font-bold tracking-wider">
                            <th class="px-6 py-4">Fecha y Hora</th>
                            <th class="px-6 py-4">Artículo Afectado</th>
                            <th class="px-6 py-4">Operación</th>
                            <th class="px-6 py-4 text-right">Cant.</th>
                            <th class="px-6 py-4 text-right">Saldo Final</th>
                            <th class="px-6 py-4">Referencia / Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(count($movimientos) > 0): ?>
                            <?php foreach($movimientos as $mov): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <!-- Fecha -->
                                    <td class="px-6 py-4 text-sm font-medium text-gray-600 whitespace-nowrap">
                                        <?php echo date('d/m/Y', strtotime($mov['fecha_movimiento'])); ?> <br>
                                        <span class="text-xs text-gray-400"><?php echo date('H:i:s', strtotime($mov['fecha_movimiento'])); ?></span>
                                    </td>
                                    
                                    <!-- Artículo -->
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($mov['articulo_nombre']); ?></p>
                                        <p class="text-xs text-gray-500 font-medium">SKU: <?php echo htmlspecialchars($mov['sku_vin']); ?></p>
                                    </td>
                                    
                                    <!-- Tipo de Movimiento (Etiqueta Verde o Roja) -->
                                    <td class="px-6 py-4">
                                        <?php if($mov['tipo_movimiento'] == 'ENTRADA'): ?>
                                            <span class="px-3 py-1 bg-green-50 border border-green-200 text-green-700 text-xs rounded-lg font-bold shadow-sm flex inline-flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path></svg>
                                                ENTRADA
                                            </span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg font-bold shadow-sm flex inline-flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                                                SALIDA
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Cantidad Movida -->
                                    <td class="px-6 py-4 text-right text-sm font-black <?php echo $mov['tipo_movimiento'] == 'ENTRADA' ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo ($mov['tipo_movimiento'] == 'ENTRADA' ? '+' : '-') . $mov['cantidad']; ?>
                                    </td>
                                    
                                    <!-- Saldo Físico Actual en ese momento -->
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm font-black text-blue-700 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100">
                                            <?php echo $mov['saldo_actual']; ?>
                                        </span>
                                    </td>
                                    
                                    <!-- Referencia y Usuario Responsable -->
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($mov['referencia']); ?></p>
                                        <p class="text-xs text-gray-400">Resp: <?php echo htmlspecialchars($mov['usuario_nombre']); ?></p>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    <div class="text-4xl mb-3">📋</div>
                                    <p class="text-sm font-medium">Aún no se han registrado movimientos en el Kardex.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Script para el buscador del Kardex -->
    <script>
        function buscarKardex() {
            var input, filter, table, tr, tdArticulo, tdFecha, i, txtValueArt, txtValueFecha;
            input = document.getElementById("buscador");
            filter = input.value.toUpperCase();
            table = document.getElementById("tablaKardex");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                tdFecha = tr[i].getElementsByTagName("td")[0];
                tdArticulo = tr[i].getElementsByTagName("td")[1];
                
                if (tdArticulo || tdFecha) {
                    txtValueArt = tdArticulo.textContent || tdArticulo.innerText;
                    txtValueFecha = tdFecha.textContent || tdFecha.innerText;
                    
                    if (txtValueArt.toUpperCase().indexOf(filter) > -1 || txtValueFecha.toUpperCase().indexOf(filter) > -1) {
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