<?php
require_once 'config.php';
verificarSesion();

// Obtener rango de fechas (por defecto: último mes)
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

// Reporte de ventas por período
$sql_ventas_periodo = "SELECT 
    DATE(Venta_Fecha) as Fecha,
    COUNT(*) as Total_Ventas,
    SUM(Total) as Monto_Total,
    AVG(Total) as Ticket_Promedio
FROM Venta
WHERE DATE(Venta_Fecha) BETWEEN ? AND ?
GROUP BY DATE(Venta_Fecha)
ORDER BY Fecha DESC";

$stmt = $conn->prepare($sql_ventas_periodo);
$stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
$stmt->execute();
$ventas_periodo = $stmt->get_result();

// Resumen del período
$sql_resumen = "SELECT 
    COUNT(*) as total_ventas,
    COALESCE(SUM(Total), 0) as ingresos_totales,
    COALESCE(AVG(Total), 0) as ticket_promedio,
    COALESCE(MAX(Total), 0) as venta_maxima
FROM Venta
WHERE DATE(Venta_Fecha) BETWEEN ? AND ?";

$stmt = $conn->prepare($sql_resumen);
$stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
$stmt->execute();
$resumen = $stmt->get_result()->fetch_assoc();

// Top 10 productos más vendidos
$sql_top_productos = "SELECT 
    p.Nombre,
    p.Tipo_Producto,
    SUM(dv.Cantidad_vendida) as Unidades_Vendidas,
    SUM(dv.Subtotal) as Ingresos_Generados,
    COUNT(DISTINCT dv.ID_Venta) as Num_Ventas
FROM Detalle_Venta dv
INNER JOIN Producto p ON dv.ID_Producto = p.ID_Producto
INNER JOIN Venta v ON dv.ID_Venta = v.ID_Venta
WHERE DATE(v.Venta_Fecha) BETWEEN ? AND ?
GROUP BY p.ID_Producto
ORDER BY Unidades_Vendidas DESC
LIMIT 10";

$stmt = $conn->prepare($sql_top_productos);
$stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
$stmt->execute();
$top_productos = $stmt->get_result();

// Productos por categoría
$sql_por_categoria = "SELECT 
    p.Tipo_Producto,
    COUNT(DISTINCT p.ID_Producto) as Total_Productos,
    SUM(dv.Cantidad_vendida) as Unidades_Vendidas,
    SUM(dv.Subtotal) as Ingresos
FROM Producto p
LEFT JOIN Detalle_Venta dv ON p.ID_Producto = dv.ID_Producto
LEFT JOIN Venta v ON dv.ID_Venta = v.ID_Venta AND DATE(v.Venta_Fecha) BETWEEN ? AND ?
GROUP BY p.Tipo_Producto";

$stmt = $conn->prepare($sql_por_categoria);
$stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
$stmt->execute();
$por_categoria = $stmt->get_result();

// Empleados con más ventas
$sql_top_empleados = "SELECT 
    e.Nombre,
    e.Apellido,
    e.Puesto,
    COUNT(v.ID_Venta) as Total_Ventas,
    SUM(v.Total) as Monto_Vendido
FROM Empleado e
INNER JOIN Venta v ON e.ID_Empleado = v.ID_Empleado
WHERE DATE(v.Venta_Fecha) BETWEEN ? AND ?
GROUP BY e.ID_Empleado
ORDER BY Monto_Vendido DESC";

$stmt = $conn->prepare($sql_top_empleados);
$stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
$stmt->execute();
$top_empleados = $stmt->get_result();

// Productos con bajo stock
$sql_stock_bajo = "SELECT 
    Nombre,
    Tipo_Producto,
    Cantidad_Stock,
    Precio_Venta,
    Cantidad_Stock * Precio_Venta as Valor_Inventario
FROM Producto
WHERE Cantidad_Stock < 10
ORDER BY Cantidad_Stock ASC";
$stock_bajo = $conn->query($sql_stock_bajo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Tienda Don Manolo</title>
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
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            gap: 15px;
            align-items: end;
        }
        
        .filter-group {
            flex: 1;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }
        
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
            font-weight: 600;
        }
        
        .btn-print {
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .stat-card .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 14px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
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
            font-weight: 600;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-perecedero {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-abarrote {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #f8d7da;
            color: #721c24;
        }
        
        @media print {
            .navbar, .filters, .btn-back, .btn-filter, .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">🛒 Tienda Don Manolo</a>
        <div class="navbar-user">
            <span>👤 <?php echo obtenerNombreUsuario(); ?></span>
            <a href="dashboard.php" class="btn-back">← Volver al Panel</a>
        </div>
    </nav>
    
    <div class="container">
        <h1 style="margin-bottom: 30px; color: #333;">📊 Reportes y Estadísticas</h1>
        
        <!-- Filtros -->
        <form method="GET" class="filters">
            <div class="filter-group">
                <label>Fecha Inicio</label>
                <input type="date" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
            </div>
            <div class="filter-group">
                <label>Fecha Fin</label>
                <input type="date" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
            </div>
            <button type="submit" class="btn-filter">🔍 Generar Reporte</button>
            <button type="button" class="btn-print" onclick="window.print()">🖨️ Imprimir</button>
        </form>
        
        <!-- Resumen General -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">💰</div>
                <div class="number">$<?php echo number_format($resumen['ingresos_totales'], 2); ?></div>
                <div class="label">Ingresos Totales</div>
            </div>
            <div class="stat-card">
                <div class="icon">🛍️</div>
                <div class="number"><?php echo $resumen['total_ventas']; ?></div>
                <div class="label">Total de Ventas</div>
            </div>
            <div class="stat-card">
                <div class="icon">🎫</div>
                <div class="number">$<?php echo number_format($resumen['ticket_promedio'], 2); ?></div>
                <div class="label">Ticket Promedio</div>
            </div>
            <div class="stat-card">
                <div class="icon">🏆</div>
                <div class="number">$<?php echo number_format($resumen['venta_maxima'], 2); ?></div>
                <div class="label">Venta Máxima</div>
            </div>
        </div>
        
        <!-- Ventas por Día -->
        <div class="card">
            <h2>📅 Ventas por Día</h2>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Total Ventas</th>
                        <th>Monto Total</th>
                        <th>Ticket Promedio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $ventas_periodo->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($row['Fecha'])); ?></td>
                        <td><?php echo $row['Total_Ventas']; ?> ventas</td>
                        <td style="font-weight: 600; color: #667eea;">$<?php echo number_format($row['Monto_Total'], 2); ?></td>
                        <td>$<?php echo number_format($row['Ticket_Promedio'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Top 10 Productos -->
        <div class="card">
            <h2>🏆 Top 10 Productos Más Vendidos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Unidades Vendidas</th>
                        <th>Núm. Ventas</th>
                        <th>Ingresos Generados</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $top_productos->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['Nombre']; ?></td>
                        <td>
                            <span class="badge badge-<?php echo strtolower($row['Tipo_Producto']); ?>">
                                <?php echo $row['Tipo_Producto']; ?>
                            </span>
                        </td>
                        <td><?php echo $row['Unidades_Vendidas']; ?> unidades</td>
                        <td><?php echo $row['Num_Ventas']; ?> ventas</td>
                        <td style="font-weight: 600; color: #28a745;">$<?php echo number_format($row['Ingresos_Generados'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Ventas por Categoría -->
        <div class="card">
            <h2>📦 Ventas por Categoría de Producto</h2>
            <table>
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th>Total Productos</th>
                        <th>Unidades Vendidas</th>
                        <th>Ingresos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $por_categoria->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <span class="badge badge-<?php echo strtolower($row['Tipo_Producto']); ?>">
                                <?php echo $row['Tipo_Producto']; ?>
                            </span>
                        </td>
                        <td><?php echo $row['Total_Productos']; ?> productos</td>
                        <td><?php echo $row['Unidades_Vendidas'] ?: 0; ?> unidades</td>
                        <td style="font-weight: 600; color: #667eea;">$<?php echo number_format($row['Ingresos'] ?: 0, 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Top Empleados -->
        <div class="card">
            <h2>👥 Rendimiento de Empleados</h2>
            <table>
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Puesto</th>
                        <th>Total Ventas</th>
                        <th>Monto Vendido</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $top_empleados->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['Nombre'] . ' ' . $row['Apellido']; ?></td>
                        <td><?php echo $row['Puesto']; ?></td>
                        <td><?php echo $row['Total_Ventas']; ?> ventas</td>
                        <td style="font-weight: 600; color: #667eea;">$<?php echo number_format($row['Monto_Vendido'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Productos con Stock Bajo -->
        <div class="card">
            <h2>⚠️ Alerta: Productos con Stock Bajo</h2>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Stock Actual</th>
                        <th>Precio</th>
                        <th>Valor en Inventario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stock_bajo->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['Nombre']; ?></td>
                        <td>
                            <span class="badge badge-<?php echo strtolower($row['Tipo_Producto']); ?>">
                                <?php echo $row['Tipo_Producto']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-warning">
                                <?php echo $row['Cantidad_Stock']; ?> unidades
                            </span>
                        </td>
                        <td>$<?php echo number_format($row['Precio_Venta'], 2); ?></td>
                        <td>$<?php echo number_format($row['Valor_Inventario'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>