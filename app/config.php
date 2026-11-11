<?php
// Configuración de conexión a la Base de Datos PostgreSQL
session_start();

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'gestion_qa_db';
$user = getenv('DB_USER') ?: 'postgres';
$password = getenv('DB_PASS') ?: 'tu_password_aqui';

// Cargar configuración local privada si existe (ignorada en el repositorio)
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

function check_auth() {
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }
}
?>
