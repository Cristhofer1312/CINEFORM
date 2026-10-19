<?php

namespace Modules\Taller\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Parametros\Entities\Estados;

class StorePaso2Request extends FormRequest
{
    /**
     * Paso 2: Logística, fechas y sede.
     *
     * El trimestre NO es input: se deriva de fecha_inicio
     * (ceil(mes/3)) en validated() y en el controlador. El anio
     * defaults al año de inicio si no se indica.
     */
    public function authorize()
    {
        return hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::CREAR_CURSO);
    }

    public function rules()
    {
        return [
            'id_persona' => 'required|exists:'.\Modules\Comun\Entities\PersonalData::class.',id_persona',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'correlativo' => 'required|integer|min:1',
            'anio' => 'nullable|integer|min:2020|max:2100',
            'duracion' => 'nullable|integer|min:1',
            'horas' => 'nullable|integer|min:1',
            'cantidad_cupos' => 'required|integer|min:1',
            'telegram' => 'nullable|url|max:255',
            'direccion_corta' => 'required|string|max:150',
            'es_nacional' => 'nullable|boolean',
            'localidades' => 'required_unless:es_nacional,true,1|array',
            'localidades.*' => [Rule::exists(Estados::class, 'id')],
        ];
    }

    /**
     * Deriva trimestre/anio desde fecha_inicio para que el preview
     * PROCINEC y el guardado usen siempre el mismo valor.
     */
    public function validated($key = null, $default = null)
    {
        $datos = parent::validated($key, $default);

        if (is_array($datos) && ! empty($datos['fecha_inicio'])) {
            try {
                $inicio = Carbon::parse($datos['fecha_inicio']);
                $datos['trimestre'] = (int) ceil($inicio->month / 3);
                if (empty($datos['anio'])) {
                    $datos['anio'] = $inicio->year;
                }
            } catch (\Throwable $e) {
                // Fecha malformada: la regla 'date' ya la reporta.
            }
        }

        return $datos;
    }

    public function messages()
    {
        return [
            'id_persona.required' => 'Debe asignar un facilitador al curso.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'cantidad_cupos.required' => 'Debe indicar la cantidad de cupos disponibles.',
            'direccion_corta.required' => 'La dirección del curso es obligatoria.',
            'localidades.required_unless' => 'Debe seleccionar al menos una localidad cuando el curso no es de alcance nacional.',
            'telegram.url' => 'El enlace de Telegram debe ser una URL válida.',
        ];
    }
}
