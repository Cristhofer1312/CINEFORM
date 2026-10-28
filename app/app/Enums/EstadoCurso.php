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
    case BORRADOR = 10;

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
            self::BORRADOR => 'Borrador',
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
            self::BORRADOR => 'secondary',
        };
    }

    public function transicionesPermitidas(): array
    {
        // POR_ACEPTAR como destino cubre la reasignación de facilitador
        // (reasignarFacilitador devuelve el curso a Por Aceptar desde fases de preparación 1-5).
        return match ($this) {
            self::POR_ACEPTAR => [self::POR_ACEPTAR, self::RECHAZADO, self::DECLINADO, self::EDICION, self::BORRADOR],
            self::RECHAZADO   => [self::POR_ACEPTAR, self::BORRADOR],
            // DECLINADO → APROBACION: el facilitador corrige lo observado y reenvía a revisión.
            self::DECLINADO   => [self::POR_ACEPTAR, self::EDICION, self::APROBACION, self::BORRADOR],
            self::EDICION     => [self::POR_ACEPTAR, self::APROBACION, self::EDICION, self::BORRADOR],
            self::APROBACION  => [self::POR_ACEPTAR, self::INSCRIPCION, self::EDICION, self::RECHAZADO, self::DECLINADO, self::BORRADOR],
            self::INSCRIPCION => [self::EN_CURSO, self::INSCRIPCION],
            self::EN_CURSO    => [self::FINALIZADO, self::EN_CURSO],
            self::FINALIZADO  => [self::CERRADO],
            self::CERRADO     => [],
            self::BORRADOR    => [self::POR_ACEPTAR],
        };
    }

    public function puedeTransicionarA(self $siguiente): bool
    {
        return in_array($siguiente, $this->transicionesPermitidas(), true);
    }
}
