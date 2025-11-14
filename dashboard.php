<?php
require_once 'config.php';
verificarSesion();

// Obtener estadísticas
$sql_productos = "SELECT COUNT(*) as total FROM Producto";
$sql_ventas_hoy = "SELECT COUNT(*) as total, COALESCE(SUM(Total), 0) as monto 
                   FROM Venta 
                   WHERE DATE(Venta_Fecha) = CURDATE()";
$sql_stock_bajo = "SELECT COUNT(*) as total FROM Producto WHERE Cantidad_Stock < 10";
$sql_proveedores = "SELECT COUNT(*) as total FROM Proveedor";

$productos_total = $conn->query($sql_productos)->fetch_assoc()['total'];
$ventas_hoy = $conn->query($sql_ventas_hoy)->fetch_assoc();
$stock_bajo = $conn->query($sql_stock_bajo)->fetch_assoc()['total'];
$proveedores_total = $conn->query($sql_proveedores)->fetch_assoc()['total'];

// Productos más vendidos
$sql_mas_vendidos = "SELECT p.Nombre, SUM(dv.Cantidad_vendida) as total_vendido, 
                      SUM(dv.Subtotal) as ingresos
                      FROM Detalle_Venta dv
                      INNER JOIN Producto p ON dv.ID_Producto = p.ID_Producto
                      GROUP BY p.ID_Producto
                      ORDER BY total_vendido DESC
                      LIMIT 5";
$mas_vendidos = $conn->query($sql_mas_vendidos);

// Productos con stock bajo
$sql_stock_critico = "SELECT Nombre, Cantidad_Stock, Tipo_Producto
                       FROM Producto
                       WHERE Cantidad_Stock < 10
                       ORDER BY Cantidad_Stock ASC
                       LIMIT 5";
$stock_critico = $conn->query($sql_stock_critico);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tienda Don Manolo</title>
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
        }
        
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .btn-logout {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .menu-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: #333;
        }
        
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .menu-card .icon {
            font-size: 50px;
            margin-bottom: 15px;
        }
        
        .menu-card h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #667eea;
        }
        
        .menu-card p {
            font-size: 14px;
            color: #666;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 14px;
        }
        
        .stat-card .icon {
            font-size: 30px;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .card h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 20px;
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
        
        .badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">🏪 Tienda Don Manolo</div>
        <div class="navbar-user">
            <span>👤 <?php echo obtenerNombreUsuario(); ?></span>
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </nav>
    
    <div class="container">
        <h1 style="margin-bottom: 30px; color: #333;">Panel de Control</h1>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">📦</div>
                <div class="number"><?php echo $productos_total; ?></div>
                <div class="label">Productos en Catálogo</div>
            </div>
            <div class="stat-card">
                <div class="icon">💰</div>
                <div class="number">$<?php echo number_format($ventas_hoy['monto'], 2); ?></div>
                <div class="label">Ventas Hoy (<?php echo $ventas_hoy['total']; ?>)</div>
            </div>
            <div class="stat-card">
                <div class="icon">⚠️</div>
                <div class="number"><?php echo $stock_bajo; ?></div>
                <div class="label">Productos con Stock Bajo</div>
            </div>
            <div class="stat-card">
                <div class="icon">🚚</div>
                <div class="number"><?php echo $proveedores_total; ?></div>
                <div class="label">Proveedores Activos</div>
            </div>
        </div>
        
        <!-- Menú de Navegación -->
        <div class="menu-grid">
            <a href="productos.php" class="menu-card">
                <div class="icon">📦</div>
                <h3>Productos</h3>
                <p>Gestionar inventario y catálogo</p>
            </a>
            <a href="ventas.php" class="menu-card">
                <div class="icon">🛒</div>
                <h3>Registrar Venta</h3>
                <p>Nueva venta de productos</p>
            </a>
            <a href="proveedores.php" class="menu-card">
                <div class="icon">🚚</div>
                <h3>Proveedores</h3>
                <p>Administrar proveedores</p>
            </a>
            <a href="reportes.php" class="menu-card">
                <div class="icon">📊</div>
                <h3>Reportes</h3>
                <p>Análisis y estadísticas</p>
            </a>
        </div>
        
        <!-- Contenido -->
        <div class="content-grid">
            <div class="card">
                <h2>🏆 Productos Más Vendidos</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Vendidos</th>
                            <th>Ingresos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $mas_vendidos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['Nombre']; ?></td>
                            <td><?php echo $row['total_vendido']; ?> unidades</td>
                            <td>$<?php echo number_format($row['ingresos'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h2>⚠️ Productos con Stock Bajo</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $stock_critico->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['Nombre']; ?></td>
                            <td>
                                <?php if ($row['Tipo_Producto'] == 'Perecedero'): ?>
                                    <span class="badge badge-warning">Perecedero</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Abarrote</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $row['Cantidad_Stock'] < 5 ? 'badge-danger' : 'badge-warning'; ?>">
                                    <?php echo $row['Cantidad_Stock']; ?> unidades
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>