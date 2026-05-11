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
        if ($curso->isDirty($camposClave)) {
            $this->generarCodigoProcinec($curso);
        }
    }

    /**
     * Lógica centralizada para la generación del código único PROCINEC
     * Formato original: LAB-{actividad}{trimestre}{correlativo}{aspecto}{modalidad}{modalidadEspecial}-{año}
     */
    private function generarCodigoProcinec(Curso $curso)
    {
        try {
            // Obtener abreviaturas de las relaciones
            $actividad = $curso->actividadFormativa?->abreviatura ?? '';
            $aspecto = $curso->aspecto?->abreviatura ?? '';
            $trimestre = $curso->trimestre ?? '';
            $correlativo = $curso->correlativo ?? '0';
            $anio = $curso->anio ?? date('Y');
            $modEsp = $curso->modalidadEspecial?->abreviatura ?? '';
            $modalidad = $curso->modalidad?->abreviatura ?? '';

            // Construcción del código según nomenclatura PROCINEC: LAB-ABC11XYZ-2026
            $middle = $actividad . $trimestre . $correlativo . $aspecto . $modalidad . $modEsp;
            
            $curso->codigo = strtoupper("LAB-" . $middle . "-" . $anio);
            
            Log::info('Código PROCINEC generado en Servidor:', ['codigo' => $curso->codigo]);
            
        } catch (\Exception $e) {
            Log::error('Error al generar código PROCINEC en Observer: ' . $e->getMessage());
        }
    }
}
