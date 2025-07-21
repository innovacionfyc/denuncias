<?php
require_once "db/conexion.php";

$sql = "SELECT * FROM usuarios LIMIT 1";
$resultado = $conn->query($sql);

if ($resultado->num_rows > 0) {
    echo "✅ Consulta exitosa. Hay al menos un usuario en la base de datos.";
} else {
    echo "⚠️ La tabla usuarios está vacía o no existe.";
}
