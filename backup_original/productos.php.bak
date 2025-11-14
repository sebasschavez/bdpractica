<?php
require_once 'config.php';
verificarSesion();

$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'agregar':
                $nombre = limpiarDato($_POST['nombre']);
                $descripcion = limpiarDato($_POST['descripcion']);
                $precio = floatval($_POST['precio']);
                $stock = intval($_POST['stock']);
                $tipo = limpiarDato($_POST['tipo_producto']);
                
                $sql = "INSERT INTO Producto (Nombre, Descripcion, Precio_Venta, Cantidad_Stock, Tipo_Producto";
                
                if ($tipo == 'Perecedero') {
                    $fecha_cad = $_POST['fecha_caducidad'];
                    $refrigeracion = isset($_POST['requiere_refrigeracion']) ? 1 : 0;
                    $sql .= ", Fecha_Caducidad, Requiere_Refrigeracion) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssdissi", $nombre, $descripcion, $precio, $stock, $tipo, $fecha_cad, $refrigeracion);
                } else {
                    $marca = limpiarDato($_POST['marca']);
                    $contenido = limpiarDato($_POST['contenido_neto']);
                    $sql .= ", Marca, Contenido_Neto) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssdisss", $nombre, $descripcion, $precio, $stock, $tipo, $marca, $contenido);
                }
                
                if ($stmt->execute()) {
                    $mensaje = "Producto agregado exitosamente";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al agregar producto: " . $conn->error;
                    $tipo_mensaje = "error";
                }
                break;
                
            case 'actualizar_stock':
                $id = intval($_POST['id_producto']);
                $nuevo_stock = intval($_POST['nuevo_stock']);
                
                $sql = "UPDATE Producto SET Cantidad_Stock = ? WHERE ID_Producto = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $nuevo_stock, $id);
                
                if ($stmt->execute()) {
                    $mensaje = "Stock actualizado exitosamente";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al actualizar stock";
                    $tipo_mensaje = "error";
                }
                break;
                
            case 'eliminar':
                $id = intval($_POST['id_producto']);
                
                $sql = "DELETE FROM Producto WHERE ID_Producto = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $mensaje = "Producto eliminado exitosamente";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al eliminar producto";
                    $tipo_mensaje = "error";
                }
                break;
        }
    }
}

// Obtener productos
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';
$busqueda = isset($_GET['busqueda']) ? limpiarDato($_GET['busqueda']) : '';

$sql = "SELECT * FROM Producto WHERE 1=1";

if ($filtro != 'todos') {
    $sql .= " AND Tipo_Producto = '$filtro'";
}

if ($busqueda != '') {
    $sql .= " AND (Nombre LIKE '%$busqueda%' OR Descripcion LIKE '%$busqueda%')";
}

$sql .= " ORDER BY Nombre";
$productos = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Tienda Don Manolo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }
        
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .btn-back {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .btn-back:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .filter-group {
            flex: 1;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #666;
        }
        
        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .btn-filter {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .product-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .product-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .product-type {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .type-perecedero {
            background: #fff3cd;
            color: #856404;
        }
        
        .type-abarrote {
            background: #d4edda;
            color: #155724;
        }
        
        .product-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .product-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
        }
        
        .info-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-small {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #333;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            font-size: 24px;
            margin-bottom: 20px;
            color: #667eea;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-cancel {
            flex: 1;
            padding: 12px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-submit {
            flex: 1;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">🏪 Tienda Don Manolo</a>
        <div class="navbar-user">
            <span>👤 <?php echo obtenerNombreUsuario(); ?></span>
            <a href="dashboard.php" class="btn-back">← Volver al Panel</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="header-actions">
            <h1>📦 Gestión de Productos</h1>
            <button class="btn-primary" onclick="abrirModal()">+ Nuevo Producto</button>
        </div>
        
        <?php if ($mensaje): ?>
        <div class="message <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
        <?php endif; ?>
        
        <!-- Filtros -->
        <form method="GET" class="filters">
            <div class="filter-group">
                <label>Tipo de Producto</label>
                <select name="filtro">
                    <option value="todos" <?php echo $filtro == 'todos' ? 'selected' : ''; ?>>Todos</option>
                    <option value="Perecedero" <?php echo $filtro == 'Perecedero' ? 'selected' : ''; ?>>Perecedero</option>
                    <option value="Abarrote" <?php echo $filtro == 'Abarrote' ? 'selected' : ''; ?>>Abarrote</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Buscar Producto</label>
                <input type="text" name="busqueda" placeholder="Nombre o descripción..." value="<?php echo $busqueda; ?>">
            </div>
            <button type="submit" class="btn-filter">🔍 Filtrar</button>
        </form>
        
        <!-- Grid de Productos -->
        <div class="products-grid">
            <?php while ($producto = $productos->fetch_assoc()): ?>
            <div class="product-card">
                <div class="product-header">
                    <h3 class="product-title"><?php echo $producto['Nombre']; ?></h3>
                    <span class="product-type type-<?php echo strtolower($producto['Tipo_Producto']); ?>">
                        <?php echo $producto['Tipo_Producto']; ?>
                    </span>
                </div>
                
                <p class="product-description"><?php echo $producto['Descripcion']; ?></p>
                
                <div class="product-info">
                    <div class="info-item">
                        <div class="info-label">Precio</div>
                        <div class="info-value">$<?php echo number_format($producto['Precio_Venta'], 2); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Stock</div>
                        <div class="info-value"><?php echo $producto['Cantidad_Stock']; ?> unidades</div>
                    </div>
                </div>
                
                <?php if ($producto['Tipo_Producto'] == 'Perecedero'): ?>
                    <div class="product-info">
                        <div class="info-item">
                            <div class="info-label">Fecha Caducidad</div>
                            <div class="info-value"><?php echo date('d/m/Y', strtotime($producto['Fecha_Caducidad'])); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Refrigeración</div>
                            <div class="info-value"><?php echo $producto['Requiere_Refrigeracion'] ? 'Sí' : 'No'; ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="product-info">
                        <div class="info-item">
                            <div class="info-label">Marca</div>
                            <div class="info-value"><?php echo $producto['Marca']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Contenido</div>
                            <div class="info-value"><?php echo $producto['Contenido_Neto']; ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="product-actions">
                    <button class="btn-small btn-edit" onclick="editarStock(<?php echo $producto['ID_Producto']; ?>, <?php echo $producto['Cantidad_Stock']; ?>)">✏️ Editar Stock</button>
                    <button class="btn-small btn-delete" onclick="confirmarEliminar(<?php echo $producto['ID_Producto']; ?>)">🗑️ Eliminar</button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <!-- Modal Nuevo Producto -->
    <div id="modalNuevo" class="modal">
        <div class="modal-content">
            <h2 class="modal-header">Agregar Nuevo Producto</h2>
            <form method="POST">
                <input type="hidden" name="accion" value="agregar">
                
                <div class="form-group">
                    <label>Nombre del Producto *</label>
                    <input type="text" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Precio de Venta *</label>
                        <input type="number" name="precio" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Stock Inicial *</label>
                        <input type="number" name="stock" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Tipo de Producto *</label>
                    <select name="tipo_producto" id="tipoProducto" onchange="cambiarTipo()" required>
                        <option value="">Seleccione...</option>
                        <option value="Perecedero">Perecedero</option>
                        <option value="Abarrote">Abarrote</option>
                    </select>
                </div>
                
                <div id="camposPerece dero" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Fecha de Caducidad</label>
                            <input type="date" name="fecha_caducidad">
                        </div>
                        <div class="form-group checkbox-group">
                            <input type="checkbox" name="requiere_refrigeracion" id="refri">
                            <label for="refri">Requiere Refrigeración</label>
                        </div>
                    </div>
                </div>
                
                <div id="camposAbarrote" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Marca</label>
                            <input type="text" name="marca">
                        </div>
                        <div class="form-group">
                            <label>Contenido Neto</label>
                            <input type="text" name="contenido_neto" placeholder="ej: 500g, 1L">
                        </div>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn-submit">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Editar Stock -->
    <div id="modalStock" class="modal">
        <div class="modal-content">
            <h2 class="modal-header">Actualizar Stock</h2>
            <form method="POST">
                <input type="hidden" name="accion" value="actualizar_stock">
                <input type="hidden" name="id_producto" id="id_producto_stock">
                
                <div class="form-group">
                    <label>Nuevo Stock</label>
                    <input type="number" name="nuevo_stock" id="nuevo_stock" required>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="cerrarModalStock()">Cancelar</button>
                    <button type="submit" class="btn-submit">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function abrirModal() {
            document.getElementById('modalNuevo').classList.add('active');
        }
        
        function cerrarModal() {
            document.getElementById('modalNuevo').classList.remove('active');
        }
        
        function cambiarTipo() {
            const tipo = document.getElementById('tipoProducto').value;
            const perecedero = document.getElementById('camposPerecedero');
            const abarrote = document.getElementById('camposAbarrote');
            
            if (tipo == 'Perecedero') {
                perecedero.style.display = 'block';
                abarrote.style.display = 'none';
            } else if (tipo == 'Abarrote') {
                perecedero.style.display = 'none';
                abarrote.style.display = 'block';
            } else {
                perecedero.style.display = 'none';
                abarrote.style.display = 'none';
            }
        }
        
        function editarStock(id, stockActual) {
            document.getElementById('id_producto_stock').value = id;
            document.getElementById('nuevo_stock').value = stockActual;
            document.getElementById('modalStock').classList.add('active');
        }
        
        function cerrarModalStock() {
            document.getElementById('modalStock').classList.remove('active');
        }
        
        function confirmarEliminar(id) {
            if (confirm('¿Está seguro de eliminar este producto?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id_producto" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>