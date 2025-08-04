<?php
require_once "db/conexion.php";
session_start();

if (!isset($_SESSION['verificado']) || $_SESSION['verificado'] !== true) {
  http_response_code(403);
  echo "No autorizado.";
  exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
  echo "<p class='text-gray-600'>No se proporcionó el ID.</p>";
  exit;
}

// Traer respuestas del comité
$respuestas = [];

$sqlComite = "SELECT 'comite' AS origen, mensaje, fecha_respuesta AS fecha FROM respuestas WHERE id_denuncia = ?";
$stmt1 = $conn->prepare($sqlComite);
$stmt1->bind_param("i", $id);
$stmt1->execute();
$res1 = $stmt1->get_result();
while ($r = $res1->fetch_assoc()) {
  $respuestas[] = $r;
}

// Traer respuestas del denunciante
$sqlDenunciante = "SELECT 'denunciante' AS origen, mensaje, fecha FROM respuestas_denunciante WHERE id_denuncia = ?";
$stmt2 = $conn->prepare($sqlDenunciante);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($r = $res2->fetch_assoc()) {
  $respuestas[] = $r;
}

// Ordenar por fecha
usort($respuestas, function ($a, $b) {
  return strtotime($a['fecha']) <=> strtotime($b['fecha']);
});

if (count($respuestas) > 0):
  foreach ($respuestas as $r):
    $esComite = $r['origen'] === 'comite';
    $color = $esComite ? 'bg-green-50 ml-0' : 'bg-gray-50 ml-auto';
    $fecha = date('Y-m-d H:i', strtotime($r['fecha']));
?>
    <div class="border border-gray-300 rounded p-4 w-fit max-w-lg <?= $color ?>">
      <?= nl2br(htmlspecialchars($r['mensaje'])) ?>
      <p class="text-sm text-gray-500 mt-2">📅 <?= $fecha ?></p>
    </div>
<?php
  endforeach;
else:
  echo "<p class='text-gray-600'>Aún no hay conversación registrada.</p>";
endif;
?>
