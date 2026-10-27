<?php
// Archivo: views/articulos.php
session_start();
if(!isset($_SESSION['usuario_id'])){ header("Location: ../index.php?error=nologin"); exit(); }
require_once '../config/conexion.php';

$database = new Conexion();
$db = $database->getConnection();

// Traemos los artículos con su proveedor y también consultamos su Stock Actual en el Kardex
$query = "SELECT a.id_articulo, a.sku_vin, a.nombre, a.categoria, a.precio_venta, p.razon_social,
          COALESCE((SELECT saldo_actual FROM kardex k WHERE k.id_articulo = a.id_articulo ORDER BY id_movimiento DESC LIMIT 1), 0) as stock
          FROM articulos a 
          LEFT JOIN proveedores p ON a.id_proveedor = p.id_proveedor 
          ORDER BY a.nombre ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Métricas de resumen (calculadas tras la consulta)
$totalSkus    = count($articulos);
$enStock      = 0;
$sinStock     = 0;
$valorCatalogo = 0.0;
foreach ($articulos as $a) {
    if ($a['stock'] > 0) {
        $enStock++;
        $valorCatalogo += $a['precio_venta'] * $a['stock'];
    } else {
        $sinStock++;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo Maestro - ComerXync AutoParts</title>
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

        /* Animation utilities */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }

        /* Filter pill active state */
        .filter-pill.active {
            background-color: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
            box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.15);
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 flex h-screen overflow-hidden antialiased">

    <!-- TOAST NOTIFICATION FLOTANTE (Auto-dismiss en 3 segundos) -->
    <?php if(isset($_GET['exito']) && ($_GET['exito'] == 'creado' || $_GET['exito'] == 'eliminado')): ?>
    <div id="toast-notification" class="fixed top-6 right-6 z-50 flex items-center gap-3.5 px-5 py-4 bg-white/95 backdrop-blur-xl border <?php echo $_GET['exito'] == 'creado' ? 'border-emerald-200/90 text-slate-800 shadow-[0_12px_36px_-6px_rgba(16,185,129,0.3)]' : 'border-rose-200/90 text-slate-800 shadow-[0_12px_36px_-6px_rgba(244,63,94,0.3)]'; ?> rounded-2xl transition-all duration-500 transform translate-y-0 opacity-100">
        <div class="w-9 h-9 rounded-xl <?php echo $_GET['exito'] == 'creado' ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-600' : 'bg-rose-500/10 border border-rose-500/20 text-rose-600'; ?> flex items-center justify-center shrink-0">
            <?php if($_GET['exito'] == 'creado'): ?>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <?php else: ?>
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            <?php endif; ?>
        </div>
        <div>
            <p class="text-[11px] font-black uppercase tracking-wider <?php echo $_GET['exito'] == 'creado' ? 'text-emerald-600' : 'text-rose-600'; ?>">
                <?php echo $_GET['exito'] == 'creado' ? 'Catálogo Actualizado' : 'Registro Eliminado'; ?>
            </p>
            <p class="text-xs font-semibold text-slate-700">
                <?php echo $_GET['exito'] == 'creado' ? 'El nuevo artículo ha sido registrado exitosamente.' : 'El artículo ha sido eliminado del catálogo y base de datos.'; ?>
            </p>
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">Catálogo de Artículos</h1>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Gestión de repuestos, accesorios, maquinaria y vehículos en inventario.</p>
                </div>
            </div>

            <!-- Right: Buscador con Floating Label + Botón Nuevo -->
            <div class="flex items-center gap-3.5">
                <!-- FORMULARIO / BUSCADOR CON FLOATING LABEL Y SOMBRA DINÁMICA -->
                <div class="relative group">
                    <input type="text" id="buscador" onkeyup="buscarTabla()" placeholder=" "
                           class="peer w-72 pl-10 pr-4 pt-4 pb-1.5 bg-white/90 border border-slate-200/90 rounded-xl text-xs font-semibold text-slate-800 placeholder-transparent focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:shadow-[0_0_20px_rgba(59,130,246,0.15)] transition-all duration-200">
                    <label for="buscador"
                           class="absolute left-10 top-1 text-[9px] font-bold text-slate-400 uppercase tracking-wider transition-all duration-200 pointer-events-none peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-xs peer-placeholder-shown:font-medium peer-placeholder-shown:text-slate-400 peer-placeholder-shown:normal-case peer-focus:top-1 peer-focus:text-[9px] peer-focus:font-bold peer-focus:text-blue-600 peer-focus:uppercase peer-focus:tracking-wider">
                        Buscar artículo o SKU...
                    </label>
                    <svg class="w-4 h-4 text-slate-400 peer-focus:text-blue-500 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- Botón Nuevo Artículo -->
                <a href="nuevo_articulo.php"
                   class="inline-flex items-center gap-2 bg-slate-900 hover:bg-blue-600 active:scale-95 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo Artículo
                </a>
            </div>
        </header>

        <!-- ═══════════════ ÁREA DE TRABAJO SCROLLABLE ═══════════════ -->
        <div class="flex-1 overflow-y-auto px-8 py-7 space-y-6 scrollbar-saas">

            <!-- ── SECCIÓN SUPERIOR: TÍTULO Y FILTROS POR CATEGORÍA ── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-base font-black text-slate-900 tracking-tight">Inventario Base y Disponibilidad</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Control de saldos sincronizado en tiempo real con el libro mayor Kardex.</p>
                </div>

                <!-- Category Filter Pills -->
                <div class="flex items-center gap-1.5 p-1 bg-slate-100/80 rounded-2xl border border-slate-200/80 shadow-2xs" id="categoryFilters">
                    <button onclick="filterCategory('all')"
                            class="filter-pill active px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 transition-all duration-150">
                        Todos
                    </button>
                    <button onclick="filterCategory('Repuesto')"
                            class="filter-pill px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-600 hover:text-blue-600 transition-all duration-150">
                        Repuesto
                    </button>
                    <button onclick="filterCategory('Accesorio')"
                            class="filter-pill px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-600 hover:text-violet-600 transition-all duration-150">
                        Accesorio
                    </button>
                    <button onclick="filterCategory('Maquinaria')"
                            class="filter-pill px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-600 hover:text-amber-600 transition-all duration-150">
                        Maquinaria
                    </button>
                    <button onclick="filterCategory('Vehiculo')"
                            class="filter-pill px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-600 hover:text-emerald-600 transition-all duration-150">
                        Vehículo
                    </button>
                </div>
            </div>

            <!-- ── SECCIÓN KPI CARDS (GLASSMORPHISM) ── -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Total SKUs -->
                <div class="glass-card rounded-2xl p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Total SKUs</p>
                        <p class="text-2xl font-black text-slate-900 leading-tight mt-0.5"><?php echo $totalSkus; ?></p>
                        <p class="text-[10px] text-slate-400 font-medium">Registros activos</p>
                    </div>
                </div>

                <!-- En Stock -->
                <div class="glass-card rounded-2xl p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Disponibles</p>
                        <p class="text-2xl font-black text-emerald-600 leading-tight mt-0.5"><?php echo $enStock; ?></p>
                        <p class="text-[10px] text-emerald-600 font-medium">Con inventario físico</p>
                    </div>
                </div>

                <!-- Sin Stock -->
                <div class="glass-card rounded-2xl p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Agotados</p>
                        <p class="text-2xl font-black text-rose-600 leading-tight mt-0.5"><?php echo $sinStock; ?></p>
                        <p class="text-[10px] text-rose-500 font-medium">Requieren reposición</p>
                    </div>
                </div>

                <!-- Valor del Catálogo -->
                <div class="glass-card rounded-2xl p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Valoración Total</p>
                        <p class="text-xl font-black text-slate-900 leading-tight mt-0.5 truncate">
                            S/ <?php echo number_format($valorCatalogo, 2); ?>
                        </p>
                        <p class="text-[10px] text-violet-600 font-medium">Precio público estimado</p>
                    </div>
                </div>

            </div>

            <!-- ── TABLA DE DATOS PRINCIPAL ── -->
            <div class="glass-card rounded-2xl overflow-hidden border border-slate-200/80">

                <!-- Table Header Bar -->
                <div class="px-7 py-4 border-b border-slate-100 flex items-center justify-between bg-white/50">
                    <p class="text-xs font-semibold text-slate-600">
                        <span id="rowCount" class="text-slate-900 font-black"><?php echo count($articulos); ?></span>
                        artículos catalogados
                    </p>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-xs text-slate-400 font-medium">Stock sincronizado con Kardex</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="tablaArticulos">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200/80 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
                                <th class="px-7 py-3.5">Artículo / Información</th>
                                <th class="px-7 py-3.5">Categoría</th>
                                <th class="px-7 py-3.5">Stock Físico</th>
                                <th class="px-7 py-3.5">Precio (S/)</th>
                                <th class="px-7 py-3.5 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">

                            <?php if(count($articulos) > 0): ?>
                                <?php foreach($articulos as $item): ?>

                                <?php
                                $cat = $item['categoria'];
                                $catColors = [
                                    'Repuesto'   => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'Accesorio'  => 'bg-violet-50 text-violet-700 border-violet-200',
                                    'Maquinaria' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'Vehiculo'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                ];
                                $catClass = $catColors[$cat] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                ?>

                                <tr class="hover:bg-slate-50/80 transition-colors duration-150 group" data-category="<?php echo htmlspecialchars($cat); ?>">

                                    <!-- ① Artículo: Mantiene exactamente dos <p> tags — JS buscarTabla depende de [0] y [1] -->
                                    <td class="px-7 py-4">
                                        <p class="text-sm font-bold text-slate-800 leading-snug group-hover:text-blue-600 transition-colors"><?php echo htmlspecialchars($item['nombre']); ?></p>
                                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                                            <span class="font-mono font-bold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">SKU: <?php echo htmlspecialchars($item['sku_vin']); ?></span>
                                            &nbsp;&bull;&nbsp; Proveedor: <span class="text-slate-600 font-semibold"><?php echo htmlspecialchars($item['razon_social'] ?? 'N/A'); ?></span>
                                        </p>
                                    </td>

                                    <!-- ② Categoría -->
                                    <td class="px-7 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border <?php echo $catClass; ?>">
                                            <?php echo htmlspecialchars($item['categoria']); ?>
                                        </span>
                                    </td>

                                    <!-- ③ Stock Físico -->
                                    <td class="px-7 py-4">
                                        <?php if($item['stock'] > 0): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200/80">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <?php echo $item['stock']; ?> uds
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-rose-50 text-rose-600 text-xs font-bold rounded-lg border border-rose-200/80">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                                Agotado (0)
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- ④ Precio -->
                                    <td class="px-7 py-4">
                                        <span class="text-sm font-black text-slate-900">S/ <?php echo number_format($item['precio_venta'], 2); ?></span>
                                    </td>

                                    <!-- ⑤ Acciones encapsuladas -->
                                    <td class="px-7 py-4 text-center">
                                        <div class="inline-flex items-center gap-1 p-1 bg-slate-100/80 group-hover:bg-slate-200/70 rounded-xl border border-slate-200/80 transition-all">
                                            <!-- Editar -->
                                            <a href="editar_articulo.php?id=<?php echo $item['id_articulo']; ?>"
                                               class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-white hover:shadow-xs transition-all"
                                               title="Editar repuesto">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </a>
                                            <span class="w-[1px] h-3.5 bg-slate-200"></span>
                                            <!-- Eliminar -->
                                            <a href="../controllers/eliminarArticuloController.php?id=<?php echo $item['id_articulo']; ?>"
                                               class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-white hover:shadow-xs transition-all"
                                               title="Eliminar del catálogo"
                                               onclick="return confirm('¿Peligro! ¿Estás seguro de eliminar el artículo <?php echo htmlspecialchars($item['sku_vin']); ?>?');">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>

                                </tr>
                                <?php endforeach; ?>

                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-7 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm font-bold text-slate-700">No hay artículos registrados en el catálogo.</p>
                                            <a href="nuevo_articulo.php"
                                               class="text-xs text-blue-600 font-bold hover:underline">+ Crear primer artículo</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>

                <!-- Table Footer -->
                <div class="px-7 py-3.5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <p class="text-xs text-slate-400 font-medium">
                        Mostrando <span id="visibleCount" class="font-bold text-slate-700"><?php echo count($articulos); ?></span>
                        de <?php echo count($articulos); ?> artículos
                    </p>
                    <p class="text-xs text-slate-400 font-medium hidden sm:block">
                        Acciones protegidas por confirmación de seguridad
                    </p>
                </div>

            </div>

        </div>
    </main>

    <!-- ═══════════════ JAVASCRIPT DE BÚSQUEDA Y FILTROS ═══════════════ -->
    <script>
        /* ─────────────────────────────────────────────────────────────
         * buscarTabla() — Mantenida exactamente como la original
         * ───────────────────────────────────────────────────────────── */
        function buscarTabla() {
            var input, filter, table, tr, tdNombre, tdSku, i, txtValueNombre, txtValueSku;
            input = document.getElementById("buscador");
            filter = input.value.toUpperCase();
            table = document.getElementById("tablaArticulos");
            tr = table.getElementsByTagName("tr");

            var visible = 0;
            for (i = 1; i < tr.length; i++) {
                tdNombre = tr[i].getElementsByTagName("td")[0].getElementsByTagName("p")[0];
                tdSku = tr[i].getElementsByTagName("td")[0].getElementsByTagName("p")[1];
                
                if (tdNombre || tdSku) {
                    txtValueNombre = tdNombre.textContent || tdNombre.innerText;
                    txtValueSku = tdSku.textContent || tdSku.innerText;
                    
                    if (txtValueNombre.toUpperCase().indexOf(filter) > -1 || txtValueSku.toUpperCase().indexOf(filter) > -1) {
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
         * filterCategory() — Función complementaria sin alterar buscarTabla
         * ───────────────────────────────────────────────────────────── */
        function filterCategory(cat) {
            var pills = document.querySelectorAll('#categoryFilters .filter-pill');
            pills.forEach(function(pill) {
                pill.classList.remove('active');
            });
            event.currentTarget.classList.add('active');

            var table = document.getElementById("tablaArticulos");
            var rows  = table.querySelectorAll("tbody tr[data-category]");
            var visible = 0;

            rows.forEach(function(row) {
                if (cat === 'all' || row.getAttribute('data-category') === cat) {
                    row.style.display = "";
                    visible++;
                } else {
                    row.style.display = "none";
                }
            });

            var vc = document.getElementById('visibleCount');
            if (vc) vc.textContent = visible;

            var searchInput = document.getElementById("buscador");
            if (searchInput) searchInput.value = "";
        }
    </script>
</body>
</html>