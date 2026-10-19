<?php

namespace Modules\Taller\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Taller\Http\Requests\StorePaso1Request;
use Modules\Taller\Http\Requests\StorePaso2Request;
use Modules\Taller\Http\Requests\StorePaso3Request;

/**
 * Re-validación pre-flight del borrador antes de finalizar().
 *
 * Los slices se guardaron paso a paso (pueden ser viejos o estar
 * manipulados); finalizar() no debe confiar en empty() ni en lo que
 * quedó en sesión. Reutiliza las rules de StorePaso1/2/3 + requisitos
 * y deriva trimestre/anio igual que StorePaso2Request.
 */
class CursoBorradorValidator
{
    /**
     * @param  array  $payload  payload crudo del borrador (paso1, paso2, contenidos, requisitos)
     * @return array ['datos' => [...], 'requisitos' => [...]]
     *
     * @throws ValidationException
     */
    public function validarParaFinalizar(array $payload): array
    {
        $paso1 = $payload['paso1'] ?? [];
        $paso2 = $payload['paso2'] ?? [];
        $contenidos = $payload['contenidos'] ?? [];
        $requisitos = $payload['requisitos'] ?? [];

        // Paso 1: identidad y clasificación PROCINEC.
        $datosPaso1 = Validator::make(
            is_array($paso1) ? $paso1 : [],
            (new StorePaso1Request)->rules()
        )->validate();

        // Paso 2: logística. Deriva trimestre/anio igual que el Request.
        $datosPaso2 = Validator::make(
            is_array($paso2) ? $paso2 : [],
            (new StorePaso2Request)->rules()
        )->validate();
        $datosPaso2 = $this->derivarTrimestreAnio($datosPaso2);

        // Paso 3: temario. El rango se toma del paso 2 (no de la sesión,
        // que puede haber expirado aunque el borrador siga vivo).
        $datosContenidos = Validator::make(
            ['contenidos' => is_array($contenidos) ? $contenidos : []],
            ['contenidos' => 'nullable|array'] + StorePaso3Request::reglasBloques()
        )->validate();
        StorePaso3Request::validarRangoContenidos(
            $datosContenidos['contenidos'] ?? [],
            $datosPaso2['fecha_inicio'] ?? null,
            $datosPaso2['fecha_fin'] ?? null
        );

        // Paso 5a: requisitos (mismas reglas que wizardStore/store).
        $datosRequisitos = Validator::make(
            ['requisitos' => is_array($requisitos) ? $requisitos : []],
            [
                'requisitos' => 'nullable|array',
                'requisitos.*.tipo' => 'required|in:pregunta,recurso,documento',
                'requisitos.*.titulo' => 'required|string|max:255',
                'requisitos.*.descripcion' => 'nullable|string|max:1000',
                'requisitos.*.obligatorio' => 'nullable|boolean',
            ]
        )->validate();

        $datos = array_merge($datosPaso1, $datosPaso2, [
            'contenidos' => $datosContenidos['contenidos'] ?? [],
        ]);

        return [
            'datos' => $datos,
            'requisitos' => $datosRequisitos['requisitos'] ?? [],
        ];
    }

    /**
     * Replica StorePaso2Request::validated(): trimestre desde fecha_inicio,
     * anio por defecto el año de inicio.
     */
    private function derivarTrimestreAnio(array $datos): array
    {
        if (! empty($datos['fecha_inicio'])) {
            try {
                $inicio = Carbon::parse($datos['fecha_inicio']);
                $datos['trimestre'] = (int) ceil($inicio->month / 3);
                if (empty($datos['anio'])) {
                    $datos['anio'] = $inicio->year;
                }
            } catch (\Throwable $e) {
                // La regla 'date' ya reportó el error; no se deriva nada.
            }
        }

        return $datos;
    }
}
