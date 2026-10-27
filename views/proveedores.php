<?php
// Archivo: views/proveedores.php
session_start();
if(!isset($_SESSION['usuario_id'])){ header("Location: ../index.php?error=nologin"); exit(); }
require_once '../config/conexion.php';

$database = new Conexion();
$db = $database->getConnection();
$query = "SELECT * FROM proveedores ORDER BY razon_social ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Métricas de resumen KPI
$totalProveedores   = count($proveedores);
$paisesDistintos    = count(array_unique(array_column($proveedores, 'pais')));
$promedioEntrega    = round(array_sum(array_column($proveedores,'tiempo_entrega_dias')) / max(1, $totalProveedores)) . ' días';

// Helper: País a bandera emoji
function getFlag(string $pais): string {
    $pais = mb_strtolower(trim($pais));
    $map = [
        'japón'          => '🇯🇵', 'japon'         => '🇯🇵',
        'china'          => '🇨🇳',
        'usa'            => '🇺🇸', 'estados unidos' => '🇺🇸', 'ee.uu.'  => '🇺🇸', 'eeuu' => '🇺🇸',
        'alemania'       => '🇩🇪', 'germany'        => '🇩🇪',
        'korea'          => '🇰🇷', 'corea'          => '🇰🇷', 'corea del sur' => '🇰🇷',
        'taiwan'         => '🇹🇼', 'taiwán'         => '🇹🇼',
        'india'          => '🇮🇳',
        'italia'         => '🇮🇹', 'italy'          => '🇮🇹',
        'francia'        => '🇫🇷', 'france'         => '🇫🇷',
        'brasil'         => '🇧🇷', 'brazil'         => '🇧🇷',
        'méxico'         => '🇲🇽', 'mexico'         => '🇲🇽',
        'españa'         => '🇪🇸', 'espana'         => '🇪🇸', 'spain' => '🇪🇸',
        'reino unido'    => '🇬🇧', 'uk'             => '🇬🇧',
        'canadá'         => '🇨🇦', 'canada'         => '🇨🇦',
        'australia'      => '🇦🇺',
        'argentina'      => '🇦🇷',
        'colombia'       => '🇨🇴',
        'chile'          => '🇨🇱',
        'perú'           => '🇵🇪', 'peru'           => '🇵🇪',
        'holanda'        => '🇳🇱', 'países bajos'   => '🇳🇱',
        'suecia'         => '🇸🇪', 'sweden'         => '🇸🇪',
        'noruega'        => '🇳🇴', 'norway'         => '🇳🇴',
        'suiza'          => '🇨🇭', 'switzerland'    => '🇨🇭',
        'rusia'          => '🇷🇺', 'russia'         => '🇷🇺',
        'turquía'        => '🇹🇷', 'turkey'         => '🇹🇷',
        'indonesia'      => '🇮🇩',
        'vietnam'        => '🇻🇳',
        'tailandia'      => '🇹🇭', 'thailand'       => '🇹🇭',
        'singapur'       => '🇸🇬', 'singapore'      => '🇸🇬',
        'malasia'        => '🇲🇾', 'malaysia'       => '🇲🇾',
        'portugal'       => '🇵🇹',
        'polonia'        => '🇵🇱', 'poland'         => '🇵🇱',
        'egipto'         => '🇪🇬', 'egypt'          => '🇪🇬',
        'sudáfrica'      => '🇿🇦', 'sudafrica'      => '🇿🇦',
    ];
    return $map[$pais] ?? '🌐';
}

// Helper: Badge de tiempo de entrega -> [clase_badge, etiqueta, clase_dot]
function deliveryBadge(int $dias): array {
    if ($dias <= 7)  return ['bg-emerald-50 text-emerald-700 border-emerald-200/90', 'Rápido',    'bg-emerald-500'];
    if ($dias <= 21) return ['bg-blue-50 text-blue-700 border-blue-200/90',          'Estándar',  'bg-blue-500'];
    return               ['bg-amber-50 text-amber-700 border-amber-200/90',          'Extendido', 'bg-amber-500'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directorio de Proveedores - ComerXync AutoParts</title>
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

        /* View toggles */
        .view-cards { display: grid; }
        .view-cards.hidden-view { display: none !important; }
        .view-table { display: block; }
        .view-table.hidden-view { display: none !important; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 flex h-screen overflow-hidden antialiased">

    <!-- TOAST NOTIFICATION FLOTANTE (Auto-dismiss en 3 segundos) -->
    <?php if(isset($_GET['exito']) && $_GET['exito'] === 'creado'): ?>
    <div id="toast-notification" class="fixed top-6 right-6 z-50 flex items-center gap-3.5 px-5 py-4 bg-white/95 backdrop-blur-xl border border-emerald-200/90 text-slate-800 rounded-2xl shadow-[0_12px_36px_-6px_rgba(16,185,129,0.3)] transition-all duration-500 transform translate-y-0 opacity-100">
        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <p class="text-[11px] font-black uppercase tracking-wider text-emerald-600">Proveedor Registrado</p>
            <p class="text-xs font-semibold text-slate-700">El nuevo socio estratégico ha sido incorporado a la red global.</p>
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
                <div class="w-9 h-9 rounded-xl bg-cyan-600/10 border border-cyan-600/20 flex items-center justify-center text-cyan-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">Proveedores Globales</h1>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Gestión de socios de origen, canales de suministro y lead-time logístico.</p>
                </div>
            </div>

            <!-- Right: Buscador Floating Label + Toggle View + Botón Nuevo -->
            <div class="flex items-center gap-3.5">
                <!-- FORMULARIO / BUSCADOR CON FLOATING LABEL Y SOMBRA DINÁMICA -->
                <div class="relative group">
                    <input type="text" id="buscador" onkeyup="buscarProveedor()" placeholder=" "
                           class="peer w-64 pl-10 pr-4 pt-4 pb-1.5 bg-white/90 border border-slate-200/90 rounded-xl text-xs font-semibold text-slate-800 placeholder-transparent focus:outline-none focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 focus:shadow-[0_0_20px_rgba(6,182,212,0.15)] transition-all duration-200">
                    <label for="buscador"
                           class="absolute left-10 top-1 text-[9px] font-bold text-slate-400 uppercase tracking-wider transition-all duration-200 pointer-events-none peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-xs peer-placeholder-shown:font-medium peer-placeholder-shown:text-slate-400 peer-placeholder-shown:normal-case peer-focus:top-1 peer-focus:text-[9px] peer-focus:font-bold peer-focus:text-cyan-600 peer-focus:uppercase peer-focus:tracking-wider">
                        Buscar empresa o país...
                    </label>
                    <svg class="w-4 h-4 text-slate-400 peer-focus:text-cyan-500 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- Botón Toggle Vista (Cards / Tabla) -->
                <button id="btnToggleView" onclick="toggleView()"
                        class="inline-flex items-center gap-2 px-3.5 py-2.5 text-xs font-bold bg-slate-100 hover:bg-slate-200/80 text-slate-700 rounded-xl border border-slate-200/80 transition-all cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span id="btnToggleLabel">Vista Tabla</span>
                </button>

                <!-- Botón Nuevo Proveedor -->
                <a href="nuevo_proveedor.php"
                   class="inline-flex items-center gap-2 bg-slate-900 hover:bg-cyan-600 active:scale-95 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo Proveedor
                </a>
            </div>
        </header>

        <!-- ═══════════════ ÁREA DE TRABAJO SCROLLABLE ═══════════════ -->
        <div class="flex-1 overflow-y-auto px-8 py-7 space-y-6 scrollbar-saas">

            <!-- ── SECCIÓN SUPERIOR: TÍTULO Y DESCRIPCIÓN ── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-base font-black text-slate-900 tracking-tight">Directorio Internacional de Suministro</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Control de contactos, tiempos de entrega estimados y origen geográfico.</p>
                </div>
            </div>

            <!-- ── SECCIÓN KPI CARDS (GLASSMORPHISM) ── -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <!-- Total Proveedores -->
                <div class="glass-card rounded-2xl p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Total Proveedores</p>
                        <p class="text-2xl font-black text-slate-900 leading-tight mt-0.5"><?php echo $totalProveedores; ?></p>
                        <p class="text-[10px] text-slate-400 font-medium">Empresas homologadas</p>
                    </div>
                </div>

                <!-- Países Distintos -->
                <div class="glass-card rounded-2xl p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Países de Origen</p>
                        <p class="text-2xl font-black text-blue-600 leading-tight mt-0.5"><?php echo $paisesDistintos; ?></p>
                        <p class="text-[10px] text-blue-600 font-medium">Cobertura global</p>
                    </div>
                </div>

                <!-- Tiempo de Entrega Promedio -->
                <div class="glass-card rounded-2xl p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Entrega Promedio</p>
                        <p class="text-2xl font-black text-amber-600 leading-tight mt-0.5"><?php echo $promedioEntrega; ?></p>
                        <p class="text-[10px] text-amber-600 font-medium">Lead-time promedio</p>
                    </div>
                </div>

            </div>

            <!-- ══ VISTA EN TARJETAS (CARDS VIEW - DEFAULT) ══════════════════════════ -->
            <div id="viewCards" class="view-cards grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                <?php foreach($proveedores as $prov):
                    $dias = (int)$prov['tiempo_entrega_dias'];
                    [$badgeCls, $badgeLabel, $dotCls] = deliveryBadge($dias);
                    $flag = getFlag($prov['pais']);
                    $fechaReg = !empty($prov['fecha_registro']) ? date('d/m/Y', strtotime($prov['fecha_registro'])) : '—';
                ?>
                <div class="glass-card rounded-2xl p-5 flex flex-col justify-between group">
                    <div>
                        <!-- Header de tarjeta -->
                        <div class="flex items-start gap-3.5 mb-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-2xl shrink-0 shadow-inner">
                                <?php echo $flag; ?>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-sm font-bold text-slate-800 leading-snug truncate group-hover:text-cyan-600 transition-colors" title="<?php echo htmlspecialchars($prov['razon_social']); ?>">
                                    <?php echo htmlspecialchars($prov['razon_social']); ?>
                                </h3>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-500 bg-slate-100/90 rounded-md px-2 py-0.5">
                                        <?php echo htmlspecialchars($prov['pais']); ?>
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-mono font-medium">ID #<?php echo htmlspecialchars($prov['id_proveedor']); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="border-t border-slate-100/90 my-3"></div>

                        <!-- Datos de contacto -->
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center gap-2 text-slate-600">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="truncate font-semibold text-slate-700"><?php echo htmlspecialchars($prov['contacto']); ?></span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-600">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span class="text-slate-500 font-mono text-[11px]"><?php echo htmlspecialchars($prov['telefono']); ?></span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-400 text-[11px]">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <span>Registrado: <?php echo $fechaReg; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer de tarjeta -->
                    <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full border <?php echo $badgeCls; ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?php echo $dotCls; ?>"></span>
                            <?php echo $dias; ?> días &bull; <?php echo $badgeLabel; ?>
                        </span>

                        <!-- Acción encapsulada eliminar -->
                        <a href="../controllers/eliminarProveedorController.php?id=<?php echo $prov['id_proveedor']; ?>"
                           onclick="return confirm('¿Eliminar este proveedor?')"
                           class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                           title="Eliminar socio">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if(empty($proveedores)): ?>
                <div class="col-span-full py-16 text-center text-slate-400">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-700">No hay proveedores registrados aún.</p>
                    <a href="nuevo_proveedor.php" class="text-xs text-cyan-600 font-bold hover:underline mt-1 inline-block">+ Registrar primer proveedor</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- ══ VISTA EN TABLA (TABLE VIEW - OCULTA POR DEFECTO) ══════════════════════════ -->
            <div id="viewTable" class="view-table hidden-view">
                <div class="glass-card rounded-2xl overflow-hidden border border-slate-200/80">
                    <div class="overflow-x-auto">
                        <table id="tablaProveedores" class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-200/80 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
                                    <th class="px-7 py-3.5">Empresa (Razón Social)</th>
                                    <th class="px-7 py-3.5">País de Origen</th>
                                    <th class="px-7 py-3.5">Contacto y Teléfono</th>
                                    <th class="px-7 py-3.5">Tiempo de Entrega</th>
                                    <th class="px-7 py-3.5 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                <?php foreach($proveedores as $prov):
                                    $dias = (int)$prov['tiempo_entrega_dias'];
                                    [$badgeCls, $badgeLabel, $dotCls] = deliveryBadge($dias);
                                    $flag = getFlag($prov['pais']);
                                ?>
                                <tr class="hover:bg-slate-50/80 transition-colors duration-150 group">
                                    <!-- TD[0] Empresa (JS buscarProveedor busca aquí) -->
                                    <td class="px-7 py-4 whitespace-nowrap">
                                        <p class="text-sm font-bold text-slate-800 group-hover:text-cyan-600 transition-colors"><?php echo htmlspecialchars($prov['razon_social']); ?></p>
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">ID #<?php echo htmlspecialchars($prov['id_proveedor']); ?></p>
                                    </td>
                                    <!-- TD[1] País (JS buscarProveedor busca aquí) -->
                                    <td class="px-7 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-slate-100 text-slate-700 rounded-lg px-2.5 py-1">
                                            <?php echo $flag; ?> <?php echo htmlspecialchars($prov['pais']); ?>
                                        </span>
                                    </td>
                                    <!-- TD[2] Contacto + Teléfono -->
                                    <td class="px-7 py-4">
                                        <p class="font-bold text-slate-800"><?php echo htmlspecialchars($prov['contacto']); ?></p>
                                        <p class="text-[11px] text-slate-400 font-mono mt-0.5"><?php echo htmlspecialchars($prov['telefono']); ?></p>
                                    </td>
                                    <!-- TD[3] Tiempo de Entrega -->
                                    <td class="px-7 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full border <?php echo $badgeCls; ?>">
                                            <span class="w-1.5 h-1.5 rounded-full <?php echo $dotCls; ?>"></span>
                                            <?php echo $dias; ?> días &bull; <?php echo $badgeLabel; ?>
                                        </span>
                                    </td>
                                    <!-- TD[4] Acciones encapsuladas -->
                                    <td class="px-7 py-4 whitespace-nowrap text-center">
                                        <div class="inline-flex items-center p-1 bg-slate-100/80 group-hover:bg-slate-200/70 rounded-xl border border-slate-200/80 transition-all">
                                            <a href="../controllers/eliminarProveedorController.php?id=<?php echo $prov['id_proveedor']; ?>"
                                               onclick="return confirm('¿Eliminar este proveedor?')"
                                               class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-white hover:shadow-xs transition-all"
                                               title="Eliminar socio">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                                <?php if(empty($proveedores)): ?>
                                <tr>
                                    <td colspan="5" class="px-7 py-16 text-center text-slate-400">
                                        <p class="text-sm font-bold text-slate-700">No hay proveedores en la lista.</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Table footer -->
                    <div class="px-7 py-3.5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <p class="text-xs text-slate-400 font-medium">
                            Mostrando <span class="font-bold text-slate-700"><?php echo $totalProveedores; ?></span> proveedores
                        </p>
                        <p class="text-xs text-slate-400 font-medium">
                            <?php echo $paisesDistintos; ?> países distintos &bull; Lead-time promedio <?php echo $promedioEntrega; ?>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- ═══════════════ JAVASCRIPT DE BÚSQUEDA Y VISTAS ═══════════════ -->
    <script>
        /* ── Alternancia de Vistas (Cards / Tabla) ─────────────── */
        var currentView = 'cards';

        function toggleView() {
            var cards = document.getElementById('viewCards');
            var table = document.getElementById('viewTable');
            var label = document.getElementById('btnToggleLabel');

            if (currentView === 'cards') {
                cards.classList.add('hidden-view');
                table.classList.remove('hidden-view');
                label.textContent = 'Vista Cards';
                currentView = 'table';
            } else {
                table.classList.add('hidden-view');
                cards.classList.remove('hidden-view');
                label.textContent = 'Vista Tabla';
                currentView = 'cards';
            }
        }

        /* ── Búsqueda de proveedores (Mantenida exactamente) ───── */
        function buscarProveedor() {
            var input, filter, table, tr, tdEmpresa, tdPais, i, txtValueEmpresa, txtValuePais;
            input = document.getElementById("buscador");
            filter = input.value.toUpperCase();
            table = document.getElementById("tablaProveedores");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                tdEmpresa = tr[i].getElementsByTagName("td")[0];
                tdPais = tr[i].getElementsByTagName("td")[1];
                
                if (tdEmpresa || tdPais) {
                    txtValueEmpresa = tdEmpresa.textContent || tdEmpresa.innerText;
                    txtValuePais = tdPais.textContent || tdPais.innerText;
                    
                    if (txtValueEmpresa.toUpperCase().indexOf(filter) > -1 || txtValuePais.toUpperCase().indexOf(filter) > -1) {
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
