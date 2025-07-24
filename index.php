<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Denuncia anónima o personal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="assets/css/output.css" rel="stylesheet">
</head>
<body class="bg-[#f8f9fb] flex justify-center items-center min-h-screen p-4">

  <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-xl space-y-6 border border-gray-300">

    <h1 class="text-2xl font-bold text-center text-[#942934]">Formulario de Denuncia</h1>

    <?php if (isset($_GET['error'])): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm mb-4 animate-pulse">
        <?php echo htmlspecialchars($_GET['error']); ?>
      </div>
      <script>
        window.scrollTo({ top: 0, behavior: 'smooth' });
      </script>
    <?php endif; ?>

    <form action="guardar_denuncia.php" method="POST" enctype="multipart/form-data" class="space-y-4">

      <input type="text" name="nombre" placeholder="Tu nombre completo" required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#d32f57] invalid:border-red-500" />

      <input type="text" name="cedula" placeholder="Tu cédula" required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#d32f57] invalid:border-red-500" />

      <input type="email" name="correo" placeholder="Tu correo electrónico" required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#d32f57] invalid:border-red-500" />

      <textarea name="mensaje" rows="5" placeholder="Escribe aquí tu denuncia..." required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#d32f57] invalid:border-red-500"></textarea>

      <div>
        <label class="block text-sm font-semibold text-[#685f2f] mb-1">Subir fotos (puedes subir varias):</label>
        <label class="flex items-center justify-center w-full px-4 py-3 bg-[#e96510] text-white rounded-xl shadow hover:bg-[#f39322] cursor-pointer transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
          📷 Elegir fotos
          <input type="file" name="fotos[]" multiple accept="image/*" class="hidden" onchange="mostrarArchivos(this, 'fotosSeleccionadas')" />
        </label>

        <div class="mt-2">
          <div id="loaderFotos" class="hidden text-sm text-[#e96510] flex items-center gap-2">
            <svg class="animate-spin h-4 w-4 text-[#e96510]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Cargando fotos...
          </div>
          <ul id="fotosSeleccionadas" class="text-sm text-gray-700 list-disc list-inside"></ul>
        </div>
      </div>

      <div>
        <label class="block text-sm font-semibold text-[#685f2f] mb-1">Subir audios:</label>
        <label class="flex items-center justify-center w-full px-4 py-3 bg-[#685f2f] text-white rounded-xl shadow hover:bg-[#a08e43] cursor-pointer transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
          🎤 Elegir audios
          <input type="file" name="audios[]" multiple accept="audio/*" class="hidden" onchange="mostrarArchivos(this, 'audiosSeleccionados')" />
        </label>

        <div class="mt-2">
          <div id="loaderAudios" class="hidden text-sm text-[#a08e43] flex items-center gap-2">
            <svg class="animate-spin h-4 w-4 text-[#a08e43]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Cargando audios...
          </div>
          <ul id="audiosSeleccionados" class="text-sm text-gray-700 list-disc list-inside"></ul>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-4 pt-4">
        <button type="submit"
          class="w-full bg-[#942934] hover:bg-[#d32f57] text-white font-semibold px-6 py-3 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
          📝 Enviar denuncia
        </button>

        <a href="ver_estado.php"
          class="w-full bg-[#a08e43] hover:bg-[#685f2f] text-white font-semibold px-6 py-3 rounded-xl text-center transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
          🔎 Consultar estado
        </a>
      </div>

      <p class="text-xs text-center text-gray-500 pt-2">
        La información enviada es confidencial y será tratada de manera segura según nuestra política de protección de datos.
      </p>

    </form>
  </div>

  <script src="assets/js/formulario.js"></script>
</body>
</html>
