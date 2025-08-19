<?php
require_once "db/conexion.php";
require_once "correo/enviar_correo.php";
session_start();

// Evitar reenvío/ cache de formularios
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Ventana de gracia en minutos
$GRACE_MINUTES = 20;

// Helpers (compatibles con PHP viejo)
function verificacion_vigente($id) {
  return isset($_SESSION['verificado'], $_SESSION['verificado_id'], $_SESSION['verificado_until'])
    && $_SESSION['verificado'] === true
    && $_SESSION['verificado_id'] == $id
    && $_SESSION['verificado_until'] > time();
}
function renovar_gracia($minutos) {
  $_SESSION['verificado_until'] = time() + ($minutos * 60);
}


if (isset($_POST['reiniciar'])) {
  session_unset(); // Limpia TODAS las variables de sesión
  session_destroy(); // Cierra completamente la sesión
  session_start(); // Inicia nueva
}

function generarCodigoVerificacion($longitud = 6) {
  return str_pad(random_int(0, 999999), $longitud, '0', STR_PAD_LEFT);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Consultar denuncia</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="assets/css/output.css" rel="stylesheet">
</head>
<body class="bg-[#f8f9fb] min-h-screen p-4 flex items-center justify-center flex-col">

<?php if (isset($_GET['mensaje'])): ?>
  <div id="toast"
       class="animate-toast-in bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-xl mt-4 text-sm text-center shadow max-w-xl mx-auto fixed bottom-6 left-1/2 transform -translate-x-1/2 z-50">
    ✅ <?= htmlspecialchars($_GET['mensaje']) ?>
  </div>
<?php endif; ?>

<?php
// Paso 1: Formulario inicial
if (!isset($_POST['verificar']) && !isset($_POST['codigo']) && !isset($_SESSION['esperando_codigo']) && !isset($_SESSION['verificado'])): ?>
  <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md border border-gray-300 space-y-4">
    <h2 class="text-xl font-bold text-center text-[#942934]">🔐 Consultar estado de denuncia</h2>
    <form method="POST" class="space-y-4">
      <input type="number" name="id" placeholder="ID del presunto caso" required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#d32f57] invalid:border-red-500" />
      <input type="email" name="correo" placeholder="Correo electrónico registrado" required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#d32f57] invalid:border-red-500" />
      <button type="submit" name="verificar"
        class="w-full bg-[#685f2f] hover:bg-[#a08e43] text-white font-semibold px-6 py-3 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
        Enviar código de verificación
      </button>
    </form>
  </div>
<?php exit; endif; ?>

<?php
// Paso 2: Enviar código
if (isset($_POST['verificar'])) {
  $id = $_POST['id'];
  $correo = $_POST['correo'];

  $stmt = $conn->prepare("SELECT * FROM denuncias WHERE id = ? AND correo = ?");
  $stmt->bind_param("is", $id, $correo);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res->num_rows === 0) {
    echo "<div class='bg-white p-6 rounded-xl shadow border border-red-300 text-red-700 space-y-4 max-w-md w-full text-center'>
            <p class='text-lg'>❌ No se encontró ninguna denuncia con esos datos.</p>
            <a href='ver_estado.php' class='inline-block mt-4 bg-[#942934] hover:bg-[#d32f57] text-white font-semibold px-6 py-2 rounded-xl transition-all duration-300'>Intentar de nuevo</a>
          </div>";
    exit;
  }

  $codigo = generarCodigoVerificacion();
  $_SESSION['codigo_verificacion'] = $codigo;
  $_SESSION['id_denuncia'] = $id;
  $_SESSION['correo_denunciante'] = $correo;
  $_SESSION['esperando_codigo'] = true;

  $denuncia = $res->fetch_assoc();
  $correoDenuncia = new CorreoDenuncia();
  $asunto = "Código de verificación - Consulta de denuncia #$id";
  $mensaje = "
    <p>Hola,</p>
    <p>Tu código para consultar el estado de tu denuncia es:</p>
    <h2 style='font-size:28px;'>$codigo</h2>
    <p>Este código es válido por unos minutos.</p>
  ";
  $correoDenuncia->sendConfirmacion($denuncia['nombre'], $correo, $id, $asunto, $mensaje);
}

<?php
// Paso 3: Mostrar formulario para ingresar código (solo si estamos en paso=codigo)
if (
  isset($_GET['paso']) && $_GET['paso'] === 'codigo' &&
  isset($_SESSION['esperando_codigo'])
) {
  $hayError = (isset($_GET['error']) && $_GET['error'] === '1');
  ?>
  <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md border <?php echo $hayError ? 'border-red-300' : 'border-gray-300'; ?> space-y-4">
    <h2 class="text-xl font-bold text-center <?php echo $hayError ? 'text-red-600' : 'text-[#942934]'; ?>">
      <?php echo $hayError ? '❌ Código incorrecto' : '📩 Verificación de código'; ?>
    </h2>

    <?php if ($hayError): ?>
      <p class="text-sm text-center text-red-600">El código no es válido. Intenta nuevamente.</p>
    <?php endif; ?>

    <form method="POST" class="space-y-4" autocomplete="off">
      <input
        type="text"
        name="codigo"
        maxlength="6"
        inputmode="numeric"
        pattern="\d{6}"
        placeholder="Código recibido en tu correo"
        required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#d32f57]"
        autocomplete="one-time-code"
      />
      <button type="submit"
        class="w-full bg-[#942934] hover:bg-[#d32f57] text-white font-semibold px-6 py-3 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
        Ver comunicación
      </button>
    </form>
  </div>
  <?php
  exit;
}
?>

// Paso 4: Validar código (PRG)
if (isset($_POST['codigo'])) {
  $idActual = isset($_SESSION['id_denuncia']) ? $_SESSION['id_denuncia'] : null;
  $codigoOk = (isset($_SESSION['codigo_verificacion']) && $_POST['codigo'] === $_SESSION['codigo_verificacion']);

  if (!$codigoOk || !$idActual) {
    // Código inválido -> redirigimos a GET (PRG) con error
    header('Location: ver_estado.php?paso=codigo&id=' . urlencode($idActual ? $idActual : 0) . '&error=1');
    exit;
  }

  // Código correcto -> marcar verificado con ventana de gracia
  $_SESSION['verificado']       = true;
  $_SESSION['verificado_id']    = $idActual;
  $_SESSION['verificado_until'] = time() + ($GRACE_MINUTES * 60);

  // Limpia datos de verificación ya usados
  unset($_SESSION['codigo_verificacion'], $_SESSION['esperando_codigo']);

  // PRG a la vista por GET
  header('Location: ver_estado.php?id=' . urlencode($idActual));
  exit;
}

// Paso 5: Verificación obligatoria (con ventana de gracia)
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id) {
  // aquí va tu formulario inicial de ID + correo y un "exit;"
  // ...
  exit;
}

if (verificacion_vigente($id)) {
  // cada visita renueva la gracia
  renovar_gracia($GRACE_MINUTES);
} else {
  // no verificado o expirado -> ir a pedir código
  header('Location: ver_estado.php?paso=codigo&id=' . urlencode($id));
  exit;
}


// Paso 6: Mostrar denuncia
$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM denuncias WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0): ?>
  <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md border border-red-300 text-center text-red-700 space-y-4">
    <h2 class="text-xl font-bold">❌ Comunicación no encontrada</h2>
    <p>No se encontró ninguna Comunicación con ese ID.</p>
    <a href="ver_estado.php"
      class="inline-block bg-[#942934] hover:bg-[#d32f57] text-white font-semibold px-6 py-2 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
      Intentar de nuevo
    </a>
  </div>
<?php exit; endif;

$denuncia = $resultado->fetch_assoc(); ?>

<div class="w-full max-w-5xl space-y-6">

  <!-- Detalles -->
  <div class="bg-white p-8 rounded-2xl shadow-2xl border border-gray-300 space-y-4">
    <h1 class="text-2xl font-bold text-[#685f2f]">📄 Detalles de tu Comunicación #<?= htmlspecialchars($denuncia['id']) ?></h1>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
      <p><strong>Nombre:</strong> <?= htmlspecialchars($denuncia['nombre']) ?></p>
      <p><strong>Correo:</strong> <?= htmlspecialchars($denuncia['correo']) ?></p>
      <p><strong>Estado:</strong> <?= ucfirst($denuncia['estado']) ?></p>
    </div>
    <div>
      <h2 class="text-lg font-semibold text-[#942934] mb-2">📝 Mensaje:</h2>
      <div class="border border-gray-200 p-4 bg-gray-50 rounded-xl whitespace-pre-line shadow"><?= htmlspecialchars($denuncia['mensaje']) ?></div>
    </div>
    <?php if (!empty($denuncia['firma'])): ?>
      <div>
        <p class="mt-4 font-semibold">Firma del colaborador:</p>
        <img src="<?= htmlspecialchars($denuncia['firma']) ?>" alt="Firma" class="w-60 border border-gray-200 mt-2 rounded-2xl shadow">
      </div>
    <?php endif; ?>
  </div>

  <!-- Archivos -->
  <div class="bg-white p-8 rounded-2xl shadow-2xl border border-gray-300">
    <h2 class="text-xl font-bold mb-4 text-[#942934]">📎 Archivos adjuntos</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
    <?php
    $sqlArchivos = "SELECT * FROM archivos WHERE id_denuncia = ?";
    $stmtArchivos = $conn->prepare($sqlArchivos);
    $stmtArchivos->bind_param("i", $denuncia['id']);
    $stmtArchivos->execute();
    $archivos = $stmtArchivos->get_result();

    $hayArchivos = false;
    while ($archivo = $archivos->fetch_assoc()):
      $hayArchivos = true;
      $ruta = htmlspecialchars($archivo['ruta_archivo']);
    ?>
      <?php if ($archivo['tipo'] === 'foto'): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-md p-2">
          <img src="<?= $ruta ?>" alt="Foto" class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition" onclick="ampliarImagen(this.src)">
        </div>
      <?php elseif ($archivo['tipo'] === 'audio'): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-md p-4 flex flex-col items-center justify-center">
          <p class="text-sm font-medium text-[#942934] mb-2">🎧 Evidencia de audio</p>
          <audio controls class="w-full">
            <source src="<?= htmlspecialchars($archivo['ruta_archivo']) ?>">
            Tu navegador no soporta la reproducción de audio.
          </audio>
        </div>
      <?php endif; ?>
    <?php endwhile; ?>
    <?php if (!$hayArchivos): ?>
      <p class="text-gray-600">No se subieron archivos.</p>
    <?php endif; ?>
    </div>
  </div>

  <!-- Conversación intercalada con recarga -->
  <div class="bg-white p-6 rounded-2xl shadow border border-gray-300 space-y-4">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-bold text-[#a08e43]">💬 Conversación</h2>
      <button onclick="recargarMensajes()"
        class="bg-[#685f2f] hover:bg-[#a08e43] text-white text-sm font-semibold px-4 py-2 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
        🔄 Revisar nuevos mensajes
      </button>
    </div>

    <div id="contenedor-conversacion">
      <p class="text-gray-500 text-sm">⏳ Cargando conversación...</p>
    </div>
  </div>


  <!-- Enviar nueva respuesta -->
  <?php if ($denuncia['estado'] === 'en_proceso'): ?>
    <div class="bg-white p-6 rounded-2xl shadow border border-gray-300">
      <h2 class="text-lg font-bold mb-4 text-[#685f2f]">📨 Enviar una nueva respuesta</h2>
      <form action="responder_denunciante.php" method="POST" class="space-y-4">
        <input type="hidden" name="id_denuncia" value="<?= $denuncia['id'] ?>">
        <textarea name="mensaje" rows="5" required
          class="w-full border border-gray-300 rounded-lg p-3 placeholder:text-gray-500 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#d32f57] invalid:border-red-500"
          placeholder="Escribe tu respuesta..."></textarea>
        <button type="submit"
          class="bg-[#942934] hover:bg-[#d32f57] text-white font-semibold px-6 py-2 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
          Enviar respuesta
        </button>
      </form>
    </div>
  <?php endif; ?>
</div>

<!-- Modal imagen -->
<div id="modalImagen" class="fixed inset-0 bg-black/80 hidden justify-center items-center z-50">
  <img id="imagenGrande" class="max-w-4xl max-h-[90vh] rounded-xl shadow-xl border-4 border-white" />
</div>

  <script>
    let ultimaConversacion = ""; // contenido anterior

    function ampliarImagen(src) {
      const modal = document.getElementById('modalImagen');
      const img = document.getElementById('imagenGrande');
      img.src = src;
      modal.classList.remove('hidden');
    }

    document.getElementById('modalImagen').addEventListener('click', () => {
      document.getElementById('modalImagen').classList.add('hidden');
    });

    const toast = document.getElementById('toast');
    if (toast) {
      setTimeout(() => {
        toast.classList.remove('animate-toast-in');
        toast.classList.add('animate-toast-out');
        setTimeout(() => toast.remove(), 500);
      }, 4000);
    }

    function recargarMensajes() {
      const contenedor = document.getElementById("contenedor-conversacion");
      const sonido = document.getElementById("sonido-pop");
      const id = <?= json_encode($_GET['id']) ?>;

      fetch(`cargar_conversacion.php?id=${id}`)
        .then(res => {
          if (!res.ok) throw new Error("Error al cargar");
          return res.text();
        })
        .then(html => {
          if (html.trim() !== ultimaConversacion.trim()) {
            contenedor.innerHTML = html;
            sonido.play().catch(() => {}); // reproducir el sonido si hay cambios
            ultimaConversacion = html;
          }
        })
        .catch(() => {
          contenedor.innerHTML = "<p class='text-red-500 text-sm'>❌ Error al actualizar mensajes.</p>";
        });
    }

    // Cargar al entrar + cada 20 segundos
    document.addEventListener("DOMContentLoaded", () => {
      recargarMensajes();
      setInterval(recargarMensajes, 20000);
    });
  </script>

  <!-- Sonido pop -->
  <audio id="sonido-pop" src="assets/sounds/bubblepop-254773.mp3" preload="auto"></audio>
  <div class="mt-10 text-center">
    <a href="cerrar_sesion_estado.php"
       class="inline-block bg-[#942934] hover:bg-[#d32f57] text-white font-semibold px-6 py-3 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
      🔒 Cerrar sesión y volver al inicio
    </a>
  </div>
</body>
</html>
