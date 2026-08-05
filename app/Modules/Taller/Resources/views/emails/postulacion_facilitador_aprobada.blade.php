<!DOCTYPE html>
<html>
<head>
    <title>Postulación Aprobada - CINEFORM</title>
</head>
<body>
    <h2>¡Felicidades, {{ $postulacion->persona->primer_nombre }}!</h2>
    <p>Tu postulación como Facilitador de CINEFORM ha sido <strong>APROBADA</strong>.</p>
    <p>Ya eres un facilitador formal de CINEFORM. Tu nuevo perfil está disponible inmediatamente.</p>
    <p><a href="{{ route('login') }}">Haz clic aquí para iniciar sesión</a></p>
    <br>
    <p>Atentamente,</p>
    <p>El equipo de CINEFORM</p>
</body>
</html>