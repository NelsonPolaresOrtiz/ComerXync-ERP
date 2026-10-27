<?php
// Archivo: views/kardex.php
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

// Métricas KPI de resumen
$totalMovimientos = count($movimientos);
$totalEntradas    = 0;
$totalSalidas     = 0;
foreach ($movimientos as $m) {
    if ($m['tipo_movimiento'] === 'ENTRADA') $totalEntradas++;
    if ($m['tipo_movimiento'] === 'SALIDA')  $totalSalidas++;
}
$ultimoRegistro = ($totalMovimientos > 0)
    ? date('d/m/Y H:i', strtotime($movimientos[0]['fecha_movimiento']))
    : 'Sin registros';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libro Mayor Kardex - ComerXync AutoParts</title>
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
            transform: translateY(-2px);
            border-color: rgba(203, 213, 225, 1);
        }

        /* Subtle scrollbar */
        .scrollbar-saas::-webkit-scrollbar { width: 5px; height: 5px; }
        .scrollbar-saas::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-saas::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        .scrollbar-saas::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Row hover transition */
        #tablaKardex tbody tr {
            transition: background-color 0.15s ease, border-left-color 0.15s ease;
        }

        /* Pill filter active styles */
        .tipo-btn.active-all {
            background-color: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
            box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.15);
        }
        .tipo-btn.active-in {
            background-color: #059669;
            color: #ffffff;
            border-color: #059669;
            box-shadow: 0 4px 12px -2px rgba(5, 150, 105, 0.2);
        }
        .tipo-btn.active-out {
            background-color: #e11d48;
            color: #ffffff;
            border-color: #e11d48;
            box-shadow: 0 4px 12px -2px rgba(225, 29, 72, 0.2);
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 flex h-screen overflow-hidden antialiased">

    <!-- TOAST NOTIFICATION FLOTANTE (Auto-dismiss en 3 segundos) -->
    <?php if(isset($_GET['exito']) && $_GET['exito'] === 'venta_ok'): ?>
    <div id="toast-notification" class="fixed top-6 right-6 z-50 flex items-center gap-3.5 px-5 py-4 bg-white/95 backdrop-blur-xl border border-emerald-200/90 text-slate-800 rounded-2xl shadow-[0_12px_36px_-6px_rgba(16,185,129,0.3)] transition-all duration-500 transform translate-y-0 opacity-100">
        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <p class="text-[11px] font-black uppercase tracking-wider text-emerald-600">Salida de Almacén Exitosa</p>
            <p class="text-xs font-semibold text-slate-700">El movimiento fue registrado y el saldo del Kardex ha sido recalculado.</p>
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
                <div class="w-9 h-9 rounded-xl bg-indigo-600/10 border border-indigo-600/20 flex items-center justify-center text-indigo-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">Libro Mayor Kardex</h1>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Auditoría inmutable de movimientos logísticos, entradas y despachos.</p>
                </div>
            </div>

            <!-- Right: Buscador con Floating Label + Botón Registrar Entrada -->
            <div class="flex items-center gap-3.5">
                <!-- FORMULARIO / BUSCADOR CON FLOATING LABEL Y SOMBRA DINÁMICA -->
                <div class="relative group">
                    <input type="text" id="buscador" onkeyup="buscarKardex()" placeholder=" "
                           class="peer w-72 pl-10 pr-4 pt-4 pb-1.5 bg-white/90 border border-slate-200/90 rounded-xl text-xs font-semibold text-slate-800 placeholder-transparent focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:shadow-[0_0_20px_rgba(99,102,241,0.15)] transition-all duration-200">
                    <label for="buscador"
                           class="absolute left-10 top-1 text-[9px] font-bold text-slate-400 uppercase tracking-wider transition-all duration-200 pointer-events-none peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-xs peer-placeholder-shown:font-medium peer-placeholder-shown:text-slate-400 peer-placeholder-shown:normal-case peer-focus:top-1 peer-focus:text-[9px] peer-focus:font-bold peer-focus:text-indigo-600 peer-focus:uppercase peer-focus:tracking-wider">
                        Buscar artículo o fecha...
                    </label>
                    <svg class="w-4 h-4 text-slate-400 peer-focus:text-indigo-500 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- Botón Registrar Entrada -->
                <a href="nuevo_ingreso.php"
                   class="inline-flex items-center gap-2 bg-slate-900 hover:bg-indigo-600 active:scale-95 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Registrar Entrada
                </a>
            </div>
        </header>

        <!-- ═══════════════ ÁREA DE TRABAJO SCROLLABLE ═══════════════ -->
        <div class="flex-1 overflow-y-auto px-8 py-7 space-y-6 scrollbar-saas">

            <!-- ── SECCIÓN SUPERIOR: TÍTULO Y FILTROS POR OPERACIÓN ── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-base font-black text-slate-900 tracking-tight">Historial de Transacciones Físicas</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Control cronológico y saldos finales registrados por usuario responsable.</p>
                </div>

                <!-- Type Filter Pills -->
                <div class="flex items-center gap-1.5 p-1 bg-slate-100/80 rounded-2xl border border-slate-200/80 shadow-2xs" id="tipoFilters">
                    <button onclick="filterTipo('todos')"
                            id="btn-todos"
                            class="tipo-btn active-all px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-150">
                        Todos
                    </button>
                    <button onclick="filterTipo('ENTRADA')"
                            id="btn-entrada"
                            class="tipo-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-600 hover:text-emerald-600 transition-all duration-150">
                        Entradas
                    </button>
                    <button onclick="filterTipo('SALIDA')"
                            id="btn-salida"
                            class="tipo-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-600 hover:text-rose-600 transition-all duration-150">
                        Salidas
                    </button>
                </div>
            </div>

            <!-- ── SECCIÓN KPI CARDS (GLASSMORPHISM) ── -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Total Movimientos -->
                <div class="glass-card rounded-2xl p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Movimientos</p>
                        <p class="text-2xl font-black text-slate-900 leading-tight mt-0.5"><?php echo $totalMovimientos; ?></p>
                        <p class="text-[10px] text-slate-400 font-medium">Asientos registrados</p>
                    </div>
                </div>

                <!-- Total Entradas -->
                <div class="glass-card rounded-2xl p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Entradas</p>
                        <p class="text-2xl font-black text-emerald-600 leading-tight mt-0.5"><?php echo $totalEntradas; ?></p>
                        <p class="text-[10px] text-emerald-600 font-medium">Compras e ingresos</p>
                    </div>
                </div>

                <!-- Total Salidas -->
                <div class="glass-card rounded-2xl p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Salidas</p>
                        <p class="text-2xl font-black text-rose-600 leading-tight mt-0.5"><?php echo $totalSalidas; ?></p>
                        <p class="text-[10px] text-rose-500 font-medium">Ventas y despachos</p>
                    </div>
                </div>

                <!-- Último Registro -->
                <div class="glass-card rounded-2xl p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Última Operación</p>
                        <p class="text-sm font-black text-slate-900 leading-tight mt-0.5 truncate">
                            <?php echo htmlspecialchars($ultimoRegistro); ?>
                        </p>
                        <p class="text-[10px] text-slate-400 font-medium">Fecha y hora del asiento</p>
                    </div>
                </div>

            </div>

            <!-- ── TABLA DE DATOS DEL KARDEX ── -->
            <div class="glass-card rounded-2xl overflow-hidden border border-slate-200/80">

                <!-- Table Header Bar -->
                <div class="px-7 py-4 border-b border-slate-100 flex items-center justify-between bg-white/50">
                    <p class="text-xs font-semibold text-slate-600">
                        <span id="rowCount" class="text-slate-900 font-black"><?php echo $totalMovimientos; ?></span>
                        asientos en el libro mayor
                    </p>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                        <span class="text-xs text-slate-400 font-medium">Registros inmutables auditados</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="tablaKardex">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200/80 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
                                <th class="px-7 py-3.5">Fecha y Hora</th>
                                <th class="px-7 py-3.5">Artículo Afectado</th>
                                <th class="px-7 py-3.5 text-center">Operación</th>
                                <th class="px-7 py-3.5 text-right">Cantidad</th>
                                <th class="px-7 py-3.5 text-right">Saldo Final</th>
                                <th class="px-7 py-3.5">Referencia / Responsable</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">

                            <?php if(empty($movimientos)): ?>
                                <tr>
                                    <td colspan="6" class="px-7 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm font-bold text-slate-700">No se registran movimientos en el libro mayor.</p>
                                            <a href="nuevo_ingreso.php" class="text-xs text-indigo-600 font-bold hover:underline">+ Registrar primer ingreso</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($movimientos as $mov): ?>
                                <?php
                                    $esEntrada = ($mov['tipo_movimiento'] === 'ENTRADA');
                                    $borderTimeline = $esEntrada ? 'border-l-4 border-emerald-500' : 'border-l-4 border-rose-500';
                                ?>
                                <tr class="<?php echo $borderTimeline; ?> hover:bg-slate-50/80 transition-colors duration-150 group"
                                    data-tipo="<?php echo htmlspecialchars($mov['tipo_movimiento']); ?>">

                                    <!-- TD[0] — Fecha y Hora (JS buscarKardex busca en este elemento) -->
                                    <td class="px-7 py-4 whitespace-nowrap">
                                        <p class="text-xs font-bold text-slate-800">
                                            <?php echo date('d/m/Y', strtotime($mov['fecha_movimiento'])); ?>
                                        </p>
                                        <p class="text-[10px] text-slate-400 font-medium mt-0.5">
                                            <?php echo date('H:i:s', strtotime($mov['fecha_movimiento'])); ?>
                                        </p>
                                    </td>

                                    <!-- TD[1] — Artículo (JS buscarKardex busca en este elemento) -->
                                    <td class="px-7 py-4">
                                        <p class="text-sm font-bold text-slate-800 leading-snug group-hover:text-indigo-600 transition-colors">
                                            <?php echo htmlspecialchars($mov['articulo_nombre']); ?>
                                        </p>
                                        <p class="text-[11px] text-slate-400 font-mono mt-0.5">
                                            <span class="bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-bold">SKU: <?php echo htmlspecialchars($mov['sku_vin']); ?></span>
                                        </p>
                                    </td>

                                    <!-- TD[2] — Tipo de Movimiento -->
                                    <td class="px-7 py-4 text-center whitespace-nowrap">
                                        <?php if($esEntrada): ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200/90 shadow-2xs">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                                </svg>
                                                ENTRADA
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200/90 shadow-2xs">
                                                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                                </svg>
                                                SALIDA
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- TD[3] — Cantidad Movida -->
                                    <td class="px-7 py-4 text-right whitespace-nowrap">
                                        <?php if($esEntrada): ?>
                                            <span class="text-sm font-black text-emerald-600">
                                                +<?php echo htmlspecialchars($mov['cantidad']); ?> uds
                                            </span>
                                        <?php else: ?>
                                            <span class="text-sm font-black text-rose-600">
                                                -<?php echo htmlspecialchars($mov['cantidad']); ?> uds
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- TD[4] — Saldo Final -->
                                    <td class="px-7 py-4 text-right whitespace-nowrap">
                                        <span class="inline-block px-3 py-1 rounded-xl bg-indigo-50 border border-indigo-200/80 text-indigo-700 font-black text-xs shadow-2xs">
                                            <?php echo htmlspecialchars($mov['saldo_actual']); ?> uds
                                        </span>
                                    </td>

                                    <!-- TD[5] — Referencia / Responsable -->
                                    <td class="px-7 py-4">
                                        <p class="text-xs font-semibold text-slate-800 leading-snug truncate max-w-xs" title="<?php echo htmlspecialchars($mov['referencia']); ?>">
                                            <?php echo htmlspecialchars($mov['referencia']); ?>
                                        </p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">
                                            Usuario: <span class="text-slate-600 font-semibold"><?php echo htmlspecialchars($mov['usuario_nombre']); ?></span>
                                        </p>
                                    </td>

                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>

                <!-- Table Footer -->
                <div class="px-7 py-3.5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <p class="text-xs text-slate-400 font-medium">
                        Total asientos listados: <strong class="text-slate-700" id="visibleCount"><?php echo $totalMovimientos; ?></strong>
                    </p>
                    <p class="text-xs text-slate-400 font-medium hidden sm:block">
                        Consulta ejecutada: <span class="font-semibold text-slate-600"><?php echo date('d/m/Y H:i:s'); ?></span>
                    </p>
                </div>

            </div>

        </div>
    </main>

    <!-- ═══════════════ JAVASCRIPT DE BÚSQUEDA Y FILTRADO ═══════════════ -->
    <script>
        /* ─────────────────────────────────────────────────────────────
         * buscarKardex() — Mantenida exactamente como la original
         * ───────────────────────────────────────────────────────────── */
        function buscarKardex() {
            var input, filter, table, tr, tdArticulo, tdFecha, i, txtValueArt, txtValueFecha;
            input = document.getElementById("buscador");
            filter = input.value.toUpperCase();
            table = document.getElementById("tablaKardex");
            tr = table.getElementsByTagName("tr");

            var visible = 0;
            for (i = 1; i < tr.length; i++) {
                tdFecha = tr[i].getElementsByTagName("td")[0];
                tdArticulo = tr[i].getElementsByTagName("td")[1];
                
                if (tdArticulo || tdFecha) {
                    txtValueArt = tdArticulo.textContent || tdArticulo.innerText;
                    txtValueFecha = tdFecha.textContent || tdFecha.innerText;
                    
                    if (txtValueArt.toUpperCase().indexOf(filter) > -1 || txtValueFecha.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        visible++;
                    } else {
                        tr[i].style.display = "none";
                    }
                }       
            }

            var vc = document.getElementById('visibleCount');
            if (vc) vc.textContent = visible;
        }

        /* ─────────────────────────────────────────────────────────────
         * filterTipo() — Filtrado por tipo de movimiento (Entrada / Salida)
         * ───────────────────────────────────────────────────────────── */
        function filterTipo(tipo) {
            var table      = document.getElementById("tablaKardex");
            var rows       = table.querySelectorAll("tbody tr[data-tipo]");
            var btnTodos   = document.getElementById("btn-todos");
            var btnEntrada = document.getElementById("btn-entrada");
            var btnSalida  = document.getElementById("btn-salida");

            // Reset classes
            var neutralClass = "tipo-btn px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 transition-all duration-150";
            btnTodos.className = neutralClass;
            btnEntrada.className = neutralClass + " hover:text-emerald-600";
            btnSalida.className = neutralClass + " hover:text-rose-600";

            // Set active class
            if (tipo === 'todos') {
                btnTodos.className = "tipo-btn active-all px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-150";
            } else if (tipo === 'ENTRADA') {
                btnEntrada.className = "tipo-btn active-in px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-150";
            } else if (tipo === 'SALIDA') {
                btnSalida.className = "tipo-btn active-out px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-150";
            }

            // Show/hide rows
            var visible = 0;
            rows.forEach(function(row) {
                if (tipo === 'todos' || row.getAttribute("data-tipo") === tipo) {
                    row.style.display = "";
                    visible++;
                } else {
                    row.style.display = "none";
                }
            });

            var vc = document.getElementById('visibleCount');
            if (vc) vc.textContent = visible;

            // Clear search input
            var searchInput = document.getElementById("buscador");
            if (searchInput) searchInput.value = "";
        }
    </script>
</body>
</html>
