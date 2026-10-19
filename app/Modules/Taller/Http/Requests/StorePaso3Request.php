<?php

namespace Modules\Taller\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StorePaso3Request extends FormRequest
{
    /**
     * Paso 3: Temario simple (maquetado, sin evaluaciones).
     *
     * Las evaluaciones/ponderaciones las gestiona el facilitador
     * después (edición/calificación); aquí solo bloques de clase.
     */
    public function authorize()
    {
        return hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::CREAR_CURSO);
    }

    public function rules()
    {
        return array_merge(
            ['contenidos' => 'nullable|array'],
            self::reglasBloques()
        );
    }

    /**
     * Reglas por bloque, reutilizables fuera del Request (validador pre-flight
     * de finalizar(), que no pasa por HTTP ni por sesión).
     */
    public static function reglasBloques(): array
    {
        return [
            'contenidos.*.titulo' => 'required_with:contenidos|string|max:255',
            'contenidos.*.descripcion_breve' => 'nullable|string|max:500',
            'contenidos.*.url_contenido' => 'nullable|url|max:500',
            'contenidos.*.fecha_contenido' => 'nullable|date',
            'contenidos.*.hora_inicio' => 'nullable|date_format:H:i',
            'contenidos.*.hora_fin' => 'nullable|date_format:H:i',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Rango desde la sesión (flujo HTTP normal). finalizar() usa el
            // borrador BD vía validarRangoContenidos() para no depender de ella.
            $borrador = session('wizard_curso.datos', []);

            try {
                self::validarRangoContenidos(
                    $this->input('contenidos', []),
                    $borrador['fecha_inicio'] ?? null,
                    $borrador['fecha_fin'] ?? null
                );
            } catch (\Illuminate\Validation\ValidationException $e) {
                foreach ($e->errors() as $campo => $mensajes) {
                    foreach ((array) $mensajes as $mensaje) {
                        $validator->errors()->add($campo, $mensaje);
                    }
                }
            }
        });
    }

    /**
     * Chequeos cruzados de cada bloque contra el rango del curso.
     * Sin rango (sesión expirada / borrador sin paso 2) no se exige nada
     * aquí: finalizar() ya rechaza el borrador incompleto antes.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function validarRangoContenidos(array $contenidos, $fechaInicio, $fechaFin): void
    {
        $inicio = self::parseFechaStatic($fechaInicio);
        $fin = self::parseFechaStatic($fechaFin);
        $errores = [];

        foreach ($contenidos as $index => $contenido) {
            if (! is_array($contenido)) {
                continue;
            }
            // Fecha del bloque dentro del rango del curso (paso 2).
            $fc = isset($contenido['fecha_contenido']) ? self::parseFechaStatic($contenido['fecha_contenido']) : null;
            if ($fc && $inicio && $fin && ($fc->lt($inicio) || $fc->gt($fin))) {
                $errores["contenidos.$index.fecha_contenido"][] =
                    'La fecha del bloque "'.self::tituloStatic($contenido, (int) $index).'" debe estar entre '.$inicio->format('d/m/Y').' y '.$fin->format('d/m/Y').'.';
            }

            // Las clases llevan hora de inicio y fin secuenciales.
            if (empty($contenido['hora_inicio'] ?? '')) {
                $errores["contenidos.$index.hora_inicio"][] = 'La hora de inicio es obligatoria para las clases.';
            }
            if (empty($contenido['hora_fin'] ?? '')) {
                $errores["contenidos.$index.hora_fin"][] = 'La hora de fin es obligatoria para las clases.';
            }
            if (isset($contenido['hora_inicio'], $contenido['hora_fin'])
                && $contenido['hora_inicio'] !== '' && $contenido['hora_fin'] !== ''
                && $contenido['hora_fin'] <= $contenido['hora_inicio']) {
                $errores["contenidos.$index.hora_fin"][] = 'La hora de fin debe ser posterior a la hora de inicio.';
            }
        }

        if (! empty($errores)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errores);
        }
    }

    private function parseFecha($valor): ?Carbon
    {
        return self::parseFechaStatic($valor);
    }

    private function titulo(array $contenido, int $index): string
    {
        return self::tituloStatic($contenido, $index);
    }

    private static function parseFechaStatic($valor): ?Carbon
    {
        try {
            return $valor ? Carbon::parse($valor) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function tituloStatic(array $contenido, int $index): string
    {
        return $contenido['titulo'] ?? 'Bloque '.($index + 1);
    }

    public function messages()
    {
        return [
            'contenidos.*.titulo.required_with' => 'Cada bloque debe tener un título.',
            'contenidos.*.hora_inicio.date_format' => 'La hora de inicio debe tener formato HH:MM (24h).',
            'contenidos.*.hora_fin.date_format' => 'La hora de fin debe tener formato HH:MM (24h).',
        ];
    }
}
