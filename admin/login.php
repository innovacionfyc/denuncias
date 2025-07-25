<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../db/conexion.php";

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    $sql = "SELECT * FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $fila = $resultado->fetch_assoc();
        $hash_enviado = hash("sha256", $contrasena);

        if ($fila['contrasena'] === $hash_enviado) {
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
    <body class="bg-gray-100 min-h-screen flex items-center justify-center px-4">
      <div class="bg-white w-full max-w-[400px] p-6 sm:p-8 rounded-2xl shadow-2xl border border-gray-300 flex flex-col items-center">
      
          <!-- 🦉 Búho -->
          <img src="../assets/img/Ovi6.gif" alt="Búho F&C" class="w-24 sm:w-28 h-auto mb-4">

          <h2 class="text-2xl font-bold text-center mb-6 text-[#942934]">Ingreso al sistema</h2>

          <?php if (!empty($error)): ?>
              <div class="bg-red-100 text-red-700 p-3 mb-4 rounded border border-red-300 animate-pulse w-full text-sm">
                  <?php echo $error; ?>
              </div>
          <?php endif; ?>

          <form method="POST" class="space-y-4 w-full">
              <input type="text" name="usuario" placeholder="Usuario"
                  class="w-full border border-gray-300 rounded-xl px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:ring-2 focus:ring-[#d32f57]"
                  required>

              <input type="password" name="contrasena" placeholder="Contraseña"
                  class="w-full border border-gray-300 rounded-xl px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:ring-2 focus:ring-[#d32f57]"
                  required>

              <button type="submit"
                  class="w-full bg-[#d32f57] text-white font-semibold px-6 py-2 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98] hover:bg-[#942934]">
                  Ingresar
              </button>
          </form>
      </div>
    </body>

</html>
