<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../db/conexion.php";
require_once "../correo/enviar_correo.php";

$error = "";

// Si ya está logueado, llévalo al dashboard
if (isset($_SESSION['usuario'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ident = trim($_POST['usuario']);     // usamos tu input "usuario" (puede ser usuario o correo)
    $contrasena = $_POST['contrasena'];

    // Traer por usuario O correo
    $sql = "SELECT id, usuario, correo, contrasena, rol, activo
            FROM usuarios
            WHERE (usuario = ? OR correo = ?) AND activo = 1
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $ident, $ident);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();

        // Compatibilidad: acepta tu SHA-256 y también password_hash por si luego migras
        $hash_db = $fila['contrasena'];
        $okPass = false;
        if (strpos($hash_db, '$2y$') === 0 || strpos($hash_db, '$argon') === 0) {
            // bcrypt/argon
            $okPass = password_verify($contrasena, $hash_db);
        } else {
            // tu esquema actual SHA-256
            $okPass = (hash("sha256", $contrasena) === $hash_db);
        }

        if ($okPass) {
            // ADMIN entra directo
            if ($fila['rol'] === 'admin') {
                $_SESSION['usuario'] = [
                    'id' => (int) $fila['id'],
                    'nombre' => $fila['usuario'],
                    'correo' => $fila['correo'],
                    'rol' => 'admin'
                ];
                header("Location: dashboard.php");
                exit;
            }

            // AGENTE -> 2FA grupal
            $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expira = date('Y-m-d H:i:s', time() + 15 * 60); // 15 min

            $ins = $conn->prepare("INSERT INTO sesiones_2fa(user_id, codigo, expires_at) VALUES (?,?,?)");
            $uid = (int) $fila['id'];
            $ins->bind_param("iss", $uid, $codigo, $expira);
            $ins->execute();

            // Guardar estado de 2FA pendiente
            $_SESSION['pending_2fa'] = [
                'user_id' => $uid,
                'usuario' => $fila['usuario'],
                'correo' => $fila['correo']
            ];

            // Recopilar destinatarios: todos los agentes menos el que entra
            $dest = [];
            $q = $conn->query(
                "SELECT correo, usuario FROM usuarios
               WHERE rol='agente' AND activo=1
               AND id <> $uid
               AND correo IS NOT NULL AND correo <> ''"
            );
            while ($r = $q->fetch_assoc()) {
                $dest[] = $r;
            }

            // Fallback: si no hay otros agentes, se envía a admins (para que alguien reciba el aviso)
            if (count($dest) === 0) {
                $q2 = $conn->query(
                    "SELECT correo, usuario FROM usuarios
                 WHERE rol='admin' AND activo=1
                 AND correo IS NOT NULL AND correo <> ''"
                );
                while ($r = $q2->fetch_assoc()) {
                    $dest[] = $r;
                }
            }

            // Enviar correo con el código a los destinatarios
            if (count($dest) > 0) {
                $mailer = new CorreoDenuncia();
                $subject = "Código de verificación de acceso (equipo)";
                $body = "
                  <div style='font-family:Arial,sans-serif;background:#f8f9fb;padding:20px'>
                    <div style='max-width:600px;margin:auto;background:#fff;border:1px solid #eee;border-radius:12px;overflow:hidden'>
                      <div style='background:#942934;color:#fff;padding:14px 20px;font-weight:bold'>
                        Alerta de acceso a la plataforma
                      </div>
                      <div style='padding:24px;color:#333'>
                        <p>El usuario <strong>{$fila['usuario']}</strong> está intentando ingresar.</p>
                        <p>Comparte el siguiente código solo si reconoces al compañero:</p>
                        <h2 style='letter-spacing:3px;margin:12px 0;font-size:28px;color:#d32f57;'>$codigo</h2>
                        <p style='color:#666;font-size:13px'>El código caduca en 15 minutos.</p>
                      </div>
                    </div>
                  </div>
                ";
                foreach ($dest as $d) {
                    $mailer->sendConfirmacion($d['usuario'], $d['correo'], null, $subject, $body);
                }
            }

            header("Location: verificar_2fa.php");
            exit;

        } else {
            $error = "⚠️ Contraseña incorrecta.";
        }
    } else {
        $error = "⚠️ Usuario no encontrado o inactivo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Login - Administrador</title>
    <link href="../assets/css/output.css" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4">
    <div
        class="bg-white w-full max-w-[400px] p-6 sm:p-8 rounded-2xl shadow-2xl border border-gray-300 flex flex-col items-center">

        <!-- 🦉 Búho -->
        <img src="../assets/img/Ovi6.gif" alt="Búho F&C" class="w-24 sm:w-28 h-auto mb-4">

        <h2 class="text-2xl font-bold text-center mb-6 text-[#942934]">Ingreso al sistema</h2>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 p-3 mb-4 rounded border border-red-300 animate-pulse w-full text-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4 w-full" autocomplete="off">
            <input type="text" name="usuario" placeholder="Usuario o correo"
                class="w-full border border-gray-300 rounded-xl px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:ring-2 focus:ring-[#d32f57]"
                required>

            <input type="password" name="contrasena" placeholder="Contraseña"
                class="w-full border border-gray-300 rounded-xl px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:ring-2 focus:ring-[#d32f57]"
                required>

            <button type="submit"
                class="w-full bg-[#d32f57] text-white font-semibold px-6 py-2 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98] hover:bg-[#942934]">
                Ingresar
            </button>
        </form>
    </div>
</body>

</html>