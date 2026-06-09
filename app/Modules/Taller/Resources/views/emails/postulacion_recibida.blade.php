<!DOCTYPE html>
<html>
<head>
    <title>Postulación Recibida - CINEFORM</title>
</head>
<body>
    <h2>Hola, {{ $inscripcion->persona->primer_nombre }}</h2>
    <p>Hemos recibido correctamente tu postulación al programa: <strong>{{ $inscripcion->curso->nombre }}</strong>.</p>
    <p>Actualmente, tu solicitud se encuentra en <strong>fase de revisión</strong>. Te notificaremos por este mismo medio una vez que tengamos una respuesta.</p>
    <p>Gracias por tu interés en nuestra formación.</p>
    <br>
    <p>Atentamente,</p>
    <p>El equipo de CINEFORM</p>
</body>
</html>
