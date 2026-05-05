<?php

namespace App\Enums;

enum EstadoCurso: string
{
    
 case por_aceptar = 'por_aceptar'; //Facilitador aun no acepta el curso
 case rechazadof = 'rechazado'; //Facilitador rechaza el curso
 case evaluacion = 'evaluacion'; // Facilitador evalua la plantilla entregada para edicion de la misma a sus necesidades   
 case aprobacion = 'aprobacion'; //Fase donde el analista o el coordinador aprueba el curso
 case inscripcion = 'inscripcion'; //Fase donde el curso esta listo para inscribirse
 case en_curso = 'en curso'; // Fase donde se imparte el curso
 case finalizado = 'finalizado'; // Fase de emision de certificados y aclaraciones con el facilitador
 case cerrado = 'cerrado'; // Fase final del curso donde solamente se puede solicitar la emision de certificados

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
