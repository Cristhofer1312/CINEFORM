<?php

namespace App\Observers;

use Modules\Taller\Entities\Curso;
use Illuminate\Support\Facades\Log;

class CursoObserver
{
    /**
     * Se ejecuta antes de crear un nuevo registro
     */
    public function creating(Curso $curso)
    {
        $this->generarCodigoProcinec($curso);
    }

    /**
     * Se ejecuta antes de actualizar un registro existente
     */
    public function updating(Curso $curso)
    {
        // Solo regenerar si cambian campos clave
        $camposClave = ['id_actividad_formativa', 'id_aspecto', 'trimestre', 'correlativo', 'anio', 'id_modalidad_especial', 'id_modalidad'];
        if (!$curso->isDirty($camposClave)) {
            return;
        }

        // El código ya impreso en QR/PDF no se puede cambiar: invalidaría
        // certificados aprobados. Se conserva y se deja constancia.
        $tieneCertificados = \Modules\Taller\Entities\Inscripcion::where('id_curso', $curso->getKey())
            ->where('certificado_aprobado', true)
            ->exists();

        if ($tieneCertificados) {
            Log::warning('Código PROCINEC conservado: el curso ya tiene certificados aprobados', [
                'id_curso' => $curso->getKey(),
                'codigo' => $curso->getOriginal('codigo'),
            ]);

            return;
        }

        $this->generarCodigoProcinec($curso);
    }

    /**
     * Lógica centralizada para la generación del código único PROCINEC
     * Formato original: LAB-{actividad}{trimestre}{correlativo}{aspecto}{modalidad}{modalidadEspecial}-{año}
     */
    private function generarCodigoProcinec(Curso $curso)
    {
        // Obtener abreviaturas de las relaciones (sin tragar errores: un
        // código nulo/degenerado es peor que una excepción que revierte la tx).
        $actividad = trim((string) ($curso->actividadFormativa?->abreviatura ?? ''));
        $modalidad = trim((string) ($curso->modalidad?->abreviatura ?? ''));
        $aspecto = trim((string) ($curso->aspecto?->abreviatura ?? ''));
        $modEsp = trim((string) ($curso->modalidadEspecial?->abreviatura ?? ''));
        $trimestre = $curso->trimestre;
        $correlativo = $curso->correlativo;
        $anio = $curso->anio ?? date('Y');

        if ($actividad === '' || $modalidad === '') {
            throw new \InvalidArgumentException('No se puede generar el código PROCINEC sin abreviaturas de actividad y modalidad.');
        }
        if (! is_numeric($trimestre) || (int) $trimestre < 1 || (int) $trimestre > 4) {
            throw new \InvalidArgumentException('No se puede generar el código PROCINEC sin un trimestre válido (1-4).');
        }
        if (! is_numeric($correlativo) || (int) $correlativo < 1) {
            throw new \InvalidArgumentException('No se puede generar el código PROCINEC sin un correlativo válido (>= 1).');
        }

        // Construcción del código según nomenclatura PROCINEC: LAB-ABC11XYZ-2026
        $middle = $actividad . $trimestre . $correlativo . $aspecto . $modalidad . $modEsp;

        $curso->codigo = strtoupper('LAB-' . $middle . '-' . $anio);

        Log::info('Código PROCINEC generado en Servidor:', ['codigo' => $curso->codigo]);
    }
}
