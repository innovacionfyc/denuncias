<?php
require_once "db/conexion.php";
require_once "correo/enviar_correo.php";
session_start();

$ident = trim($_POST['ident'] ?? '');
$pass = $_POST['pass'] ?? '';

$stmt = $conn->prepare("
  SELECT id, usuario, correo, contrasena, rol, activo
  FROM usuarios
  WHERE (usuario=? OR correo=?) AND activo=1
  LIMIT 1
");
$stmt->bind_param("ss", $ident, $ident);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();

if (!$u || !password_verify($pass, $u['contrasena'])) {
    header("Location: login.php?e=1");
    exit;
}

if ($u['rol'] === 'admin') {
    // Admin entra directo
    $_SESSION['usuario'] = [
        'id' => (int) $u['id'],
        'nombre' => $u['usuario'],   // puedes cambiar a 'nombre' si luego lo agregas
        'correo' => $u['correo'],
        'rol' => 'admin'
    ];
    header("Location: admin/dashboard.php");
    exit;
}

/* Rol: agente → 2FA grupal */
$codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expira = date('Y-m-d H:i:s', time() + 15 * 60); // 15 min

$ins = $conn->prepare("INSERT INTO sesiones_2fa(user_id,codigo,expires_at) VALUES (?,?,?)");
$ins->bind_param("iss", $u['id'], $codigo, $expira);
$ins->execute();

// Guardar "pendiente de 2FA"
$_SESSION['pending_2fa'] = ['user_id' => (int) $u['id'], 'usuario' => $u['usuario'], 'correo' => $u['correo']];

// Enviar a TODOS los agentes menos el que entra (que tengan correo)
$correoTool = new CorreoDenuncia();
$subject = "Código de verificación de acceso (equipo)";
$body = "
  <div style='font-family:Arial,sans-serif;background:#f8f9fb;padding:20px'>
    <div style='max-width:600px;margin:auto;background:#fff;border:1px solid #eee;border-radius:12px;overflow:hidden'>
      <div style='background:#942934;color:#fff;padding:14px 20px;font-weight:bold'>Alerta: ingreso a la plataforma</div>
      <div style='padding:24px;color:#333'>
        <p>Un compañero está intentando ingresar.</p>
        <p>Comparte este código solo si lo reconoces:</p>
        <h2 style='letter-spacing:3px;margin:12px 0;font-size:28px;color:#d32f57;'>$codigo</h2>
        <p style='color:#666;font-size:13px'>Caduca en 15 minutos.</p>
      </div>
    </div>
  </div>
";

// Saca correos de agentes
$q = $conn->query("SELECT correo FROM usuarios WHERE rol='agente' AND activo=1 AND id<>" . (int) $u['id'] . " AND correo IS NOT NULL AND correo<>''");
while ($r = $q->fetch_assoc()) {
    $correoTool->sendConfirmacion('Equipo', $r['correo'], null, $subject, $body);
}

header("Location: verificar_2fa.php");
