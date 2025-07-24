<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../db/conexion.php";

// Verificar si se envió el formulario
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    // Buscar el usuario
    $sql = "SELECT * FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $fila = $resultado->fetch_assoc();
        $hash_enviado = hash("sha256", $contrasena);

        if ($fila['contrasena'] === $hash_enviado) {
            // Guardar sesión y redirigir
            $_SESSION['usuario'] = $usuario;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "⚠️ Contraseña incorrecta.";
        }
    } else {
        $error = "⚠️ Usuario no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Administrador</title>
    <link href="../assets/css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-bold text-center mb-6">Ingreso al sistema</h2>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="text" name="usuario" placeholder="Usuario"
                   class="w-full border border-gray-300 rounded px-4 py-2" required>

            <input type="password" name="contrasena" placeholder="Contraseña"
                   class="w-full border border-gray-300 rounded px-4 py-2" required>

            <button type="submit"
                    class="bg-blue-600 text-white font-semibold px-6 py-2 rounded hover:bg-blue-700 w-full">
                Ingresar
            </button>
        </form>
    </div>
</body>
</html>
