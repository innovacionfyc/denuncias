<?php
// Datos de conexión
$host = "localhost";        // O el nombre de tu servidor
$usuario = "root";          // Usuario de MySQL
$contrasena = "";           // Contraseña (vacía si estás en XAMPP)
$base_datos = "denuncias_db";

// Crear conexión
$conn = new mysqli($host, $usuario, $contrasena, $base_datos);

// Verificar conexión
if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

// Establecer codificación para caracteres especiales
$conn->set_charset("utf8mb4");

// Puedes dejar esto como prueba para saber si se conectó bien
echo "✅ Conexión exitosa a la base de datos";
?>
