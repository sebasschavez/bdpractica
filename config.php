<?php
// config.php - Configuración de conexión a la base de datos
// VERSIÓN PARA DOCKER

// Iniciar sesión
session_start();

// Configuración de la base de datos
// Para Docker, el host es 'db' (nombre del servicio en docker-compose)
// Para XAMPP/WAMP local, cambiar a 'localhost'
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'rootpassword');
define('DB_NAME', getenv('DB_NAME') ?: 'tienda_don_manolo');

// Crear conexión con manejo de errores mejorado
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar conexión
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    // Establecer charset UTF-8
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    // En desarrollo, mostrar error
    if (getenv('ENVIRONMENT') === 'development') {
        die("Error de base de datos: " . $e->getMessage());
    }
    // En producción, registrar y mostrar mensaje genérico
    error_log($e->getMessage());
    die("Error al conectar con la base de datos. Contacte al administrador.");
}

// Funciones auxiliares
function verificarSesion() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }
}

function limpiarDato($dato) {
    global $conn;
    return $conn->real_escape_string(trim($dato));
}

function obtenerNombreUsuario() {
    return isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : '';
}

function obtenerIdUsuario() {
    return isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 0;
}

// Función para logging (útil en producción)
function registrarLog($mensaje, $nivel = 'INFO') {
    $fecha = date('Y-m-d H:i:s');
    $log = "[$fecha] [$nivel] $mensaje" . PHP_EOL;
    error_log($log, 3, __DIR__ . '/logs/app.log');
}
?>