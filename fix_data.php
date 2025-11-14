<?php
header('Content-Type: text/html; charset=UTF-8');

$conn = new mysqli('db', 'root', 'rootpassword', 'tienda_don_manolo');
$conn->set_charset("utf8mb4");

// Actualizar el nombre correctamente
$sql = "UPDATE Empleado SET Apellido = 'Pérez' WHERE Usuario = 'donmanolo'";
$conn->query($sql);

echo "✅ Datos actualizados correctamente<br>";

// Verificar
$result = $conn->query("SELECT CONCAT(Nombre, ' ', Apellido) as nombre FROM Empleado WHERE Usuario = 'donmanolo'");
$row = $result->fetch_assoc();
echo "Nombre actual: " . $row['nombre'];

$conn->close();
?>