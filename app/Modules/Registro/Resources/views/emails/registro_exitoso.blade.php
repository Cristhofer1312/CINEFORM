<!DOCTYPE html>
<html>
<head>
    <title>Bienvenido a CINEFORM</title>
</head>
<body>
    <h2>¡Hola, {{ $user->username }}!</h2>
    <p>Te damos la bienvenida a CINEFORM. Tu registro ha sido completado exitosamente.</p>
    <p>Ahora puedes acceder al sistema con tu nombre de usuario y la contraseña que proporcionaste.</p>
    <p><a href="{{ route('login') }}">Haz clic aquí para iniciar sesión</a></p>
    <br>
    <p>Atentamente,</p>
    <p>El equipo de CINEFORM</p>
</body>
</html>
