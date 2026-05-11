<?php

namespace Modules\Taller\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCursoRequest extends FormRequest
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
     * A diferencia de StoreCursoRequest, aquí los campos que solo se
     * establecen en la creación (trimestre, correlativo, anio, 
     * id_actividad_formativa) NO son requeridos, ya que el formulario
     * de edición no los incluye.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nombre'                => 'sometimes|required|string|max:255',
            'codigo'                => 'nullable|string|max:100',
            'id_modalidad'          => 'sometimes|required|integer',
            'id_actividad_formativa'=> 'nullable|integer',
            'id_aspecto'            => 'nullable|integer',
            'id_modalidad_especial' => 'nullable|integer',
            'id_persona'            => 'sometimes|required|integer',
            'nivel'                 => 'nullable|string|max:50',
            'trimestre'             => 'nullable|integer|min:1|max:4',
            'correlativo'           => 'nullable|integer|min:1',
            'anio'                  => 'nullable|integer|min:2020|max:2100',
            'descripcion'           => 'nullable|string',
            'duracion'              => 'nullable|integer|min:1',
            'horas'                 => 'nullable|integer|min:1',
            'cantidad_cupos'        => 'sometimes|integer|min:1',
            'telegram'              => 'nullable|url|max:255',
            'es_nacional'           => 'nullable',
            'fecha_inicio'          => 'sometimes|required|date',
            'fecha_fin'             => 'sometimes|required|date|after_or_equal:fecha_inicio',
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
            'id_persona.required'   => 'Debe asignar un facilitador al curso.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required'    => 'La fecha de fin es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'cantidad_cupos.min'      => 'La cantidad de cupos debe ser al menos 1.',
            'telegram.url'          => 'El enlace de Telegram debe ser una URL válida.',
            'contenidos.*.titulo.required_with' => 'Cada contenido debe tener un título.',
        ];
    }
}
