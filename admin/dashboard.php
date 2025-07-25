<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require_once "../db/conexion.php";

$por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$inicio = ($pagina_actual - 1) * $por_pagina;

// Filtros
$nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$cedula = isset($_GET['cedula']) ? $_GET['cedula'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

$condiciones = array();
$parametros = array();
$tipos = '';

if ($nombre !== '') {
    $condiciones[] = "nombre LIKE ?";
    $parametros[] = "%" . $nombre . "%";
    $tipos .= 's';
}
if ($cedula !== '') {
    $condiciones[] = "cedula LIKE ?";
    $parametros[] = "%" . $cedula . "%";
    $tipos .= 's';
}
if ($estado !== '') {
    $condiciones[] = "estado = ?";
    $parametros[] = $estado;
    $tipos .= 's';
}

$where = count($condiciones) ? "WHERE " . implode(" AND ", $condiciones) : "";

// Total de denuncias para paginación
$sqlTotal = "SELECT COUNT(*) as total FROM denuncias $where";
$stmtTotal = $conn->prepare($sqlTotal);
if ($tipos !== '') {
    $bind_params_total[] = $tipos;
    foreach ($parametros as $key => $val) {
        $bind_params_total[] = &$parametros[$key];
    }
    call_user_func_array(array($stmtTotal, 'bind_param'), $bind_params_total);
}
$stmtTotal->execute();
$resTotal = $stmtTotal->get_result();
$totalDenuncias = $resTotal->fetch_assoc()['total'];
$totalPaginas = ceil($totalDenuncias / $por_pagina);

// Obtener denuncias paginadas
$sql = "SELECT * FROM denuncias $where ORDER BY fecha_creacion DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
if (!empty($parametros)) {
    $parametros[] = $inicio;
    $parametros[] = $por_pagina;
    $tipos .= 'ii';
    $bind_params = array_merge(array($tipos), array_map(function (&$val) { return $val; }, $parametros));
    for ($i = 1; $i < count($bind_params); $i++) {
        $bind_refs[] = &$bind_params[$i];
    }
    call_user_func_array(array($stmt, 'bind_param'), array_merge(array($bind_params[0]), $bind_refs));
} else {
    $stmt->bind_param("ii", $inicio, $por_pagina);
}
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Denuncias</title>
    <link href="../assets/css/output.css" rel="stylesheet">
</head>
<body class="bg-[#f8f9fb] min-h-screen p-8">
    <div class="max-w-7xl mx-auto bg-white p-8 rounded-2xl shadow-2xl border border-gray-300 space-y-6">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-[#685f2f]">📋 Panel de Denuncias</h1>
            <a href="cerrar_sesion.php" class="text-[#942934] font-semibold hover:underline transition-all duration-200">Cerrar sesión</a>
        </div>

        <!-- Filtros -->
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <input type="text" name="nombre" placeholder="Nombre" 
            value="<?= isset($_GET['nombre']) ? $_GET['nombre'] : '' ?>"
            class="w-full border border-gray-300 rounded px-3 py-2 text-sm placeholder:font-medium placeholder:text-gray-500">

          <input type="text" name="cedula" placeholder="Cédula" 
            value="<?= isset($_GET['cedula']) ? $_GET['cedula'] : '' ?>"
            class="w-full border border-gray-300 rounded px-3 py-2 text-sm placeholder:font-medium placeholder:text-gray-500">

          <select name="estado" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            <option value="">Todos los estados</option>
            <option value="pendiente" <?= (isset($_GET['estado']) && $_GET['estado'] === 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
            <option value="en_proceso" <?= (isset($_GET['estado']) && $_GET['estado'] === 'en_proceso') ? 'selected' : '' ?>>En proceso</option>
            <option value="resuelto" <?= (isset($_GET['estado']) && $_GET['estado'] === 'resuelto') ? 'selected' : '' ?>>Finalizada</option>
          </select>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left border border-gray-300">
                <thead class="bg-[#a08e43] text-white">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-2">Cédula</th>
                        <th class="px-4 py-3">Correo</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($fila = $resultado->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 transition-all">
                            <td class="px-4 py-3 font-medium"><?= $fila['id'] ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($fila['nombre']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($fila['cedula']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($fila['correo']) ?></td>
                            <td class="px-4 py-3">
                                <?php
                                $estado = $fila['estado'];
                                switch ($estado) {
                                    case 'pendiente':
                                        $colorEstado = 'bg-[#942934] text-white';
                                        break;
                                    case 'en_proceso':
                                        $colorEstado = 'bg-[#e96510] text-white';
                                        break;
                                    case 'resuelto':
                                        $colorEstado = 'bg-[#685f2f] text-white';
                                        break;
                                    default:
                                        $colorEstado = 'bg-gray-300 text-black';
                                        break;
                                }
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $colorEstado ?>">
                                    <?= ucfirst($estado) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3"><?= $fila['fecha_creacion'] ?></td>
                            <td class="px-4 py-3">
                                <form action="cambiar_estado.php" method="POST" class="flex flex-col md:flex-row items-start md:items-center gap-2">
                                    <input type="hidden" name="id" value="<?= $fila['id'] ?>">
                                    <select name="nuevo_estado" class="border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-[#d32f57]">
                                        <option value="pendiente" <?= $estado == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="en_proceso" <?= $estado == 'en_proceso' ? 'selected' : '' ?>>En proceso</option>
                                        <option value="resuelto" <?= $estado == 'resuelto' ? 'selected' : '' ?>>Finalizada</option>
                                    </select>
                                    <button type="submit"
                                        class="bg-[#d32f57] hover:bg-[#942934] text-white font-semibold px-3 py-1 rounded-xl transition-all duration-300 text-sm">
                                        Cambiar
                                    </button>
                                    <a href="ver_denuncia.php?id=<?= $fila['id'] ?>"
                                       class="bg-[#a08e43] text-white px-4 py-1 rounded-xl text-sm font-semibold shadow-md transition-all duration-300 hover:bg-[#685f2f] hover:scale-[1.03]">
                                       Ver
                                    </a>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPaginas > 1): ?>
        <div class="flex justify-center mt-6 space-x-2">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a href="?<?php 
                  $query = $_GET; 
                  $query['pagina'] = $i; 
                  echo http_build_query($query); ?>"
                   class="px-3 py-1 rounded border <?= $i == $pagina_actual ? 'bg-[#942934] text-white font-bold' : 'bg-white text-[#942934]' ?>">
                  <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

    </div>

    <script>
      document.querySelectorAll('form input, form select').forEach(el => {
        el.addEventListener('change', () => {
          el.form.submit();
        });

        if (el.tagName === 'INPUT') {
          el.addEventListener('keyup', () => {
            clearTimeout(el._delay);
            el._delay = setTimeout(() => {
              el.form.submit();
            }, 300);
          });
        }
      });
    </script>
</body>
</html>
