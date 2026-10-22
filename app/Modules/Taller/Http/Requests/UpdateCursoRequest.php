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
        $param = $this->route('curso');
        $curso = $param instanceof \Modules\Taller\Entities\Curso
            ? $param
            : \Modules\Taller\Entities\Curso::find($param);

        return $curso ? $this->user()->can('editar', $curso) : false;
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
            'id_modalidad'          => 'sometimes|required|exists:' . \Modules\Taller\Entities\Modalidad::class . ',id_modalidad',
            'id_actividad_formativa'=> 'nullable|exists:' . \Modules\Taller\Entities\ActividadFormativa::class . ',id_actividad_formativa',
            'id_aspecto'            => 'nullable|exists:' . \Modules\Taller\Entities\Aspecto::class . ',id_aspecto',
            'id_modalidad_especial' => 'nullable|exists:' . \Modules\Taller\Entities\ModalidadEspecial::class . ',id_modalidad_especial',
            'id_persona'            => 'sometimes|required|exists:' . \Modules\Comun\Entities\PersonalData::class . ',id_persona',
            'nivel'                 => 'nullable|string|max:50',
            'trimestre'             => 'nullable|integer|min:1|max:4',
            'correlativo'           => 'nullable|integer|min:1',
            'anio'                  => 'nullable|integer|min:2020|max:2100',
            'descripcion'           => 'nullable|string',
            'duracion'              => 'nullable|integer|min:1',
            'horas'                 => 'nullable|integer|min:1',
            'cantidad_cupos'        => 'sometimes|integer|min:1',
            'telegram'              => 'nullable|url|max:255',
            'direccion_corta'       => 'nullable|string|max:150',
            'es_nacional'           => 'nullable|boolean',
            'foto'                  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'fecha_inicio'          => 'sometimes|required|date',
            'fecha_fin'             => 'sometimes|required|date|after:fecha_inicio',
            'localidades'           => 'nullable|array',
            'localidades.*'         => [\Illuminate\Validation\Rule::exists(\Modules\Parametros\Entities\Estados::class, 'id')],

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

    protected function prepareForValidation()
    {
        if ($this->has('fecha_fin') && ! $this->has('fecha_inicio')) {
            $id = $this->route('curso');
            $curso = $id ? \Modules\Taller\Entities\Curso::find($id) : null;
            if ($curso && $curso->fecha_inicio) {
                $this->merge(['fecha_inicio' => $curso->fecha_inicio->format('Y-m-d')]);
            }
        }
        if ($this->has('fecha_inicio') && ! $this->has('fecha_fin')) {
            $id = $this->route('curso');
            $curso = $id ? \Modules\Taller\Entities\Curso::find($id) : null;
            if ($curso && $curso->fecha_fin) {
                $this->merge(['fecha_fin' => $curso->fecha_fin->format('Y-m-d')]);
            }
        }
    }

    /**
     * Las clases deben tener hora de inicio y fin (secuenciales).
     * Las evaluaciones solo conservan la fecha de entrega, sin horas.
     * El rango de fechas lo valida la regla 'fecha_fin.after'.
     *
     * Nota: Se eliminaron las reglas de trimestre que bloqueaban la edición
     * (Regla 1 ahora es informativa; Regla 2 "el curso no puede cruzar
     * trimestres" fue retirada).
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Trimestre vigente del curso (no editable; llega como campo oculto).
            $trimestre = (int) ($this->input('trimestre') ?: $this->cursoTrimestre());
            $fechaInicio = $this->input('fecha_inicio');
            $fechaFin = $this->input('fecha_fin');

            if ((! $fechaInicio || ! $fechaFin) && $this->route('curso')) {
                $curso = \Modules\Taller\Entities\Curso::find($this->route('curso'));
                if ($curso) {
                    $fechaInicio = $fechaInicio ?: ($curso->fecha_inicio ? $curso->fecha_inicio->format('Y-m-d') : null);
                    $fechaFin = $fechaFin ?: ($curso->fecha_fin ? $curso->fecha_fin->format('Y-m-d') : null);
                }
            }

            if ($fechaInicio && $fechaFin) {
                try {
                    $inicio = \Carbon\Carbon::parse($fechaInicio);
                    $fin = \Carbon\Carbon::parse($fechaFin);
                } catch (\Throwable $e) {
                    // Fecha malformada: ya la reportan las reglas 'date'.
                    // Se omite la lógica de fechas para no forzar un error 500.
                    return;
                }

                // Regla 1 (informativa, NO bloquea): el trimestre solo debería coincidir
                // con el mes de inicio. La sugerencia se muestra en el cliente.
                $trimestreCalculado = (int) ceil($inicio->month / 3);

                // Regla 3: La fecha del contenido debe estar dentro del rango del curso
                foreach ($this->input('contenidos', []) as $index => $contenido) {
                    $nombreBloque = $contenido['titulo'] ?? 'Bloque ' . ($index + 1);
                    $fechaContenido = $contenido['fecha_contenido'] ?? null;
                    if (empty($fechaContenido)) {
                        $validator->errors()->add(
                            "contenidos.$index.fecha_contenido",
                            'La fecha de clase es obligatoria para el bloque "' . $nombreBloque . '".'
                        );
                    } else {
                        try {
                            $fc = \Carbon\Carbon::parse($fechaContenido);
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
                }
            }

            foreach ($this->input('contenidos', []) as $index => $contenido) {
                $esEvaluacion = (bool) ($contenido['es_evaluacion'] ?? false);
                if ($esEvaluacion) continue; // Las evaluaciones no llevan horas

                if (empty($contenido['hora_inicio'] ?? '')) {
                    $validator->errors()->add("contenidos.$index.hora_inicio", 'La hora de inicio es obligatoria para las clases.');
                }
                if (empty($contenido['hora_fin'] ?? '')) {
                    $validator->errors()->add("contenidos.$index.hora_fin", 'La hora de fin es obligatoria para las clases.');
                }
                if (isset($contenido['hora_inicio'], $contenido['hora_fin'])
                    && $contenido['hora_inicio'] !== '' && $contenido['hora_fin'] !== ''
                    && $contenido['hora_fin'] <= $contenido['hora_inicio']) {
                    $validator->errors()->add("contenidos.$index.hora_fin", 'La hora de fin debe ser posterior a la hora de inicio.');
                }
            }
        });
    }

    /**
     * Obtiene el trimestre del curso en edición como respaldo.
     *
     * @return int|null
     */
    protected function cursoTrimestre()
    {
        $id = $this->route('curso');
        if ($id) {
            $curso = \Modules\Taller\Entities\Curso::find($id);
            if ($curso) {
                return (int) $curso->trimestre;
            }
        }
        return null;
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
            'fecha_fin.after'       => 'La fecha de finalización debe ser posterior a la fecha de inicio.',
            'cantidad_cupos.min'      => 'La cantidad de cupos debe ser al menos 1.',
            'telegram.url'          => 'El enlace de Telegram debe ser una URL válida.',
            'foto.image'            => 'La foto de presentación debe ser una imagen.',
            'foto.mimes'            => 'La foto de presentación debe ser un archivo JPG o PNG.',
            'contenidos.*.titulo.required_with' => 'Cada contenido debe tener un título.',
            'contenidos.*.fecha_contenido.required_with' => 'Cada bloque de contenido debe tener una fecha de clase.',
            'contenidos.*.fecha_contenido.date' => 'La fecha de clase debe ser una fecha válida.',
            'contenidos.*.hora_inicio.date_format' => 'La hora de inicio debe tener formato HH:MM (24h).',
            'contenidos.*.hora_fin.date_format'    => 'La hora de fin debe tener formato HH:MM (24h).',
        ];
    }
}
