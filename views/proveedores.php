<?php
session_start();
if(!isset($_SESSION['usuario_id'])){ header("Location: ../index.php?error=nologin"); exit(); }
require_once '../config/conexion.php';

$database = new Conexion();
$db = $database->getConnection();
$query = "SELECT * FROM proveedores ORDER BY razon_social ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proveedores - ComerXync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    
    <!-- SIDEBAR MODULARIZADO -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col overflow-hidden">
        
        <!-- Topbar -->
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 shrink-0 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="bg-slate-100 text-slate-600 p-2 rounded-lg">🏢</div>
                <h1 class="text-xl font-bold text-slate-800">Directorio de Proveedores</h1>
            </div>
            
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <input type="text" id="buscador" onkeyup="buscarProveedor()" placeholder="Buscar empresa o país..." 
                           class="w-64 pl-10 pr-4 py-2 bg-gray-100 border-transparent rounded-lg text-sm focus:border-blue-500 focus:bg-white focus:ring-0 outline-none transition-all">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </header>

        <!-- Área de Trabajo -->
        <div class="p-8 flex-1 overflow-auto">
            
            <?php if(isset($_GET['exito']) && $_GET['exito'] == 'creado'): ?>
                <div class="mb-6 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">✅ Proveedor registrado exitosamente.</div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Proveedores Internacionales</h2>
                    <p class="text-sm text-gray-500 mt-1">Directorio de socios estratégicos y tiempos de entrega.</p>
                </div>
                <a href="nuevo_proveedor.php" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-md flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nuevo Proveedor
                </a>
            </div>

            <!-- Tabla -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full text-left border-collapse" id="tablaProveedores">
                    <thead>
                        <tr class="bg-slate-50 border-b border-gray-200 text-xs uppercase text-gray-500 font-bold tracking-wider">
                            <th class="px-6 py-4">Empresa (Razón Social)</th>
                            <th class="px-6 py-4">País de Origen</th>
                            <th class="px-6 py-4">Contacto</th>
                            <th class="px-6 py-4">T. Estimado de Entrega</th>
                            <th class="px-6 py-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(count($proveedores) > 0): ?>
                            <?php foreach($proveedores as $prov): ?>
                                <tr class="hover:bg-slate-50 transition-colors group">
                                    <td class="px-6 py-4 text-sm font-bold text-slate-800"><?php echo htmlspecialchars($prov['razon_social']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs rounded-lg font-bold border border-slate-200">
                                            <?php echo htmlspecialchars($prov['pais']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-slate-700"><?php echo htmlspecialchars($prov['contacto']); ?></p>
                                        <p class="text-xs text-gray-400">Tel: <?php echo htmlspecialchars($prov['telefono']); ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-bold text-amber-600 bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-100">
                                            ⏳ <?php echo htmlspecialchars($prov['tiempo_entrega_dias']); ?> días
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="#" class="text-blue-500 hover:text-blue-700 bg-blue-50 p-2 rounded-lg" title="Editar">✏️</a>
                                            <a href="../controllers/eliminarProveedorController.php?id=<?php echo $prov['id_proveedor']; ?>" 
                                               class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg" title="Borrar"
                                               onclick="return confirm('¿Peligro! ¿Eliminar proveedor?');">🗑️</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    <div class="text-4xl mb-3">🏢</div>
                                    <p class="text-sm font-medium">No hay proveedores en la base de datos.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Script Buscador -->
    <script>
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