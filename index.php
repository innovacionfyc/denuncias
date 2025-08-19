<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>QUEJAS DE PRESUNTO ACOSO LABORAL</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="assets/css/output.css" rel="stylesheet">
</head>
<body class="bg-[#f8f9fb] flex justify-center items-center min-h-screen p-4">

  <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-5xl space-y-6 border border-gray-300">
    <h1 class="text-2xl font-bold text-center text-[#942934]">QUEJAS DE PRESUNTO ACOSO LABORAL</h1>

    <?php if (isset($_GET['error'])): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm mb-4 animate-pulse">
        <?php echo htmlspecialchars($_GET['error']); ?>
      </div>
      <script>window.scrollTo({ top: 0, behavior: 'smooth' });</script>
    <?php endif; ?>

    <form action="guardar_denuncia.php" method="POST" enctype="multipart/form-data" class="space-y-4" onsubmit="capturarFirma()">

      <input type="text" name="nombre" placeholder="Nombre del Colaborador" required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:ring-2 focus:ring-[#d32f57] invalid:border-red-500"/>

      <input type="text" name="cedula" placeholder="Documento de Identidad" required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:ring-2 focus:ring-[#d32f57] invalid:border-red-500"/>

      <select name="proceso" required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-white text-gray-700 font-medium transition-all duration-300 focus:ring-2 focus:ring-[#d32f57]">
        <option value="" disabled selected>Selecciona un proceso</option>
        <option value="Proceso Comercial">Proceso Comercial</option>
        <option value="Proceso Logístico">Proceso Logístico</option>
        <option value="Proceso de Innovación y Desarrollo">Proceso de Innovación y Desarrollo</option>
        <option value="Proceso RRHH">Proceso RRHH</option>
        <option value="Proceso Contabilidad">Proceso Contabilidad</option>
        <option value="Proceso Comunicaciones y Marketing">Proceso Comunicaciones y Marketing</option>
        <option value="Proceso de Sistema Integrado de Gestión">Proceso de Sistema Integrado de Gestión</option>
      </select>

      <input type="text" name="cargo" placeholder="Cargo" required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:ring-2 focus:ring-[#d32f57] invalid:border-red-500"/>

      <input type="email" name="correo" placeholder="Correo Electrónico" required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:ring-2 focus:ring-[#d32f57] invalid:border-red-500"/>

      <p class="text-sm text-gray-600 font-semibold pt-4">Instrucciones de diligenciamiento</p>
      <p class="text-sm text-gray-600">Para presentar queja diligencia el numeral 1.</p>

      <label class="block text-sm font-semibold text-[#685f2f] pt-4">1. Hechos que Constituyen la Queja</label>
      <textarea name="mensaje" rows="5" placeholder="Describa todas las situaciones: quién/quienes, cuándo, cómo, dónde, etc." required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:ring-2 focus:ring-[#d32f57] invalid:border-red-500"></textarea>

      <p class="text-xs text-gray-500">
        (El comité podrá solicitarle posteriormente la ampliación de la información ofrecida)
      </p>

      <p class="text-sm text-gray-600 font-medium">¿Cuenta usted con alguna prueba? ¿Cuáles? Relaciónelas y adjúntelas:</p>

      <!-- Subir fotos -->
      <div>
        <label class="block text-sm font-semibold text-[#685f2f] mb-1">Subir fotos:</label>
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

      <!-- Subir audios -->
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

      <!-- Firma -->
      <div>
        <label class="block text-sm font-semibold text-[#942934] mb-2">Firma del Colaborador:</label>
        <canvas id="firmaCanvas" class="border border-gray-400 rounded-lg w-full h-40 bg-gray-50 cursor-crosshair"></canvas>
        <input type="hidden" name="firma" id="firmaInput" />
        <button type="button" onclick="limpiarFirma()" class="mt-2 text-sm text-[#d32f57] underline">🧹 Limpiar firma</button>
      </div>

      <!-- Botones -->
      <div class="flex flex-col sm:flex-row gap-4 pt-4">
        <!-- Botón de enviar comunicación (dentro del form) -->
        <button type="submit"
          class="w-full bg-[#942934] hover:bg-[#d32f57] text-white font-semibold px-6 py-3 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
          📝 Enviar Comunicación
        </button>
      </div>
      </form> <!-- ⛔️ Cerramos el form aquí -->

      <!-- Botón de consultar estado (fuera del form) -->
      <form method="POST" action="ver_estado.php" class="w-full mt-4">
        <input type="hidden" name="reiniciar" value="1">
        <button type="submit"
          class="w-full bg-[#a08e43] hover:bg-[#685f2f] text-white font-semibold px-6 py-3 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
          🔎 Consultar estado
        </button>
      </form>

      <p class="text-xs text-center text-gray-500 pt-2">
        La información enviada es confidencial y será tratada de manera segura según nuestra política de protección de datos.
      </p>

    </form>
  </div>

  <script src="assets/js/formulario.js"></script>

  <!-- Firma Canvas Script -->
  <script>
    const canvas = document.getElementById("firmaCanvas");
    const ctx = canvas.getContext("2d");
    let dibujando = false;

    // ⚙️ Ajusta canvas interno al tamaño en pantalla y DPR
    function ajustarCanvas() {
      const rect = canvas.getBoundingClientRect();
      const dpr = window.devicePixelRatio || 1;

      // Tamaño real de pixeles del canvas
      canvas.width  = Math.round(rect.width  * dpr);
      canvas.height = Math.round(rect.height * dpr);

      // Escala el contexto para que 1 unidad = 1px CSS
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

      // Estilos del trazo
      ctx.lineWidth = 2;
      ctx.lineCap = "round";
      ctx.strokeStyle = "#000";
    }

    // 🧭 Coordenadas precisas (soporta zoom y DPR)
    function getPosicion(event) {
      const rect = canvas.getBoundingClientRect();
      const dpr = window.devicePixelRatio || 1;
      const tipo = event.type && event.type.includes('touch') ? event.touches[0] : event;

      // Escalas para convertir de px de pantalla a coords del canvas (post-transform)
      const scaleX = (canvas.width  / dpr) / rect.width;
      const scaleY = (canvas.height / dpr) / rect.height;

      return {
        x: (tipo.clientX - rect.left) * scaleX,
        y: (tipo.clientY - rect.top)  * scaleY
      };
    }

    function empezarDibujo(event) {
      dibujando = true;
      const pos = getPosicion(event);
      ctx.beginPath();
      ctx.moveTo(pos.x, pos.y);
    }

    function terminarDibujo() {
      dibujando = false;
      ctx.beginPath(); // listo para el siguiente trazo
    }

    function dibujar(event) {
      if (!dibujando) return;
      event.preventDefault(); // evita scroll en touch
      const pos = getPosicion(event);
      ctx.lineTo(pos.x, pos.y);
      ctx.stroke();
    }

    function limpiarFirma() {
      // Limpia usando el tamaño actual del canvas
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.beginPath();
    }

    function capturarFirma() {
      // Exporta como dataURL del tamaño correcto
      document.getElementById("firmaInput").value = canvas.toDataURL();
    }

    // Listeners mouse
    canvas.addEventListener("mousedown", empezarDibujo);
    canvas.addEventListener("mouseup", terminarDibujo);
    canvas.addEventListener("mouseleave", terminarDibujo);
    canvas.addEventListener("mousemove", dibujar);

    // Listeners táctil (con passive:false para poder preventDefault)
    canvas.addEventListener("touchstart", (e) => { e.preventDefault(); empezarDibujo(e); }, { passive: false });
    canvas.addEventListener("touchend",   (e) => { e.preventDefault(); terminarDibujo(e); }, { passive: false });
    canvas.addEventListener("touchcancel",(e) => { e.preventDefault(); terminarDibujo(e); }, { passive: false });
    canvas.addEventListener("touchmove",  (e) => { e.preventDefault(); dibujar(e); },       { passive: false });

    // Reajusta al cargar y al redimensionar
    window.addEventListener("resize", ajustarCanvas);
    // Si usas modales o tabs que cambian tamaño, puedes llamar ajustarCanvas() al abrirlos.
    ajustarCanvas();

    // Exponer funciones si las llamas desde el HTML
    window.limpiarFirma = limpiarFirma;
    window.capturarFirma = capturarFirma;
  </script>

</body>
</html>
