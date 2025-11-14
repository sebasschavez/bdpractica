<?php
require_once 'config.php';
verificarSesion();

$mensaje = '';
$tipo_mensaje = '';

// Procesar venta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'procesar_venta') {
    $productos_venta = json_decode($_POST['productos_carrito'], true);
    $total = floatval($_POST['total_venta']);
    $id_empleado = obtenerIdUsuario();
    
    if (count($productos_venta) > 0) {
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Insertar venta
            $sql_venta = "INSERT INTO Venta (Total, ID_Empleado) VALUES (?, ?)";
            $stmt_venta = $conn->prepare($sql_venta);
            $stmt_venta->bind_param("di", $total, $id_empleado);
            $stmt_venta->execute();
            $id_venta = $conn->insert_id;
            
            // Insertar detalles de venta
            $sql_detalle = "INSERT INTO Detalle_Venta (ID_Venta, ID_Producto, Cantidad_vendida, Precio_en_Venta) 
                           VALUES (?, ?, ?, ?)";
            $stmt_detalle = $conn->prepare($sql_detalle);
            
            foreach ($productos_venta as $producto) {
                $id_producto = $producto['id'];
                $cantidad = $producto['cantidad'];
                $precio = $producto['precio'];
                
                $stmt_detalle->bind_param("iiid", $id_venta, $id_producto, $cantidad, $precio);
                $stmt_detalle->execute();
            }
            
            $conn->commit();
            $mensaje = "Venta #$id_venta procesada exitosamente. Total: $" . number_format($total, 2);
            $tipo_mensaje = "success";
            
        } catch (Exception $e) {
            $conn->rollback();
            $mensaje = "Error al procesar la venta: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "El carrito está vacío";
        $tipo_mensaje = "error";
    }
}

// Obtener productos disponibles
$sql_productos = "SELECT ID_Producto, Nombre, Precio_Venta, Cantidad_Stock, Tipo_Producto 
                  FROM Producto 
                  WHERE Cantidad_Stock > 0 
                  ORDER BY Nombre";
$productos = $conn->query($sql_productos);

// Obtener últimas ventas
$sql_ultimas_ventas = "SELECT v.ID_Venta, v.Venta_Fecha, v.Total, e.Nombre, e.Apellido,
                       COUNT(dv.ID_Producto) as total_productos
                       FROM Venta v
                       INNER JOIN Empleado e ON v.ID_Empleado = e.ID_Empleado
                       LEFT JOIN Detalle_Venta dv ON v.ID_Venta = dv.ID_Venta
                       GROUP BY v.ID_Venta
                       ORDER BY v.Venta_Fecha DESC
                       LIMIT 10";
$ultimas_ventas = $conn->query($sql_ultimas_ventas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - Tienda Don Manolo</title>
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
            max-width: 1600px;
            margin: 30px auto;
            padding: 0 20px;
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
        
        .ventas-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
        }
        
        .productos-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .section-title {
            font-size: 24px;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .buscar-producto {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            margin-bottom: 20px;
        }
        
        .productos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            max-height: 600px;
            overflow-y: auto;
        }
        
        .producto-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .producto-item:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .producto-nombre {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .producto-precio {
            color: #667eea;
            font-size: 18px;
            font-weight: bold;
            margin: 8px 0;
        }
        
        .producto-stock {
            font-size: 12px;
            color: #666;
        }
        
        .carrito-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .carrito-items {
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        
        .carrito-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .carrito-item-info {
            flex: 1;
        }
        
        .carrito-item-nombre {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .carrito-item-precio {
            color: #667eea;
            font-size: 13px;
        }
        
        .carrito-item-cantidad {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-cantidad {
            width: 30px;
            height: 30px;
            border: none;
            background: #667eea;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-cantidad:hover {
            background: #5568d3;
        }
        
        .cantidad-display {
            min-width: 30px;
            text-align: center;
            font-weight: 600;
        }
        
        .btn-eliminar {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .carrito-total {
            border-top: 2px solid #e0e0e0;
            padding-top: 15px;
            margin-top: 15px;
        }
        
        .total-label {
            font-size: 18px;
            color: #666;
        }
        
        .total-monto {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        
        .btn-procesar {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-procesar:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-procesar:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn-limpiar {
            width: 100%;
            padding: 12px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            margin-top: 10px;
            cursor: pointer;
        }
        
        .carrito-vacio {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        
        .ultimas-ventas {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-size: 14px;
            color: #666;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        
        @media (max-width: 1200px) {
            .ventas-layout {
                grid-template-columns: 1fr;
            }
            
            .carrito-section {
                position: static;
            }
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
        <h1 style="margin-bottom: 30px; color: #333;">🛒 Punto de Venta</h1>
        
        <?php if ($mensaje): ?>
        <div class="message <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
        <?php endif; ?>
        
        <div class="ventas-layout">
            <!-- Productos -->
            <div class="productos-section">
                <h2 class="section-title">📦 Productos Disponibles</h2>
                <input type="text" class="buscar-producto" id="buscarProducto" placeholder="🔍 Buscar producto...">
                
                <div class="productos-grid" id="productosGrid">
                    <?php while ($prod = $productos->fetch_assoc()): ?>
                    <div class="producto-item" onclick='agregarAlCarrito(<?php echo json_encode($prod); ?>)'>
                        <div class="producto-nombre"><?php echo $prod['Nombre']; ?></div>
                        <div class="producto-precio">$<?php echo number_format($prod['Precio_Venta'], 2); ?></div>
                        <div class="producto-stock">
                            Stock: <?php echo $prod['Cantidad_Stock']; ?> 
                            <span style="background: <?php echo $prod['Tipo_Producto'] == 'Perecedero' ? '#fff3cd' : '#d4edda'; ?>; 
                                         padding: 2px 6px; border-radius: 3px; font-size: 11px;">
                                <?php echo $prod['Tipo_Producto']; ?>
                            </span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <!-- Carrito -->
            <div class="carrito-section">
                <h2 class="section-title">🛍️ Carrito</h2>
                
                <div class="carrito-items" id="carritoItems">
                    <div class="carrito-vacio">
                        <p style="font-size: 50px;">🛒</p>
                        <p>El carrito está vacío</p>
                        <p style="font-size: 12px; margin-top: 10px;">Selecciona productos para agregar</p>
                    </div>
                </div>
                
                <div class="carrito-total">
                    <div class="total-label">Total a Pagar:</div>
                    <div class="total-monto" id="totalMonto">$0.00</div>
                    
                    <form method="POST" id="formVenta">
                        <input type="hidden" name="accion" value="procesar_venta">
                        <input type="hidden" name="productos_carrito" id="productosCarrito">
                        <input type="hidden" name="total_venta" id="totalVenta">
                        
                        <button type="submit" class="btn-procesar" id="btnProcesar" disabled>
                            💳 Procesar Venta
                        </button>
                    </form>
                    
                    <button class="btn-limpiar" onclick="limpiarCarrito()">🗑️ Limpiar Carrito</button>
                </div>
            </div>
        </div>
        
        <!-- Últimas Ventas -->
        <div class="ultimas-ventas">
            <h2 class="section-title">📋 Últimas Ventas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Empleado</th>
                        <th>Productos</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($venta = $ultimas_ventas->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $venta['ID_Venta']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($venta['Venta_Fecha'])); ?></td>
                        <td><?php echo $venta['Nombre'] . ' ' . $venta['Apellido']; ?></td>
                        <td><?php echo $venta['total_productos']; ?> items</td>
                        <td style="font-weight: 600; color: #667eea;">$<?php echo number_format($venta['Total'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        let carrito = [];
        
        // Agregar al carrito
        function agregarAlCarrito(producto) {
            const existe = carrito.find(item => item.id === producto.ID_Producto);
            
            if (existe) {
                if (existe.cantidad < producto.Cantidad_Stock) {
                    existe.cantidad++;
                } else {
                    alert('No hay suficiente stock disponible');
                    return;
                }
            } else {
                carrito.push({
                    id: producto.ID_Producto,
                    nombre: producto.Nombre,
                    precio: parseFloat(producto.Precio_Venta),
                    cantidad: 1,
                    stock: producto.Cantidad_Stock
                });
            }
            
            actualizarCarrito();
        }
        
        // Actualizar cantidad
        function cambiarCantidad(id, cambio) {
            const item = carrito.find(i => i.id === id);
            if (item) {
                const nuevaCantidad = item.cantidad + cambio;
                
                if (nuevaCantidad > 0 && nuevaCantidad <= item.stock) {
                    item.cantidad = nuevaCantidad;
                    actualizarCarrito();
                } else if (nuevaCantidad > item.stock) {
                    alert('No hay suficiente stock');
                }
            }
        }
        
        // Eliminar del carrito
        function eliminarDelCarrito(id) {
            carrito = carrito.filter(item => item.id !== id);
            actualizarCarrito();
        }
        
        // Actualizar vista del carrito
        function actualizarCarrito() {
            const carritoItems = document.getElementById('carritoItems');
            const totalMonto = document.getElementById('totalMonto');
            const btnProcesar = document.getElementById('btnProcesar');
            
            if (carrito.length === 0) {
                carritoItems.innerHTML = `
                    <div class="carrito-vacio">
                        <p style="font-size: 50px;">🛒</p>
                        <p>El carrito está vacío</p>
                        <p style="font-size: 12px; margin-top: 10px;">Selecciona productos para agregar</p>
                    </div>
                `;
                totalMonto.textContent = '$0.00';
                btnProcesar.disabled = true;
                return;
            }
            
            let html = '';
            let total = 0;
            
            carrito.forEach(item => {
                const subtotal = item.precio * item.cantidad;
                total += subtotal;
                
                html += `
                    <div class="carrito-item">
                        <div class="carrito-item-info">
                            <div class="carrito-item-nombre">${item.nombre}</div>
                            <div class="carrito-item-precio">$${item.precio.toFixed(2)} x ${item.cantidad} = $${subtotal.toFixed(2)}</div>
                        </div>
                        <div class="carrito-item-cantidad">
                            <button type="button" class="btn-cantidad" onclick="cambiarCantidad(${item.id}, -1)">-</button>
                            <span class="cantidad-display">${item.cantidad}</span>
                            <button type="button" class="btn-cantidad" onclick="cambiarCantidad(${item.id}, 1)">+</button>
                            <button type="button" class="btn-eliminar" onclick="eliminarDelCarrito(${item.id})">🗑️</button>
                        </div>
                    </div>
                `;
            });
            
            carritoItems.innerHTML = html;
            totalMonto.textContent = '$' + total.toFixed(2);
            btnProcesar.disabled = false;
            
            // Actualizar campos ocultos
            document.getElementById('productosCarrito').value = JSON.stringify(carrito);
            document.getElementById('totalVenta').value = total.toFixed(2);
        }
        
        // Limpiar carrito
        function limpiarCarrito() {
            if (carrito.length > 0 && confirm('¿Deseas limpiar el carrito?')) {
                carrito = [];
                actualizarCarrito();
            }
        }
        
        // Buscar productos
        document.getElementById('buscarProducto').addEventListener('input', function(e) {
            const busqueda = e.target.value.toLowerCase();
            const productos = document.querySelectorAll('.producto-item');
            
            productos.forEach(prod => {
                const texto = prod.textContent.toLowerCase();
                prod.style.display = texto.includes(busqueda) ? 'block' : 'none';
            });
        });
        
        // Prevenir envío duplicado
        document.getElementById('formVenta').addEventListener('submit', function() {
            document.getElementById('btnProcesar').disabled = true;
            document.getElementById('btnProcesar').textContent = 'Procesando...';
        });
    </script>
</body>
</html>