<?php
session_start();
if(!isset($_SESSION['usuario_id'])){ header("Location: ../index.php?error=nologin"); exit(); }
require_once '../config/conexion.php';

$database = new Conexion();
$db = $database->getConnection();

// Traemos los artículos con su precio y stock actual
$query = "SELECT a.id_articulo, a.sku_vin, a.nombre, a.precio_venta, 
          COALESCE((SELECT saldo_actual FROM kardex k WHERE k.id_articulo = a.id_articulo ORDER BY id_movimiento DESC LIMIT 1), 0) as stock 
          FROM articulos a ORDER BY a.nombre ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Punto de Venta POS - ComerXync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    <!-- SIDEBAR MODULARIZADO (AQUÍ ESTÁ LA MAGIA) -->
    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col overflow-hidden bg-gray-50">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-8 shrink-0 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="bg-blue-100 text-blue-600 p-2 rounded-lg">🛒</div>
                <h1 class="text-xl font-bold text-slate-800">Caja Registradora / POS</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-600">Cajero: <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
            </div>
        </header>

        <div class="p-8 flex-1 overflow-auto">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 h-full">
                
                <!-- COLUMNA IZQUIERDA: Formulario de Selección -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col h-fit overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-gray-100">
                        <h2 class="text-lg font-bold text-slate-800">Escáner de Productos</h2>
                    </div>
                    
                    <div class="p-6">
                        <form id="formVenta" action="../controllers/ventaController.php" method="POST" class="space-y-6">
                            
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Búsqueda de Artículo (SKU / Nombre)</label>
                                <select name="id_articulo" id="select_articulo" required onchange="calcularTotal()" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-slate-700 font-medium bg-gray-50">
                                    <option value="" data-precio="0" data-stock="0">-- Seleccione o escanee un repuesto --</option>
                                    
                                    <?php foreach($articulos as $art): ?>
                                        <option value="<?php echo $art['id_articulo']; ?>" 
                                                data-precio="<?php echo $art['precio_venta']; ?>" 
                                                data-stock="<?php echo $art['stock']; ?>"
                                                <?php echo $art['stock'] <= 0 ? 'disabled' : ''; ?>>
                                            [<?php echo htmlspecialchars($art['sku_vin']); ?>] <?php echo htmlspecialchars($art['nombre']); ?> 
                                            - Disp: <?php echo $art['stock']; ?> uds 
                                            - Bs. <?php echo number_format($art['precio_venta'], 2); ?>
                                            <?php echo $art['stock'] <= 0 ? ' (AGOTADO)' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Cantidad Unidades</label>
                                    <input type="number" name="cantidad" id="input_cantidad" required min="1" value="1" oninput="calcularTotal()"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-center text-xl font-black text-slate-800 bg-gray-50">
                                    <p id="msg_stock" class="text-xs text-red-500 mt-1 font-medium hidden">¡Supera el stock disponible!</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Nro. de Comprobante / Factura</label>
                                    <input type="text" name="referencia" required placeholder="Ej: TICKET-001" value="TKT-<?php echo time(); ?>"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-medium text-slate-700 bg-gray-50 uppercase">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: Resumen (Ticket) y Botón de Pago -->
                <div class="bg-slate-900 rounded-2xl shadow-xl flex flex-col overflow-hidden relative">
                    <div class="absolute top-0 right-0 p-3 opacity-10 text-6xl">🛒</div>
                    
                    <div class="p-8 flex-1 flex flex-col justify-center">
                        <h3 class="text-slate-400 font-medium text-sm uppercase tracking-wider mb-2">Total a Cobrar</h3>
                        <div class="text-white text-5xl font-black tracking-tight mb-8">
                            Bs. <span id="display_total">0.00</span>
                        </div>

                        <div class="space-y-4 border-t border-slate-700 pt-6">
                            <div class="flex justify-between text-slate-300 text-sm">
                                <span>Subtotal</span>
                                <span>Bs. <span id="display_subtotal">0.00</span></span>
                            </div>
                            <div class="flex justify-between text-slate-300 text-sm">
                                <span>Impuestos (0%)</span>
                                <span>Bs. 0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-950">
                        <button type="submit" form="formVenta" id="btn_cobrar" disabled
                            class="w-full py-4 rounded-xl text-lg font-bold transition-all duration-300 bg-slate-700 text-slate-400 cursor-not-allowed flex justify-center items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Cobrar y Emitir Ticket
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- SCRIPT DE MAGIA PARA CALCULAR EL TOTAL -->
    <script>
        function calcularTotal() {
            const select = document.getElementById('select_articulo');
            const cantidad = parseInt(document.getElementById('input_cantidad').value) || 0;
            const btnCobrar = document.getElementById('btn_cobrar');
            const msgStock = document.getElementById('msg_stock');
            
            const opcionSeleccionada = select.options[select.selectedIndex];
            const precio = parseFloat(opcionSeleccionada.getAttribute('data-precio')) || 0;
            const stockMaximo = parseInt(opcionSeleccionada.getAttribute('data-stock')) || 0;

            const total = precio * cantidad;

            document.getElementById('display_total').innerText = total.toFixed(2);
            document.getElementById('display_subtotal').innerText = total.toFixed(2);

            if(precio > 0 && cantidad > 0 && cantidad <= stockMaximo) {
                btnCobrar.disabled = false;
                btnCobrar.className = "w-full py-4 rounded-xl text-lg font-bold transition-all duration-300 bg-blue-600 hover:bg-blue-500 text-white shadow-[0_0_15px_rgba(37,99,235,0.5)] flex justify-center items-center gap-2 transform hover:scale-[1.02]";
                msgStock.classList.add('hidden');
            } else {
                btnCobrar.disabled = true;
                btnCobrar.className = "w-full py-4 rounded-xl text-lg font-bold transition-all duration-300 bg-slate-700 text-slate-400 cursor-not-allowed flex justify-center items-center gap-2";
                
                if(cantidad > stockMaximo && select.value !== "") {
                    msgStock.classList.remove('hidden');
                } else {
                    msgStock.classList.add('hidden');
                }
            }
        }
    </script>
</body>
</html>