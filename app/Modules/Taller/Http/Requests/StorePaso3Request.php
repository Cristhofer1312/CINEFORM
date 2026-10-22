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
        $esSalir = $this->input('accion') === 'salir' || $this->filled('guardar_y_salir');

        $reglas = self::reglasBloques();
        if ($esSalir) {
            $reglas['contenidos.*.titulo'] = 'nullable|string|max:255';
            $reglas['contenidos.*.fecha_contenido'] = 'nullable|date';
        }

        return array_merge(
            ['contenidos' => 'nullable|array'],
            $reglas
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
            'contenidos.*.fecha_contenido' => 'required_with:contenidos|date',
            'contenidos.*.hora_inicio' => 'nullable|date_format:H:i',
            'contenidos.*.hora_fin' => 'nullable|date_format:H:i',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('accion') === 'salir' || $this->filled('guardar_y_salir')) {
                return;
            }

            // Rango desde la sesión o fallback a BD si la sesión expiró (UX-01)
            $borrador = session('wizard_curso.datos', []);
            $fechaInicio = $borrador['fecha_inicio'] ?? null;
            $fechaFin = $borrador['fecha_fin'] ?? null;

            if ((! $fechaInicio || ! $fechaFin) && \Illuminate\Support\Facades\Auth::check()) {
                $borradorBD = app(\Modules\Taller\Services\BorradorWizardService::class)->actual(\Illuminate\Support\Facades\Auth::id());
                $p2 = $borradorBD->payload['paso2'] ?? [];
                $fechaInicio = $fechaInicio ?: ($p2['fecha_inicio'] ?? null);
                $fechaFin = $fechaFin ?: ($p2['fecha_fin'] ?? null);
            }

            try {
                self::validarRangoContenidos(
                    $this->input('contenidos', []),
                    $fechaInicio,
                    $fechaFin
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
            $nombreBloque = self::tituloStatic($contenido, (int) $index);

            // Fecha del bloque dentro del rango del curso (paso 2).
            if (empty($contenido['fecha_contenido'])) {
                if ($inicio && $fin) {
                    $errores["contenidos.$index.fecha_contenido"][] =
                        'La fecha de clase es obligatoria para el bloque "'.$nombreBloque.'".';
                }
            } else {
                $fc = self::parseFechaStatic($contenido['fecha_contenido']);
                if ($fc && $inicio && $fin && ($fc->lt($inicio) || $fc->gt($fin))) {
                    $errores["contenidos.$index.fecha_contenido"][] =
                        'La fecha del bloque "'.$nombreBloque.'" debe estar entre '.$inicio->format('d/m/Y').' y '.$fin->format('d/m/Y').'.';
                }
            }

            // Las clases llevan hora de inicio y fin secuenciales.
            if (empty($contenido['hora_inicio'] ?? '')) {
                $errores["contenidos.$index.hora_inicio"][] = 'La hora de inicio es obligatoria para el bloque "'.$nombreBloque.'".';
            }
            if (empty($contenido['hora_fin'] ?? '')) {
                $errores["contenidos.$index.hora_fin"][] = 'La hora de fin es obligatoria para el bloque "'.$nombreBloque.'".';
            }
            if (isset($contenido['hora_inicio'], $contenido['hora_fin'])
                && $contenido['hora_inicio'] !== '' && $contenido['hora_fin'] !== ''
                && $contenido['hora_fin'] <= $contenido['hora_inicio']) {
                $errores["contenidos.$index.hora_fin"][] = 'La hora de fin debe ser posterior a la hora de inicio en el bloque "'.$nombreBloque.'".';
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
            'contenidos.*.fecha_contenido.required_with' => 'Cada bloque de clase debe tener una fecha.',
            'contenidos.*.fecha_contenido.date' => 'La fecha de clase debe ser una fecha válida.',
            'contenidos.*.hora_inicio.date_format' => 'La hora de inicio debe tener formato HH:MM (24h).',
            'contenidos.*.hora_fin.date_format' => 'La hora de fin debe tener formato HH:MM (24h).',
        ];
    }
}
