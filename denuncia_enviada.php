<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Denuncia enviada</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="assets/css/output.css" rel="stylesheet">
</head>
<body class="bg-[#f8f9fb] min-h-screen flex items-center justify-center p-4">

  <div class="bg-white shadow-2xl rounded-2xl p-8 max-w-lg w-full border border-gray-300 text-center space-y-4">

    <h1 class="text-2xl font-bold text-[#685f2f]">✅ ¡Denuncia enviada con éxito!</h1>

    <p class="text-gray-700 text-sm">
      Hemos recibido tu denuncia y te hemos enviado una confirmación por correo electrónico.
    </p>

    <?php if (isset($_GET['id'])): ?>
      <p class="text-sm text-gray-700">
        Tu número de seguimiento es: <strong class="text-[#942934]"><?php echo htmlspecialchars($_GET['id']); ?></strong>
      </p>
      <p class="text-xs text-gray-500">
        Guarda este número para consultar el estado de tu denuncia.
      </p>
    <?php endif; ?>

    <div class="pt-4">
      <a href="index.php"
         class="inline-block bg-[#685f2f] hover:bg-[#a08e43] text-white font-semibold px-6 py-3 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
        📝 Enviar otra denuncia
      </a>
    </div>

  </div>

</body>
</html>
