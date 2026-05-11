<?php

namespace Modules\Taller\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCursoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nombre'                => 'required|string|max:255',
            'codigo'                => 'nullable|string|max:100',
            'id_modalidad'          => 'required|integer',
            'id_actividad_formativa'=> 'required|integer',
            'id_aspecto'            => 'nullable|integer',
            'id_modalidad_especial' => 'nullable|integer',
            'id_persona'            => 'required|integer',
            'nivel'                 => 'nullable|string|max:50',
            'trimestre'             => 'required|integer|min:1|max:4',
            'correlativo'           => 'required|integer|min:1',
            'anio'                  => 'required|integer|min:2020|max:2100',
            'descripcion'           => 'nullable|string',
            'duracion'              => 'nullable|integer|min:1',
            'horas'                 => 'nullable|integer|min:1',
            'cantidad_cupos'        => 'required|integer|min:1',
            'telegram'              => 'nullable|url|max:255',
            'es_nacional'           => 'nullable',
            'fecha_inicio'          => 'required|date',
            'fecha_fin'             => 'required|date|after_or_equal:fecha_inicio',
            'localidades'           => 'nullable|array',
            'localidades.*'         => 'integer',

            // Contenidos (array de temas/evaluaciones)
            'contenidos'                        => 'nullable|array',
            'contenidos.*.titulo'               => 'required_with:contenidos|string|max:255',
            'contenidos.*.es_evaluacion'        => 'nullable',
            'contenidos.*.id_tipo_evaluacion'   => 'nullable|integer',
            'contenidos.*.ponderacion'          => 'nullable|numeric|min:0|max:100',
            'contenidos.*.url_contenido'        => 'nullable|url|max:500',
            'contenidos.*.fecha_contenido'      => 'nullable|date',
            'contenidos.*.descripcion_breve'    => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'nombre.required'       => 'El nombre del curso es obligatorio.',
            'id_modalidad.required' => 'La modalidad es obligatoria.',
            'id_modalidad.exists'   => 'La modalidad seleccionada no es válida.',
            'id_actividad_formativa.required' => 'La actividad formativa es obligatoria.',
            'id_persona.required'   => 'Debe asignar un facilitador al curso.',
            'trimestre.required'    => 'El trimestre es obligatorio.',
            'correlativo.required'  => 'El correlativo es obligatorio.',
            'anio.required'         => 'El año es obligatorio.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required'    => 'La fecha de fin es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'cantidad_cupos.required' => 'Debe indicar la cantidad de cupos disponibles.',
            'cantidad_cupos.min'      => 'La cantidad de cupos debe ser al menos 1.',
            'telegram.url'          => 'El enlace de Telegram debe ser una URL válida.',
            'contenidos.*.titulo.required_with' => 'Cada contenido debe tener un título.',
        ];
    }
}
