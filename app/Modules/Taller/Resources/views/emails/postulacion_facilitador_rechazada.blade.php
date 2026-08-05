<!DOCTYPE html>
<html>
<head>
    <title>Observaciones en tu Postulación - CINEFORM</title>
</head>
<body>
    <h2>Hola, {{ $postulacion->persona->primer_nombre }}</h2>
    <p>Se han encontrado las siguientes observaciones en tu postulación como Facilitador de CINEFORM:</p>
    
    <div style="background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <strong>Motivo:</strong><br>
        {{ $postulacion->motivo_rechazo }}
    </div>

    <p>No te preocupes, puedes corregir tus respuestas o volver a subir los documentos solicitados y <strong>re-postularte</strong>.</p>
    <p>Por favor, accede al siguiente enlace para volver a postularte:</p>
    <p><a href="{{ route('taller.postulacion-facilitador.landing') }}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Volver a Postularme</a></p>
    
    <br>
    <p>Atentamente,</p>
    <p>El equipo de CINEFORM</p>
</body>
</html>