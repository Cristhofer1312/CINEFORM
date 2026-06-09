<!DOCTYPE html>
<html>
<head>
    <title>Observaciones en tu Postulación - CINEFORM</title>
</head>
<body>
    <h2>Hola, {{ $inscripcion->persona->primer_nombre }}</h2>
    <p>Se han encontrado las siguientes observaciones en tu postulación al programa <strong>{{ $inscripcion->curso->nombre }}</strong>:</p>
    
    <div style="background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <strong>Motivo:</strong><br>
        {{ $inscripcion->motivo_estado }}
    </div>

    <p>No te preocupes, aún tienes la oportunidad de corregir tus respuestas o volver a subir los documentos solicitados para continuar con el proceso.</p>
    <p>Por favor, accede al siguiente enlace para actualizar tu postulación:</p>
    <p><a href="{{ route('taller.inscripciones.create', $inscripcion->id_curso) }}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Corregir Postulación</a></p>
    
    <br>
    <p>Atentamente,</p>
    <p>El equipo de CINEFORM</p>
</body>
</html>
