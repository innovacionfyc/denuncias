<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Sesión cerrada</title>
  <meta http-equiv="refresh" content="3;url=index.php">
  <link href="assets/css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex flex-col justify-center items-center min-h-screen px-4">
  
  <!-- Búho volando -->
  <img src="../assets/img/Ovi3.gif" alt="Búho volando" class="w-32 sm:w-40 mb-6">

  <!-- Mensaje -->
  <h1 class="text-xl sm:text-2xl font-bold text-[#942934] text-center mb-2">¡Sesión cerrada correctamente!</h1>
  <p class="text-gray-600 text-center text-sm">Serás redirigido en unos segundos...</p>

</body>
</html>
