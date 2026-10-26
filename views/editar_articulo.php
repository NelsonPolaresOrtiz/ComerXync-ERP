<?php
// Archivo: views/editar_articulo.php
session_start();
if(!isset($_SESSION['usuario_id'])){ header("Location: ../index.php?error=nologin"); exit(); }
require_once '../config/conexion.php';

$database = new Conexion();
$db = $database->getConnection();

// PROCESAR ACTUALIZACIÓN (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id_articulo'];
    $sku = trim($_POST['sku_vin']);
    $nombre = trim($_POST['nombre']);
    $categoria = $_POST['categoria'];
    $precio_compra = $_POST['precio_compra'];
    $precio_venta = $_POST['precio_venta'];
    $stock_minimo = $_POST['stock_minimo'];
    $imagen_url = trim($_POST['imagen_url']);

    $sql = "UPDATE articulos SET sku_vin=:sku, nombre=:nom, categoria=:cat, precio_compra=:pc, precio_venta=:pv, stock_minimo=:sm, imagen_url=:img WHERE id_articulo=:id";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':sku' => $sku, ':nom' => $nombre, ':cat' => $categoria,
        ':pc' => $precio_compra, ':pv' => $precio_venta, ':sm' => $stock_minimo,
        ':img' => $imagen_url, ':id' => $id
    ]);
    header("Location: articulos.php?exito=creado");
    exit();
}

// CARGAR DATOS ACTUALES (GET)
if (!isset($_GET['id'])) { header("Location: articulos.php"); exit(); }
$stmt = $db->prepare("SELECT * FROM articulos WHERE id_articulo = :id");
$stmt->execute([':id' => $_GET['id']]);
$art = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$art) { header("Location: articulos.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Artículo - ComerXync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    <?php include 'includes/sidebar.php'; ?>
    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 border-b border-gray-200">
            <h1 class="text-xl font-bold text-slate-800">✏️ Modificar Artículo #<?php echo $art['id_articulo']; ?></h1>
            <a href="articulos.php" class="text-sm font-medium text-gray-500 hover:text-slate-800">← Volver al Catálogo</a>
        </header>
        <div class="p-8 flex-1 overflow-auto flex justify-center">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 w-full max-w-2xl p-8 h-fit">
                <form action="editar_articulo.php" method="POST" class="space-y-5">
                    <input type="hidden" name="id_articulo" value="<?php echo $art['id_articulo']; ?>">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">SKU / Código VIN</label>
                            <input type="text" name="sku_vin" required value="<?php echo htmlspecialchars($art['sku_vin']); ?>" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Categoría</label>
                            <select name="categoria" class="w-full px-3 py-2 border rounded-lg bg-white">
                                <?php foreach(['Repuesto', 'Maquinaria', 'Vehiculo', 'Accesorio'] as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php if($art['categoria'] == $cat) echo 'selected'; ?>><?php echo $cat; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nombre del Artículo</label>
                        <input type="text" name="nombre" required value="<?php echo htmlspecialchars($art['nombre']); ?>" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Costo Compra (Bs.)</label>
                            <input type="number" step="0.01" name="precio_compra" required value="<?php echo $art['precio_compra']; ?>" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Precio Venta (Bs.)</label>
                            <input type="number" step="0.01" name="precio_venta" required value="<?php echo $art['precio_venta']; ?>" class="w-full px-3 py-2 border rounded-lg font-bold text-blue-600">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Stock Mínimo Alerta</label>
                            <input type="number" name="stock_minimo" required value="<?php echo $art['stock_minimo']; ?>" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">URL de Fotografía</label>
                        <input type="text" name="imagen_url" value="<?php echo htmlspecialchars($art['imagen_url'] ?? ''); ?>" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>