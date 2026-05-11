<?php

namespace Modules\Taller\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class StoreCursoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja vía middleware en el controlador
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'id_modalidad' => 'required|exists:' . \Modules\Taller\Entities\Modalidad::class . ',id_modalidad',
            'id_actividad_formativa' => 'required|exists:' . \Modules\Taller\Entities\ActividadFormativa::class . ',id_actividad_formativa',
            'id_aspecto' => 'nullable|exists:' . \Modules\Taller\Entities\Aspecto::class . ',id_aspecto',
            'id_modalidad_especial' => 'nullable|exists:' . \Modules\Taller\Entities\ModalidadEspecial::class . ',id_modalidad_especial',
            'id_persona' => 'required|exists:' . \Modules\Comun\Entities\PersonalData::class . ',id_persona',
            'es_nacional' => 'nullable|boolean',
            'localidades' => 'required_unless:es_nacional,true,1|array',
            'localidades.*' => 'exists:comun.estados,id',
            'trimestre' => 'required|integer|min:1|max:4',
            'anio' => 'required|integer|min:2000',
            'correlativo' => 'required|integer',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'cantidad_cupos' => 'nullable|integer|min:1',
            'duracion' => 'nullable|integer|min:1',
            'horas' => 'nullable|integer|min:1',
            'telegram' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            
            // Validación de contenidos
            'contenidos' => 'nullable|array',
            'contenidos.*.titulo' => 'required|string|max:255',
            'contenidos.*.ponderacion' => 'nullable|numeric|min:0|max:100',
        ];
    }

    /**
     * Lógica personalizada de validación (Trimestres)
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $fechaInicio = $this->input('fecha_inicio');
            $fechaFin = $this->input('fecha_fin');
            $trimestre = (int) $this->input('trimestre');

            if ($fechaInicio && $fechaFin) {
                $inicio = Carbon::parse($fechaInicio);
                $fin = Carbon::parse($fechaFin);

                // Regla 1: El trimestre debe coincidir con el mes de inicio
                $trimestreCalculado = (int) ceil($inicio->month / 3);
                if ($trimestre !== $trimestreCalculado) {
                    $validator->errors()->add('trimestre', "El trimestre seleccionado ($trimestre) no coincide con la fecha de inicio (" . $inicio->format('d/m/Y') . "). Debería ser trimestre $trimestreCalculado.");
                }

                // Regla 2: No se permite que el curso cruce trimestres (según regla de PROCINEC)
                $trimestreFin = (int) ceil($fin->month / 3);
                if ($trimestreCalculado !== $trimestreFin) {
                    $validator->errors()->add('fecha_fin', 'El curso no puede terminar en un trimestre distinto al de inicio. Esto no se adapta a la función trimestral de PROCINEC.');
                }
            }
        });
    }

    /**
     * Mensajes personalizados
     */
    public function messages(): array
    {
        return [
            'fecha_fin.after_or_equal' => 'La fecha de finalización no puede ser anterior a la de inicio.',
            'id_actividad_formativa.required' => 'Debe seleccionar una actividad formativa para generar el código.',
        ];
    }
}
