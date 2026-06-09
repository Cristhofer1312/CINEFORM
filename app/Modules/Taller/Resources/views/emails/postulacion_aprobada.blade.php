<!DOCTYPE html>
<html>
<head>
    <title>Postulación Aprobada - CINEFORM</title>
</head>
<body>
    <h2>¡Felicidades, {{ $inscripcion->persona->primer_nombre }}!</h2>
    <p>Tu postulación al programa <strong>{{ $inscripcion->curso->nombre }}</strong> ha sido <strong>APROBADA</strong>.</p>
    <p>Ya eres un participante formal de este curso. Puedes acceder al contenido y detalles desde tu panel de "Mis Cursos".</p>
    <p><a href="{{ route('login') }}">Haz clic aquí para iniciar sesión</a></p>
    <br>
    <p>Atentamente,</p>
    <p>El equipo de CINEFORM</p>
</body>
</html>
