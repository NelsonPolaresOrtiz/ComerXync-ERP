<?php
// Archivo: views/nuevo_proveedor.php
session_start();
if(!isset($_SESSION['usuario_id'])){ 
    header("Location: ../index.php?error=nologin"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Proveedor - ComerXync</title>
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
                <div class="bg-emerald-100 text-emerald-600 p-2 rounded-lg">🏢</div>
                <h1 class="text-xl font-bold text-slate-800">Registrar Proveedor Internacional</h1>
            </div>
            <a href="proveedores.php" class="text-sm font-medium text-gray-500 hover:text-slate-800 transition-colors">← Volver al Directorio</a>
        </header>

        <!-- Área del Formulario -->
        <div class="p-8 flex-1 overflow-auto flex justify-center">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 w-full max-w-2xl p-8 h-fit">
                
                <h2 class="text-xl font-bold text-slate-800 mb-6 border-b border-gray-100 pb-4">Datos de Origen y Logística</h2>
                
                <form action="../controllers/crearProveedorController.php" method="POST" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Razón Social / Empresa</label>
                            <input type="text" name="razon_social" required placeholder="Ej: Toyota Parts Inc."
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">País de Origen</label>
                            <input type="text" name="pais" required placeholder="Ej: Japón, EE.UU., Brasil"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Persona de Contacto / Agente</label>
                            <input type="text" name="contacto" placeholder="Nombre del representante comercial"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Teléfono Internacional</label>
                            <input type="text" name="telefono" placeholder="Ej: +81 3-1234-5678"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Tiempo Estimado de Entrega / Importación (Días)</label>
                        <input type="number" name="tiempo_entrega_dias" value="15" min="1" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-bold text-slate-800">
                    </div>

                    <div class="flex justify-end space-x-4 pt-4 mt-6 border-t border-gray-100">
                        <a href="proveedores.php" class="px-6 py-2.5 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl font-medium transition-colors text-sm">
                            Cancelar
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-md transition-colors text-sm">
                            + Guardar Proveedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>