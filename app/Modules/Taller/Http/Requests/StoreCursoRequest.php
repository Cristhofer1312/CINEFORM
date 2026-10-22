<?php

namespace Modules\Taller\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Modules\Parametros\Entities\Estados;

class StoreCursoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::CREAR_CURSO);
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
            'id_modalidad'          => 'required|exists:' . \Modules\Taller\Entities\Modalidad::class . ',id_modalidad',
            'id_actividad_formativa'=> 'required|exists:' . \Modules\Taller\Entities\ActividadFormativa::class . ',id_actividad_formativa',
            'id_aspecto'            => 'nullable|exists:' . \Modules\Taller\Entities\Aspecto::class . ',id_aspecto',
            'id_modalidad_especial' => 'nullable|exists:' . \Modules\Taller\Entities\ModalidadEspecial::class . ',id_modalidad_especial',
            'id_persona'            => 'required|exists:' . \Modules\Comun\Entities\PersonalData::class . ',id_persona',
            'nivel'                 => 'nullable|string|max:50',
            'trimestre'             => 'required|integer|min:1|max:4',
            'correlativo'           => 'required|integer|min:1',
            'anio'                  => 'required|integer|min:2020|max:2100',
            'descripcion'           => 'nullable|string',
            'duracion'              => 'nullable|integer|min:1',
            'horas'                 => 'nullable|integer|min:1',
            'cantidad_cupos'        => 'required|integer|min:1',
            'telegram'              => 'nullable|url|max:255',
            'direccion_corta'       => 'required|string|max:150',
            'es_nacional'           => 'nullable|boolean',
            'foto'                  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'fecha_inicio'          => 'required|date',
            'fecha_fin'             => 'required|date|after:fecha_inicio',
            'localidades'           => 'required_unless:es_nacional,true,1|array',
            'localidades.*'         => [Rule::exists(Estados::class, 'id')],

            // Contenidos (array de temas/evaluaciones)
            'contenidos'                        => 'nullable|array',
            'contenidos.*.titulo'               => 'required_with:contenidos|string|max:255',
            'contenidos.*.es_evaluacion'        => 'nullable|boolean',
            'contenidos.*.id_tipo_evaluacion'   => 'nullable|integer',
            'contenidos.*.ponderacion'          => 'nullable|numeric|min:0|max:100',
            'contenidos.*.url_contenido'        => 'nullable|url|max:500',
            'contenidos.*.fecha_contenido'      => 'required_with:contenidos|date',
            'contenidos.*.hora_inicio'          => 'nullable|date_format:H:i',
            'contenidos.*.hora_fin'             => 'nullable|date_format:H:i',
            'contenidos.*.descripcion_breve'    => 'nullable|string|max:500',
        ];
    }

    /**
     * Lógica personalizada de validación:
     * - Horas de clase obligatorias y secuenciales
     * - Contenidos dentro del rango de fechas del curso
     * - El rango de fechas lo valida la regla 'fecha_fin.after'
     *
     * Nota: Se eliminaron las reglas de trimestre que bloqueaban la creación
     * (Regla 1 "el trimestre debe coincidir con la fecha de inicio" ahora es
     * informativa; Regla 2 "el curso no puede cruzar trimestres" fue retirada).
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $fechaInicio = $this->input('fecha_inicio');
            $fechaFin = $this->input('fecha_fin');

            if ($fechaInicio && $fechaFin) {
                try {
                    $inicio = Carbon::parse($fechaInicio);
                    $fin = Carbon::parse($fechaFin);
                } catch (\Throwable $e) {
                    // Fecha malformada: ya la reportan las reglas 'date'.
                    // Se omite la lógica de fechas para no forzar un error 500.
                    return;
                }

                // Regla 1 (informativa, NO bloquea): el trimestre solo debería coincidir
                // con el mes de inicio. La sugerencia se muestra en el cliente.
                $trimestreCalculado = (int) ceil($inicio->month / 3);
            }

            // Reglas de contenidos
            foreach ($this->input('contenidos', []) as $index => $contenido) {
                $esEvaluacion = (bool) ($contenido['es_evaluacion'] ?? false);
                $nombreBloque = $contenido['titulo'] ?? 'Bloque ' . ($index + 1);

                // Regla 3: La fecha del contenido debe estar dentro del rango del curso
                $fechaContenido = $contenido['fecha_contenido'] ?? null;
                if (empty($fechaContenido)) {
                    if (isset($inicio, $fin)) {
                        $validator->errors()->add(
                            "contenidos.$index.fecha_contenido",
                            'La fecha de clase es obligatoria para el bloque "' . $nombreBloque . '".'
                        );
                    }
                } elseif (isset($inicio, $fin)) {
                    try {
                        $fc = Carbon::parse($fechaContenido);
                    } catch (\Throwable $e) {
                        continue; // Fecha del contenido malformada: la regla 'date' la reporta
                    }
                    if ($fc->lt($inicio) || $fc->gt($fin)) {
                        $validator->errors()->add(
                            "contenidos.$index.fecha_contenido",
                            'La fecha del contenido "' . $nombreBloque . '" debe estar entre ' . $inicio->format('d/m/Y') . ' y ' . $fin->format('d/m/Y') . '.'
                        );
                    }
                }

                if ($esEvaluacion) {
                    continue; // Las evaluaciones solo tienen fecha de entrega, no horas
                }

                // Regla 4: Las clases deben tener hora de inicio y fin
                if (empty($contenido['hora_inicio'] ?? '')) {
                    $validator->errors()->add("contenidos.$index.hora_inicio", 'La hora de inicio es obligatoria para las clases.');
                }
                if (empty($contenido['hora_fin'] ?? '')) {
                    $validator->errors()->add("contenidos.$index.hora_fin", 'La hora de fin es obligatoria para las clases.');
                }

                // Regla 5: La hora de fin debe ser posterior a la de inicio
                if (isset($contenido['hora_inicio'], $contenido['hora_fin'])
                    && $contenido['hora_inicio'] !== '' && $contenido['hora_fin'] !== ''
                    && $contenido['hora_fin'] <= $contenido['hora_inicio']) {
                    $validator->errors()->add("contenidos.$index.hora_fin", 'La hora de fin debe ser posterior a la hora de inicio.');
                }
            }
        });
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
            'id_actividad_formativa.exists'   => 'La actividad formativa seleccionada no es válida.',
            'id_persona.required'   => 'Debe asignar un facilitador al curso.',
            'id_persona.exists'     => 'El facilitador seleccionado no es válido.',
            'trimestre.required'    => 'El trimestre es obligatorio.',
            'correlativo.required'  => 'El correlativo es obligatorio.',
            'anio.required'         => 'El año es obligatorio.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required'    => 'La fecha de fin es obligatoria.',
            'fecha_fin.after'       => 'La fecha de finalización debe ser posterior a la fecha de inicio.',
            'cantidad_cupos.required' => 'Debe indicar la cantidad de cupos disponibles.',
            'cantidad_cupos.min'      => 'La cantidad de cupos debe ser al menos 1.',
            'telegram.url'          => 'El enlace de Telegram debe ser una URL válida.',
            'foto.image'            => 'La foto de presentación debe ser una imagen.',
            'foto.mimes'            => 'La foto de presentación debe ser un archivo JPG o PNG.',
            'direccion_corta.required' => 'La dirección del curso es obligatoria.',
            'localidades.required_unless' => 'Debe seleccionar al menos una localidad cuando el curso no es de alcance nacional.',
            'contenidos.*.titulo.required_with' => 'Cada contenido debe tener un título.',
            'contenidos.*.fecha_contenido.required_with' => 'Cada bloque de contenido debe tener una fecha de clase.',
            'contenidos.*.fecha_contenido.date' => 'La fecha de clase debe ser una fecha válida.',
            'contenidos.*.hora_inicio.date_format' => 'La hora de inicio debe tener formato HH:MM (24h).',
            'contenidos.*.hora_fin.date_format'    => 'La hora de fin debe tener formato HH:MM (24h).',
        ];
    }
}
