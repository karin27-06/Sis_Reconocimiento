<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Prueba Verificar Acceso</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 2rem;">

    <h2>Formulario de Verificación de Acceso</h2>

    <form id="verificarForm">
        @csrf

        <label>ID Espacio:</label><br>
        <input type="number" name="idEspacio" value="1" required><br><br>

        <label>ID Tipo (1=Foto, 2=Huella):</label><br>
        <input type="number" name="idTipo" value="1" required><br><br>

        <label>Código de error:</label><br>
        <input type="number" name="codigo_error" value="0"><br><br>

        <label>ID Huella (solo si idTipo=2):</label><br>
        <input type="text" name="idHuella"><br><br>

        <label>Fecha Envío ESP32:</label><br>
        <input type="datetime-local" name="fechaEnvioESP32"><br><br>

        <label>Foto Acceso:</label><br>
        <input type="file" id="fotoAcceso" accept="image/*" required><br><br>

        <button type="submit">Enviar</button>
    </form>

    <pre id="respuesta" style="margin-top: 2rem; background: #f4f4f4; padding: 1rem;"></pre>

<script>
    const form = document.getElementById('verificarForm');
    const respuesta = document.getElementById('respuesta');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const file = document.getElementById('fotoAcceso').files[0];
        if (!file) return alert("Debes seleccionar una foto");

        const reader = new FileReader();
        reader.onload = async () => {
            const fotoBase64 = reader.result;

            // Usamos FormData
            const formData = new FormData();
            formData.append("idEspacio", form.idEspacio.value);
            formData.append("idTipo", form.idTipo.value);
            formData.append("codigo_error", form.codigo_error.value);
            formData.append("idHuella", form.idHuella.value);
            formData.append("fechaEnvioESP32", form.fechaEnvioESP32.value);
            formData.append("fotoBase64", fotoBase64);

            try {
                const res = await fetch("/api/verificar-acceso", {
                    method: "POST",
                    body: formData
                });

                // Intentar leer JSON, si no es JSON lanzará error
                const json = await res.json();
                respuesta.textContent = JSON.stringify(json, null, 2);

            } catch (err) {
                respuesta.textContent = "❌ Error al procesar la respuesta: " + err;
            }
        };
        reader.readAsDataURL(file);
    });
</script>

</body>
</html>
