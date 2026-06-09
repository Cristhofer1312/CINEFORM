<!DOCTYPE html>
<html>
<head>
    <title>Postulación Denegada - CINEFORM</title>
</head>
<body>
    <h2>Hola, {{ $inscripcion->persona->primer_nombre }}</h2>
    <p>Lamentamos informarte que tu postulación al programa <strong>{{ $inscripcion->curso->nombre }}</strong> ha sido <strong>DENEGADA</strong> definitivamente.</p>
    
    <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <strong>Razón del rechazo:</strong><br>
        {{ $inscripcion->motivo_estado }}
    </div>

    <p>Esta decisión es final. Te invitamos a estar atento a nuestras próximas convocatorias y programas de formación.</p>
    
    <br>
    <p>Atentamente,</p>
    <p>El equipo de CINEFORM</p>
</body>
</html>
