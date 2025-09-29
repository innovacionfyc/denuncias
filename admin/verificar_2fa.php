<?php
session_start();
if (!isset($_SESSION['pending_2fa'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Verificación de acceso</title>
    <link href="../assets/css/output.css" rel="stylesheet">
</head>

<body class="bg-[#f8f9fb] min-h-screen flex items-center justify-center p-6">
    <form action="verificar_2fa_procesar.php" method="POST"
        class="bg-white p-8 rounded-2xl shadow w-full max-w-sm space-y-4 border border-gray-200">
        <h1 class="text-xl font-bold text-[#942934] text-center">Verificación de acceso</h1>
        <input name="codigo" maxlength="6" inputmode="numeric" pattern="\d{6}" placeholder="Código (6 dígitos)" required
            class="w-full border rounded px-3 py-2">
        <button class="w-full bg-[#942934] text-white rounded px-4 py-2">Validar</button>
        <?php if (isset($_GET['e'])): ?>
            <p class="text-red-600 text-sm text-center">Código inválido o vencido</p>
        <?php endif; ?>
    </form>
</body>

</html>