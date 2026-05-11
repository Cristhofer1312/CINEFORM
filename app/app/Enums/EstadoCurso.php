<?php

namespace App\Enums;

/**
 * Enum para los estados del Curso según la tabla taller.estados_curso
 */
enum EstadoCurso: int
{
    case POR_ACEPTAR = 1;
    case RECHAZADO = 2;
    case DECLINADO = 3;
    case EDICION = 4;
    case APROBACION = 5;
    case INSCRIPCION = 6;
    case EN_CURSO = 7;
    case FINALIZADO = 8;
    case CERRADO = 9;

    /**
     * Obtiene la etiqueta legible del estado
     */
    public function label(): string
    {
        return match($this) {
            self::POR_ACEPTAR => 'Por Aceptar',
            self::RECHAZADO => 'Rechazado',
            self::DECLINADO => 'Declinado',
            self::EDICION => 'En Edición',
            self::APROBACION => 'En Aprobación',
            self::INSCRIPCION => 'En Inscripción',
            self::EN_CURSO => 'En Curso',
            self::FINALIZADO => 'Finalizado',
            self::CERRADO => 'Cerrado',
        };
    }

    /**
     * Obtiene el color de Bootstrap asociado al estado
     */
    public function color(): string
    {
        return match($this) {
            self::POR_ACEPTAR => 'info',
            self::RECHAZADO => 'danger',
            self::DECLINADO => 'warning',
            self::EDICION => 'primary',
            self::APROBACION => 'secondary',
            self::INSCRIPCION => 'success',
            self::EN_CURSO => 'dark',
            self::FINALIZADO => 'success',
            self::CERRADO => 'dark',
        };
    }
}
