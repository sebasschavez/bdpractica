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
                $nombre = limpiarDato($_POST['nombre_empresa']);
                $email = limpiarDato($_POST['email']);
                $telefono = limpiarDato($_POST['telefono']);
                
                $sql = "INSERT INTO Proveedor (Nombre_Empresa, Email, Telefono) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $nombre, $email, $telefono);
                
                if ($stmt->execute()) {
                    $mensaje = "Proveedor agregado exitosamente";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al agregar proveedor";
                    $tipo_mensaje = "error";
                }
                break;
                
            case 'editar':
                $id = intval($_POST['id_proveedor']);
                $nombre = limpiarDato($_POST['nombre_empresa']);
                $email = limpiarDato($_POST['email']);
                $telefono = limpiarDato($_POST['telefono']);
                
                $sql = "UPDATE Proveedor SET Nombre_Empresa = ?, Email = ?, Telefono = ? WHERE ID_Proveedor = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $nombre, $email, $telefono, $id);
                
                if ($stmt->execute()) {
                    $mensaje = "Proveedor actualizado exitosamente";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al actualizar proveedor";
                    $tipo_mensaje = "error";
                }
                break;
                
            case 'eliminar':
                $id = intval($_POST['id_proveedor']);
                
                $sql = "DELETE FROM Proveedor WHERE ID_Proveedor = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $mensaje = "Proveedor eliminado exitosamente";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al eliminar proveedor. Puede tener productos asociados";
                    $tipo_mensaje = "error";
                }
                break;
                
            case 'agregar_suministro':
                $id_proveedor = intval($_POST['id_proveedor']);
                $id_producto = intval($_POST['id_producto']);
                $costo = floatval($_POST['costo_compra']);
                
                $sql = "INSERT INTO Suministra (ID_Proveedor, ID_Producto, Costo_de_Compra, Fecha_Ultimo_Suministro) 
                       VALUES (?, ?, ?, CURDATE())
                       ON DUPLICATE KEY UPDATE Costo_de_Compra = ?, Fecha_Ultimo_Suministro = CURDATE()";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iidd", $id_proveedor, $id_producto, $costo, $costo);
                
                if ($stmt->execute()) {
                    $mensaje = "Suministro registrado exitosamente";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al registrar suministro";
                    $tipo_mensaje = "error";
                }
                break;
        }
    }
}

// Obtener proveedores
$sql_proveedores = "SELECT p.*, COUNT(DISTINCT s.ID_Producto) as total_productos
                    FROM Proveedor p
                    LEFT JOIN Suministra s ON p.ID_Proveedor = s.ID_Proveedor
                    GROUP BY p.ID_Proveedor
                    ORDER BY p.Nombre_Empresa";
$proveedores = $conn->query($sql_proveedores);

// Obtener productos para el selector
$sql_productos = "SELECT ID_Producto, Nombre FROM Producto ORDER BY Nombre";
$productos = $conn->query($sql_productos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores - Tienda Don Manolo</title>
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
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
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
        
        .proveedores-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .proveedor-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        
        .proveedor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .proveedor-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .proveedor-nombre {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }
        
        .proveedor-icon {
            font-size: 40px;
        }
        
        .proveedor-info {
            margin: 15px 0;
        }
        
        .info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            color: #666;
            font-size: 14px;
        }
        
        .info-icon {
            font-size: 18px;
            min-width: 25px;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .proveedor-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
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
        
        .btn-products {
            background: #17a2b8;
            color: white;
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
            max-width: 500px;
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
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
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
        }
        
        .btn-submit {
            flex: 1;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
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
            <h1>🚚 Gestión de Proveedores</h1>
            <button class="btn-primary" onclick="abrirModalNuevo()">+ Nuevo Proveedor</button>
        </div>
        
        <?php if ($mensaje): ?>
        <div class="message <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
        <?php endif; ?>
        
        <div class="proveedores-grid">
            <?php while ($prov = $proveedores->fetch_assoc()): ?>
            <div class="proveedor-card">
                <div class="proveedor-header">
                    <div>
                        <div class="proveedor-nombre"><?php echo $prov['Nombre_Empresa']; ?></div>
                        <span class="badge"><?php echo $prov['total_productos']; ?> productos</span>
                    </div>
                    <div class="proveedor-icon">🏭</div>
                </div>
                
                <div class="proveedor-info">
                    <?php if ($prov['Email']): ?>
                    <div class="info-row">
                        <span class="info-icon">📧</span>
                        <span><?php echo $prov['Email']; ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($prov['Telefono']): ?>
                    <div class="info-row">
                        <span class="info-icon">📱</span>
                        <span><?php echo $prov['Telefono']; ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="proveedor-actions">
                    <button class="btn-small btn-edit" onclick='editarProveedor(<?php echo json_encode($prov); ?>)'>✏️ Editar</button>
                    <button class="btn-small btn-products" onclick="verProductos(<?php echo $prov['ID_Proveedor']; ?>)">📦 Productos</button>
                    <button class="btn-small btn-delete" onclick="confirmarEliminar(<?php echo $prov['ID_Proveedor']; ?>)">🗑️</button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <!-- Modal Nuevo Proveedor -->
    <div id="modalNuevo" class="modal">
        <div class="modal-content">
            <h2 class="modal-header">Agregar Nuevo Proveedor</h2>
            <form method="POST">
                <input type="hidden" name="accion" value="agregar">
                
                <div class="form-group">
                    <label>Nombre de la Empresa *</label>
                    <input type="text" name="nombre_empresa" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email">
                </div>
                
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn-submit">Guardar Proveedor</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Editar Proveedor -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <h2 class="modal-header">Editar Proveedor</h2>
            <form method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id_proveedor" id="edit_id">
                
                <div class="form-group">
                    <label>Nombre de la Empresa *</label>
                    <input type="text" name="nombre_empresa" id="edit_nombre" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email">
                </div>
                
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" id="edit_telefono">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="cerrarModalEditar()">Cancelar</button>
                    <button type="submit" class="btn-submit">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Productos del Proveedor -->
    <div id="modalProductos" class="modal">
        <div class="modal-content">
            <h2 class="modal-header">Productos del Proveedor</h2>
            <div id="listaProductos">Cargando...</div>
            <button type="button" class="btn-cancel" style="width: 100%; margin-top: 20px;" onclick="cerrarModalProductos()">Cerrar</button>
        </div>
    </div>
    
    <script>
        function abrirModalNuevo() {
            document.getElementById('modalNuevo').classList.add('active');
        }
        
        function cerrarModal() {
            document.getElementById('modalNuevo').classList.remove('active');
        }
        
        function editarProveedor(prov) {
            document.getElementById('edit_id').value = prov.ID_Proveedor;
            document.getElementById('edit_nombre').value = prov.Nombre_Empresa;
            document.getElementById('edit_email').value = prov.Email || '';
            document.getElementById('edit_telefono').value = prov.Telefono || '';
            document.getElementById('modalEditar').classList.add('active');
        }
        
        function cerrarModalEditar() {
            document.getElementById('modalEditar').classList.remove('active');
        }
        
        function confirmarEliminar(id) {
            if (confirm('¿Está seguro de eliminar este proveedor?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id_proveedor" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function verProductos(idProveedor) {
            document.getElementById('modalProductos').classList.add('active');
            // Aquí podrías hacer una petición AJAX para cargar los productos
            document.getElementById('listaProductos').innerHTML = '<p>Funcionalidad de productos por implementar con AJAX</p>';
        }
        
        function cerrarModalProductos() {
            document.getElementById('modalProductos').classList.remove('active');
        }
    </script>
</body>
</html>