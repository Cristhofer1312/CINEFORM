<?php

namespace App\Enums;

/**
 * Estado de la configuración del certificado de un curso.
 *
 * No forma parte del ciclo de vida del curso (App\Enums\EstadoCurso) ni se
 * guarda en la base de datos: es un valor derivado de los archivos que hay en
 * storage/app/public/Certificados/. Si mañana se borra una plantilla, el estado
 * cambia solo, sin ninguna tarea que mantener.
 */
enum EstadoCertificado: string
{
    case CONFIGURADO = 'configurado';
    case SIN_PLANTILLA = 'sin_plantilla';
    case FALTA_FIRMA = 'falta_firma';

    /**
     * Etiqueta legible para el distintivo de la ficha del curso.
     */
    public function label(): string
    {
        return match ($this) {
            self::CONFIGURADO => 'Certificado configurado',
            self::SIN_PLANTILLA => 'Certificado sin plantilla',
            self::FALTA_FIRMA => 'Certificado sin firma',
        };
    }

    /**
     * Color de Bootstrap del distintivo.
     */
    public function color(): string
    {
        return match ($this) {
            self::CONFIGURADO => 'success',
            self::SIN_PLANTILLA => 'danger',
            self::FALTA_FIRMA => 'danger',
        };
    }

    /**
     * Texto que explica qué falta, para el title del distintivo.
     */
    public function detalle(): string
    {
        return match ($this) {
            self::CONFIGURADO => 'El certificado tiene imagen de fondo y firma configuradas.',
            self::SIN_PLANTILLA => 'No hay imagen de fondo para este certificado, ni propia ni global. Se emitirá con el diseño propio, sin la plantilla institucional.',
            self::FALTA_FIRMA => 'Falta la firma de la autoridad. El certificado se emite sin ella.',
        };
    }

    /**
     * Icono de Font Awesome del distintivo.
     */
    public function icono(): string
    {
        return match ($this) {
            self::CONFIGURADO => 'fa-circle-check',
            self::SIN_PLANTILLA => 'fa-triangle-exclamation',
            self::FALTA_FIRMA => 'fa-triangle-exclamation',
        };
    }
}
