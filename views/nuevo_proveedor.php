<?php
session_start();
if(!isset($_SESSION['usuario_id'])){ header("Location: ../index.php?error=nologin"); exit(); }
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
<body class="bg-gray-50 flex h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-800 text-white flex flex-col">
        <div class="h-16 flex items-center justify-center border-b border-slate-700">
            <h2 class="text-xl font-bold">Comer<span class="text-amber-500">Xync</span></h2>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="dashboard.php" class="block px-4 py-2 hover:bg-slate-700 rounded text-sm transition-colors">Dashboard</a>
            <a href="articulos.php" class="block px-4 py-2 hover:bg-slate-700 rounded text-sm transition-colors">Catálogo (Almacén)</a>
            <a href="proveedores.php" class="block px-4 py-2 bg-slate-700 rounded text-sm">Proveedores</a>
        </nav>
        <div class="p-4 border-t border-slate-700">
            <a href="../controllers/logoutController.php" class="block text-center px-4 py-2 text-sm text-red-400 hover:text-red-300">Cerrar Sesión</a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 shrink-0">
            <h1 class="text-lg font-semibold text-gray-700">Registrar Proveedor</h1>
        </header>

        <div class="p-8 flex-1 overflow-auto flex justify-center">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 w-full max-w-2xl p-8 h-fit">
                <h2 class="text-xl font-bold text-slate-800 mb-6">Datos de Origen</h2>
                
                <form action="../controllers/crearProveedorController.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Razón Social / Empresa</label>
                            <input type="text" name="razon_social" required placeholder="Ej: Toyota Parts Inc."
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-800 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">País</label>
                            <input type="text" name="pais" required placeholder="Japón"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-800 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Persona de Contacto</label>
                            <input type="text" name="contacto" placeholder="Nombre del representante"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-800 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Teléfono</label>
                            <input type="text" name="telefono" placeholder="+81 ..."
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-800 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tiempo Estimado de Entrega (Días)</label>
                        <input type="number" name="tiempo_entrega_dias" value="0" min="0"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-800 outline-none">
                    </div>

                    <div class="flex justify-end space-x-4 pt-4 mt-6 border-t border-gray-100">
                        <a href="proveedores.php" class="px-6 py-2.5 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors">Cancelar</a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium shadow-sm transition-colors">Guardar Proveedor</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>