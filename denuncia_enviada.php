<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Denuncia enviada</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/css/output.css" rel="stylesheet">
</head>
<body class="bg-green-50 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-2xl rounded-2xl p-8 max-w-lg text-center border border-gray-300">
        <h1 class="text-2xl font-bold text-green-600 mb-4">✅ ¡Denuncia enviada con éxito!</h1>

        <p class="mb-2 text-gray-700">Hemos recibido tu denuncia correctamente.</p>

        <?php if (isset($_GET['id'])): ?>
            <p class="mb-4 text-sm text-gray-600">
                Tu número de denuncia es: <strong class="text-[#942934]">#<?= htmlspecialchars($_GET['id']) ?></strong><br>
                Guarda este número para consultar el estado de tu denuncia más adelante.
            </p>
        <?php endif; ?>

        <div class="flex flex-col gap-3 mt-4">
            <a href="index.php" class="bg-[#942934] hover:bg-[#d32f57] text-white px-6 py-3 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
                📝 Enviar otra denuncia
            </a>
            <a href="ver_estado.php" class="bg-[#a08e43] hover:bg-[#685f2f] text-white px-6 py-3 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
                🔍 Consultar estado
            </a>
        </div>
    </div>
</body>
</html>
