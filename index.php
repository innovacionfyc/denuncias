<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Denuncia anónima o personal</title>
    <link href="assets/css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-xl">
        <h1 class="text-2xl font-bold mb-6 text-center">Formulario de Denuncia</h1>

        <form action="guardar_denuncia.php" method="POST" enctype="multipart/form-data" class="space-y-4">

            <input type="text" name="nombre" placeholder="Tu nombre completo" required
                   class="w-full border border-gray-300 rounded px-4 py-2" />

            <input type="text" name="cedula" placeholder="Tu cédula" required
                   class="w-full border border-gray-300 rounded px-4 py-2" />

            <input type="email" name="correo" placeholder="Tu correo electrónico" required
                   class="w-full border border-gray-300 rounded px-4 py-2" />

            <textarea name="mensaje" rows="5" placeholder="Escribe aquí tu denuncia..." required
                      class="w-full border border-gray-300 rounded px-4 py-2"></textarea>

            <label class="block text-sm font-semibold">Subir fotos (puedes subir varias):</label>
            <input type="file" name="fotos[]" multiple accept="image/*"
                   class="w-full border border-gray-300 rounded px-2 py-1" />

            <label class="block text-sm font-semibold">Subir audios:</label>
            <input type="file" name="audios[]" multiple accept="audio/*"
                   class="w-full border border-gray-300 rounded px-2 py-1" />

            <button type="submit"
                    class="bg-blue-600 text-white font-semibold px-6 py-2 rounded hover:bg-blue-700 transition">
                Enviar denuncia
            </button>
        </form>
    </div>

</body>
</html>
