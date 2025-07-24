<?php
require_once "db/conexion.php";

// Paso 1: Verificar si hay un ID por GET
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    echo "<form method='GET' class='max-w-md mx-auto mt-10'>
        <label class='block mb-2 font-semibold'>Ingresa el ID de tu denuncia:</label>
        <input type='number' name='id' class='border px-3 py-2 rounded w-full mb-4' required />
        <button type='submit' class='bg-blue-600 text-white px-4 py-2 rounded'>Consultar</button>
    </form>";
    exit;
}

// Paso 2: Buscar la denuncia
$sql = "SELECT * FROM denuncias WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "<p class='text-center text-red-600 mt-10'>❌ No se encontró ninguna denuncia con ese ID.</p>";
    exit;
}

$denuncia = $resultado->fetch_assoc();


// Mostrar los datos de la denuncia
echo "<div class='bg-white p-6 rounded shadow mt-6 max-w-2xl mx-auto'>";
echo "<h2 class='text-xl font-bold mb-4'>📋 Detalles de tu denuncia #{$denuncia['id']}</h2>";
echo "<p><strong>Nombre:</strong> " . htmlspecialchars($denuncia['nombre']) . "</p>";
echo "<p><strong>Correo:</strong> " . htmlspecialchars($denuncia['correo']) . "</p>";
echo "<p><strong>Estado:</strong> " . ucfirst($denuncia['estado']) . "</p>";
echo "<p><strong>Mensaje:</strong></p>";
echo "<div class='bg-gray-50 border p-4 rounded mb-4'>" . nl2br(htmlspecialchars($denuncia['mensaje'])) . "</div>";
echo "</div>";

$sqlArchivos = "SELECT * FROM archivos WHERE id_denuncia = ?";
$stmtArchivos = $conn->prepare($sqlArchivos);
$stmtArchivos->bind_param("i", $denuncia['id']);
$stmtArchivos->execute();
$archivos = $stmtArchivos->get_result();

echo "<div class='bg-white p-6 rounded shadow mt-6 max-w-2xl mx-auto'>";
echo "<h2 class='text-xl font-semibold mb-4'>📎 Archivos adjuntos</h2>";

$hayArchivos = false;

while ($archivo = $archivos->fetch_assoc()) {
    $hayArchivos = true;
    $ruta = "" . $archivo['ruta_archivo'];

    if ($archivo['tipo'] === 'foto') {
        echo "<img src='$ruta' alt='Foto' class='w-full max-w-sm mb-4 border rounded'>";
    } elseif ($archivo['tipo'] === 'audio') {
        echo "<audio controls class='w-full mb-4'>
                <source src='$ruta' type='audio/mpeg'>
                Tu navegador no soporta la reproducción de audio.
              </audio>";
    }
}

if (!$hayArchivos) {
    echo "<p class='text-gray-600'>No se subieron archivos.</p>";
}

echo "</div>";
