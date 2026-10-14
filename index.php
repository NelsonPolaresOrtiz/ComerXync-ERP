<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ComerXync AutoParts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-50 h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-gray-100">
        
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Comer<span class="text-amber-500">Xync</span></h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">ERP Automotriz y Maquinaria</p>
        </div>

        <!-- MÓDULO DE ALERTAS Y ERRORES -->
        <?php if(isset($_GET['error'])): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded shadow-sm text-sm font-medium">
                <?php 
                    if($_GET['error'] == 'credenciales') echo "❌ Correo o contraseña incorrectos.";
                    elseif($_GET['error'] == 'acceso_denegado') echo "🔒 Acceso denegado. No tienes permisos para ver esa pantalla.";
                    elseif($_GET['error'] == 'nologin') echo "⚠️ Debes iniciar sesión para continuar.";
                    else echo "Ocurrió un error. Intenta nuevamente.";
                ?>
            </div>
        <?php endif; ?>

        <form action="controllers/loginController.php" method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Correo Electrónico</label>
                <input type="email" id="email" name="email" required 
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-800 focus:border-slate-800 outline-none transition-all"
                    placeholder="admin@comerxync.com">
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Contraseña</label>
                <input type="password" id="password" name="password" required 
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-800 focus:border-slate-800 outline-none transition-all"
                    placeholder="••••••••">
            </div>

            <button type="submit" 
                class="w-full bg-slate-800 text-white font-bold py-3 rounded-lg hover:bg-slate-700 transition-colors duration-200 shadow-md">
                Iniciar Sesión
            </button>
        </form>

        <div class="mt-8 text-center text-xs text-gray-400 font-medium">
            &copy; 2026 ComerXync Systems. Equipo USFX.
        </div>
    </div>
</body>
</html>