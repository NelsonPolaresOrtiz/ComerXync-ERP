<?php
// Archivo: index.php
session_start();
// Si ya había una sesión activa, lo mandamos directo al dashboard
if(isset($_SESSION['usuario_id'])){
    header("Location: views/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Corporativo - ComerXync AutoParts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Inter', sans-serif; } 
        .glass-card {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="bg-slate-950 h-screen w-screen flex items-center justify-center relative overflow-hidden">

    <!-- Fondo Dinámico con Iluminación Ambiental (Efecto 3D / Profundidad) -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/30 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-amber-500/20 rounded-full blur-[120px] pointer-events-none"></div>

    <!-- Contenedor Principal del Login -->
    <div class="relative z-10 w-full max-w-md p-8 rounded-3xl glass-card shadow-2xl mx-4">
        
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-600/20 text-blue-500 text-3xl font-black mb-4 border border-blue-500/30 shadow-[0_0_20px_rgba(37,99,235,0.3)]">
                ⚙️
            </div>
            <h1 class="text-3xl font-black text-white tracking-tight">Comer<span class="text-amber-500">Xync</span></h1>
            <p class="text-slate-400 text-xs uppercase tracking-widest mt-1 font-semibold">Plataforma ERP Automotriz</p>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 text-xs px-4 py-3 rounded-xl text-center font-medium">
                ⚠️ Credenciales incorrectas o sesión expirada.
            </div>
        <?php endif; ?>

        <form action="controllers/loginController.php" method="POST" class="space-y-5">
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Correo Electrónico</label>
                <input type="email" name="email" required placeholder="ejemplo@comerxync.com" 
                    class="w-full px-4 py-3 bg-slate-900/80 border border-slate-800 rounded-xl text-white placeholder-slate-600 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Contraseña de Acceso</label>
                <input type="password" name="password" required placeholder="••••••••" 
                    class="w-full px-4 py-3 bg-slate-900/80 border border-slate-800 rounded-xl text-white placeholder-slate-600 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
            </div>

            <button type="submit" 
                class="w-full py-4 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-[0_0_25px_rgba(37,99,235,0.4)] transition-all duration-300 transform hover:scale-[1.02] text-sm tracking-wide">
                Iniciar Sesión en el Sistema
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-800/80 text-center">
            <p class="text-xs text-slate-500">Universidad Mayor de San Francisco Xavier de Chuquisaca</p>
            <p class="text-[10px] text-slate-600 mt-1">Ing. de Sistemas</p>
        </div>
    </div>

</body>
</html>