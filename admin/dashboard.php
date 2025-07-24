<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require_once "../db/conexion.php";

// Consultar todas las denuncias
$sql = "SELECT * FROM denuncias ORDER BY fecha_creacion DESC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Denuncias</title>
    <link href="../assets/css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-3xl font-bold mb-6 text-center">📋 Panel de Denuncias</h1>

        <div class="mb-4 text-right">
            <a href="cerrar_sesion.php" class="text-blue-600 hover:underline">Cerrar sesión</a>
        </div>

        <table class="w-full table-auto border">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Nombre</th>
                    <th class="px-4 py-2">Correo</th>
                    <th class="px-4 py-2">Estado</th>
                    <th class="px-4 py-2">Fecha</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $resultado->fetch_assoc()): ?>
                    <tr class="border-t">
                        <td class="px-4 py-2"><?php echo $fila['id']; ?></td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($fila['nombre']); ?></td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($fila['correo']); ?></td>
                        <td class="px-4 py-2"><?php echo ucfirst($fila['estado']); ?></td>
                        <td class="px-4 py-2"><?php echo $fila['fecha_creacion']; ?></td>
                        <td class="px-4 py-2">
                            <form action="cambiar_estado.php" method="POST" class="flex items-center space-x-2">
                                <input type="hidden" name="id" value="<?= $fila['id'] ?>">
                                <select name="nuevo_estado" class="border rounded px-2 py-1 text-sm">
                                    <option value="pendiente" <?= $fila['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="en_proceso" <?= $fila['estado'] == 'en_proceso' ? 'selected' : '' ?>>En proceso</option>
                                    <option value="resuelto" <?= $fila['estado'] == 'resuelto' ? 'selected' : '' ?>>Finalizada</option>
                                </select>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-2 py-1 rounded">
                                    Cambiar
                                </button>
                                <a href="ver_denuncia.php?id=<?= $fila['id'] ?>" class="text-blue-600 hover:underline text-sm ml-2">Ver</a>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
