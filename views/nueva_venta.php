<?php
// Archivo: views/nueva_venta.php
session_start();
if(!isset($_SESSION['usuario_id'])){ header("Location: ../index.php?error=nologin"); exit(); }
require_once '../config/conexion.php';

$database = new Conexion();
$db = $database->getConnection();

// Traemos los artículos con su precio y stock actual en Kardex
$query = "SELECT a.id_articulo, a.sku_vin, a.nombre, a.categoria, a.precio_venta, 
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta POS - ComerXync AutoParts</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }

        /* Glassmorphism Vercel/Stripe style */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .glass-card:hover {
            box-shadow: 0 12px 30px -4px rgba(15, 23, 42, 0.08), 0 4px 12px -2px rgba(15, 23, 42, 0.04);
            border-color: rgba(203, 213, 225, 1);
        }

        /* Subtle scrollbar */
        .scrollbar-saas::-webkit-scrollbar { width: 5px; height: 5px; }
        .scrollbar-saas::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-saas::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        .scrollbar-saas::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 flex h-screen overflow-hidden antialiased">

    <!-- TOAST NOTIFICATION FLOTANTE EN CASO DE ERROR (Auto-dismiss en 3 segundos) -->
    <?php if(isset($_GET['error'])): ?>
    <div id="toast-notification" class="fixed top-6 right-6 z-50 flex items-center gap-3.5 px-5 py-4 bg-white/95 backdrop-blur-xl border border-rose-200/90 text-slate-800 rounded-2xl shadow-[0_12px_36px_-6px_rgba(244,63,94,0.3)] transition-all duration-500 transform translate-y-0 opacity-100">
        <div class="w-9 h-9 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
            <p class="text-[11px] font-black uppercase tracking-wider text-rose-600">Alerta de Operación</p>
            <p class="text-xs font-semibold text-slate-700"><?php echo htmlspecialchars($_GET['error']); ?></p>
        </div>
        <button onclick="dismissToast()" class="ml-2 text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <script>
        setTimeout(() => { dismissToast(); }, 3000);
        function dismissToast() {
            const toast = document.getElementById('toast-notification');
            if(toast) {
                toast.classList.add('opacity-0', '-translate-y-4', 'scale-95');
                setTimeout(() => toast.remove(), 400);
            }
        }
    </script>
    <?php endif; ?>

    <!-- SIDEBAR MODULARIZADO (Menú Maestro) -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-1 flex flex-col overflow-hidden bg-[#f8fafc]">

        <!-- ═══════════════ HEADER SUPERIOR SAAS ═══════════════ -->
        <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-8 flex items-center justify-between shrink-0 z-10">
            <!-- Left: Título y descripción -->
            <div class="flex items-center gap-3.5">
                <div class="w-9 h-9 rounded-xl bg-blue-600/10 border border-blue-600/20 flex items-center justify-center text-blue-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">Punto de Venta (POS)</h1>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Terminal de facturación, caja registradora y despacho directo de almacén.</p>
                </div>
            </div>

            <!-- Right: Indicador del operador de caja -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2.5 px-4 py-2 bg-slate-100/80 border border-slate-200/80 rounded-xl">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-semibold text-slate-700">
                        Cajero: <strong class="text-slate-900 font-bold"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></strong>
                    </span>
                </div>
            </div>
        </header>

        <!-- ═══════════════ ÁREA DE TRABAJO SCROLLABLE ═══════════════ -->
        <div class="flex-1 overflow-y-auto px-8 py-7 scrollbar-saas">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-7 items-start">
                
                <!-- ── COLUMNA IZQUIERDA: Formulario de Selección con Floating Labels ── -->
                <div class="lg:col-span-2 glass-card rounded-2xl overflow-hidden border border-slate-200/80">
                    <!-- Cabecera del panel de escaneo -->
                    <div class="px-7 py-4 border-b border-slate-100 bg-white/50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-4 rounded-full bg-blue-600"></span>
                            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">Selección y Escáner de Repuestos</h2>
                        </div>
                        <span class="text-[11px] font-bold text-slate-400 font-mono">SALIDA KARDEX AUTOMÁTICA</span>
                    </div>
                    
                    <div class="p-7">
                        <form id="formVenta" action="../controllers/ventaController.php" method="POST" class="space-y-6">
                            
                            <!-- 1. BÚSQUEDA DE ARTÍCULO CON FLOATING LABEL -->
                            <div class="relative group">
                                <select name="id_articulo" id="select_articulo" required onchange="calcularTotal()" 
                                        class="peer w-full px-4 pt-5 pb-2.5 bg-white/90 border border-slate-200/90 rounded-xl text-sm font-semibold text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:shadow-[0_0_20px_rgba(59,130,246,0.15)] transition-all duration-200 appearance-none cursor-pointer">
                                    <option value="" data-precio="0" data-stock="0" data-sku="—" data-cat="—">-- Seleccione o escanee un repuesto --</option>
                                    
                                    <?php foreach($articulos as $art): ?>
                                        <option value="<?php echo $art['id_articulo']; ?>" 
                                                data-precio="<?php echo $art['precio_venta']; ?>" 
                                                data-stock="<?php echo $art['stock']; ?>"
                                                data-sku="<?php echo htmlspecialchars($art['sku_vin']); ?>"
                                                data-cat="<?php echo htmlspecialchars($art['categoria'] ?? 'Repuesto'); ?>"
                                                <?php echo $art['stock'] <= 0 ? 'disabled' : ''; ?>>
                                            [<?php echo htmlspecialchars($art['sku_vin']); ?>] <?php echo htmlspecialchars($art['nombre']); ?> 
                                            &bull; Stock: <?php echo $art['stock']; ?> uds 
                                            &bull; Bs. <?php echo number_format($art['precio_venta'], 2); ?>
                                            <?php echo $art['stock'] <= 0 ? ' (AGOTADO)' : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="select_articulo" 
                                       class="absolute left-4 top-1.5 text-[9px] font-bold text-slate-400 uppercase tracking-wider transition-all duration-200 pointer-events-none peer-focus:text-blue-600 peer-focus:uppercase peer-focus:tracking-wider">
                                    Artículo / Repuesto del Catálogo
                                </label>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>

                            <!-- TARJETA DINÁMICA DE PREVISUALIZACIÓN DEL PRODUCTO SELECCIONADO -->
                            <div id="preview_card" class="hidden p-4 rounded-xl bg-slate-50/90 border border-slate-200/80 transition-all">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0">
                                            📦
                                        </div>
                                        <div>
                                            <p id="preview_nombre" class="text-xs font-bold text-slate-800 leading-tight">Repuesto</p>
                                            <p class="text-[10px] text-slate-400 font-mono mt-0.5">
                                                SKU: <span id="preview_sku" class="font-bold text-slate-600">—</span> &bull; 
                                                Categoría: <span id="preview_cat" class="font-bold text-slate-600">—</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] text-slate-400 font-medium">Stock Disponible</p>
                                        <span id="preview_stock" class="text-xs font-black text-emerald-600">0 uds</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. CAMPOS EN GRILLA CON FLOATING LABELS -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <!-- Cantidad -->
                                <div>
                                    <div class="relative group">
                                        <input type="number" name="cantidad" id="input_cantidad" required min="1" value="1" oninput="calcularTotal()" placeholder=" "
                                            class="peer w-full px-4 pt-5 pb-2 bg-white/90 border border-slate-200/90 rounded-xl text-center text-xl font-black text-slate-800 placeholder-transparent focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:shadow-[0_0_20px_rgba(59,130,246,0.15)] transition-all duration-200">
                                        <label for="input_cantidad"
                                            class="absolute left-0 right-0 text-center top-1.5 text-[9px] font-bold text-slate-400 uppercase tracking-wider transition-all duration-200 pointer-events-none peer-focus:text-blue-600 peer-focus:uppercase peer-focus:tracking-wider">
                                            Cantidad a Despachar
                                        </label>
                                    </div>
                                    <p id="msg_stock" class="text-xs text-rose-500 mt-2 font-bold hidden flex items-center justify-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>¡Supera el stock físico disponible!</span>
                                    </p>
                                </div>
                                
                                <!-- Nro de Comprobante / Factura -->
                                <div>
                                    <div class="relative group">
                                        <input type="text" name="referencia" id="input_referencia" required placeholder=" " value="TKT-<?php echo time(); ?>"
                                            class="peer w-full px-4 pt-5 pb-2 bg-white/90 border border-slate-200/90 rounded-xl text-sm font-mono font-bold text-slate-800 uppercase placeholder-transparent focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:shadow-[0_0_20px_rgba(59,130,246,0.15)] transition-all duration-200">
                                        <label for="input_referencia"
                                            class="absolute left-4 top-1.5 text-[9px] font-bold text-slate-400 uppercase tracking-wider transition-all duration-200 pointer-events-none peer-focus:text-blue-600 peer-focus:uppercase peer-focus:tracking-wider">
                                            Nro. Comprobante / Ticket
                                        </label>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1.5 ml-1">Identificador fiscal o correlativo interno.</p>
                                </div>
                            </div>

                        </form>
                    </div>

                    <!-- Footer Informativo -->
                    <div class="px-7 py-3.5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs text-slate-400">
                        <span>Regla de negocio: Descuento inmediato sobre saldo kardex</span>
                        <span class="font-semibold text-slate-600">Moneda: Bolivianos (Bs.)</span>
                    </div>
                </div>

                <!-- ── COLUMNA DERECHA: Resumen de Cobro (Ticket Digital SaaS) ── -->
                <div class="bg-slate-950 rounded-2xl shadow-2xl border border-slate-900 flex flex-col overflow-hidden relative text-white">
                    
                    <!-- Decorative subtle top gradient bar -->
                    <div class="h-1.5 bg-gradient-to-r from-blue-600 via-indigo-500 to-cyan-400"></div>

                    <div class="p-8 flex-1 flex flex-col justify-between">
                        
                        <!-- Header del Ticket -->
                        <div>
                            <div class="flex items-center justify-between pb-4 border-b border-slate-800/80 mb-6">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">ComerXync AutoParts</p>
                                    <p class="text-xs text-slate-500 font-medium mt-0.5">Terminal POS &bull; Caja #01</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-mono font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    EN VIVO
                                </span>
                            </div>

                            <!-- Monto Principal -->
                            <div class="mb-8">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total a Cobrar</span>
                                <div class="text-4xl font-black text-white tracking-tight mt-1">
                                    Bs. <span id="display_total">0.00</span>
                                </div>
                            </div>

                            <!-- Desglose -->
                            <div class="space-y-3 border-t border-slate-800/80 pt-5 text-xs">
                                <div class="flex justify-between text-slate-400">
                                    <span>Subtotal Neto</span>
                                    <span class="font-mono text-slate-200">Bs. <span id="display_subtotal">0.00</span></span>
                                </div>
                                <div class="flex justify-between text-slate-400">
                                    <span>Impuestos de Ley (0%)</span>
                                    <span class="font-mono text-slate-200">Bs. 0.00</span>
                                </div>
                                <div class="flex justify-between text-slate-400">
                                    <span>Descuento Comercial</span>
                                    <span class="font-mono text-slate-200">Bs. 0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Botón de Pago / Confirmar Venta -->
                        <div class="mt-8 pt-5 border-t border-slate-800/80">
                            <button type="submit" form="formVenta" id="btn_cobrar" disabled
                                class="w-full py-3.5 rounded-xl text-sm font-bold transition-all duration-300 bg-slate-800 text-slate-500 cursor-not-allowed flex justify-center items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Cobrar y Emitir Comprobante</span>
                            </button>
                            <p class="text-[10px] text-center text-slate-500 mt-2.5">
                                La transacción generará un asiento inmutable de salida.
                            </p>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- ═══════════════ SCRIPT PARA CÁLCULO DE TOTALES ═══════════════ -->
    <script>
        function calcularTotal() {
            const select = document.getElementById('select_articulo');
            const cantidad = parseInt(document.getElementById('input_cantidad').value) || 0;
            const btnCobrar = document.getElementById('btn_cobrar');
            const msgStock = document.getElementById('msg_stock');
            
            const opcionSeleccionada = select.options[select.selectedIndex];
            const precio = parseFloat(opcionSeleccionada.getAttribute('data-precio')) || 0;
            const stockMaximo = parseInt(opcionSeleccionada.getAttribute('data-stock')) || 0;
            const sku = opcionSeleccionada.getAttribute('data-sku') || '—';
            const cat = opcionSeleccionada.getAttribute('data-cat') || '—';

            // Actualizar tarjeta de previsualización
            const previewCard = document.getElementById('preview_card');
            const previewNombre = document.getElementById('preview_nombre');
            const previewSku = document.getElementById('preview_sku');
            const previewCat = document.getElementById('preview_cat');
            const previewStock = document.getElementById('preview_stock');

            if(select.value !== "") {
                previewCard.classList.remove('hidden');
                previewNombre.innerText = opcionSeleccionada.text.split('- Disp:')[0].trim();
                previewSku.innerText = sku;
                previewCat.innerText = cat;
                previewStock.innerText = stockMaximo + ' uds';
            } else {
                previewCard.classList.add('hidden');
            }

            const total = precio * cantidad;

            document.getElementById('display_total').innerText = total.toFixed(2);
            document.getElementById('display_subtotal').innerText = total.toFixed(2);

            if(precio > 0 && cantidad > 0 && cantidad <= stockMaximo) {
                btnCobrar.disabled = false;
                btnCobrar.className = "w-full py-3.5 rounded-xl text-sm font-bold transition-all duration-300 bg-blue-600 hover:bg-blue-500 active:scale-[0.99] text-white shadow-[0_0_20px_rgba(37,99,235,0.4)] flex justify-center items-center gap-2 cursor-pointer";
                msgStock.classList.add('hidden');
            } else {
                btnCobrar.disabled = true;
                btnCobrar.className = "w-full py-3.5 rounded-xl text-sm font-bold transition-all duration-300 bg-slate-800 text-slate-500 cursor-not-allowed flex justify-center items-center gap-2";
                
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
