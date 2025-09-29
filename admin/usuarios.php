<?php
// admin/usuarios.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once "../db/conexion.php";

// ✅ solo deja entrar si eres admin (ajústalo si ya tienes helper)
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$mensaje = "";

// Crear usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear') {
    $usuario = trim($_POST['usuario']);
    $correo = trim($_POST['correo']);
    $pass = $_POST['contrasena'];
    $rol = $_POST['rol'];                 // 'agente' o 'admin'
    $activo = isset($_POST['activo']) ? 1 : 0;

    if ($usuario === '' || $correo === '' || $pass === '') {
        $mensaje = "⚠️ Usuario, correo y contraseña son obligatorios.";
    } else {
        // Guardamos con password_hash (tu login ya soporta SHA-256 y password_hash)
        $hash = password_hash($pass, PASSWORD_BCRYPT);

        $ins = $conn->prepare("INSERT INTO usuarios (usuario, correo, contrasena, rol, activo, fecha_creacion)
                           VALUES (?,?,?,?,?, NOW())");
        $ins->bind_param("ssssi", $usuario, $correo, $hash, $rol, $activo);
        if ($ins->execute()) {
            $mensaje = "✅ Usuario creado.";
        } else {
            $mensaje = "❌ Error: " . $conn->error;
        }
    }
}

// Eliminar usuario (no puedes borrarte a ti mismo)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $id = (int) $_POST['id'];
    if ($id === (int) $_SESSION['usuario']['id']) {
        $mensaje = "⚠️ No puedes eliminar tu propia cuenta.";
    } else {
        $del = $conn->prepare("DELETE FROM usuarios WHERE id=?");
        $del->bind_param("i", $id);
        if ($del->execute())
            $mensaje = "🗑️ Usuario eliminado.";
        else
            $mensaje = "❌ Error: " . $conn->error;
    }
}

$users = $conn->query("SELECT id, usuario, correo, rol, activo, fecha_creacion FROM usuarios ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Usuarios</title>
    <link href="../assets/css/output.css" rel="stylesheet">
</head>

<body class="bg-[#f8f9fb] min-h-screen p-6">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-2xl shadow border">

        <h1 class="text-2xl font-bold text-[#942934] mb-4">Gestión de usuarios</h1>

        <?php if ($mensaje): ?>
            <div
                class="mb-4 p-3 rounded border <?php echo strpos($mensaje, '✅') !== false || strpos($mensaje, '🗑️') !== false ? 'bg-green-50 border-green-300 text-green-800' : 'bg-red-50 border-red-300 text-red-700'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <!-- Crear -->
        <form method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-6">
            <input type="hidden" name="accion" value="crear">
            <input name="usuario" required placeholder="Usuario" class="border rounded px-3 py-2 md:col-span-1">
            <input name="correo" type="email" required placeholder="Correo"
                class="border rounded px-3 py-2 md:col-span-2">
            <input name="contrasena" type="password" required placeholder="Contraseña"
                class="border rounded px-3 py-2 md:col-span-2">
            <select name="rol" class="border rounded px-3 py-2">
                <option value="agente">Agente</option>
                <option value="admin">Admin</option>
            </select>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="activo" checked> <span class="text-sm">Activo</span>
            </label>
            <div class="md:col-span-6">
                <button class="bg-[#942934] text-white rounded px-4 py-2">Crear usuario</button>
            </div>
        </form>

        <!-- Listado -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b">
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Activo</th>
                        <th>Creado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($u = $users->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td><?php echo (int) $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['usuario']); ?></td>
                            <td><?php echo htmlspecialchars($u['correo']); ?></td>
                            <td><?php echo $u['rol']; ?></td>
                            <td><?php echo $u['activo'] ? 'Sí' : 'No'; ?></td>
                            <td><?php echo $u['fecha_creacion']; ?></td>
                            <td>
                                <?php if ((int) $u['id'] !== (int) $_SESSION['usuario']['id']): ?>
                                    <form method="POST" onsubmit="return confirm('¿Eliminar este usuario?')" class="inline">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?php echo (int) $u['id']; ?>">
                                        <button class="text-red-600 hover:underline">Eliminar</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-gray-400">Tú</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <a href="dashboard.php" class="text-[#942934] hover:underline">← Volver</a>
        </div>
    </div>
</body>

</html>