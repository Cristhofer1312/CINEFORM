<?php

namespace Modules\Taller\Services;

use App\Enums\EstadoCurso;
use App\Exceptions\ReglaDeNegocioException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Entities\ResenaSatisfaccion;

class SatisfaccionCursoService
{
    public const CAMPOS = ['contenido', 'pedagogico', 'material', 'organizacion'];

    public const ESCALA = [
        1 => 'Deficiente',
        2 => 'Regular',
        3 => 'Bueno',
        4 => 'Muy bueno',
        5 => 'Excelente',
    ];

    public const PREGUNTAS = [
        'contenido'    => 'El contenido del curso',
        'pedagogico'   => 'El desempeño pedagógico del facilitador',
        'material'     => 'El material de apoyo',
        'organizacion' => 'La organización del curso',
    ];

    public function cursoPermiteEncuesta(Curso $curso): bool
    {
        return in_array($curso->estado_actual->id_estado ?? 0, [
            EstadoCurso::FINALIZADO->value,
            EstadoCurso::CERRADO->value,
        ], true);
    }

    public function yaRespondio(Inscripcion $inscripcion): bool
    {
        return ResenaSatisfaccion::where('id_curso', $inscripcion->id_curso)
            ->where('id_inscripcion', $inscripcion->id_inscripcion)
            ->exists();
    }

    public function registrar(Inscripcion $inscripcion, array $datos): ResenaSatisfaccion
    {
        if ($this->yaRespondio($inscripcion)) {
            throw new ReglaDeNegocioException('Ya respondiste la encuesta de este curso.');
        }

        try {
            return ResenaSatisfaccion::create([
                'id_curso'       => $inscripcion->id_curso,
                'id_inscripcion' => $inscripcion->id_inscripcion,
                'id_persona'     => $inscripcion->id_persona,
                'contenido'      => $datos['contenido'],
                'pedagogico'     => $datos['pedagogico'],
                'material'       => $datos['material'],
                'organizacion'   => $datos['organizacion'],
            ]);
        } catch (UniqueConstraintViolationException $e) {
            throw new ReglaDeNegocioException('Ya respondiste la encuesta de este curso.', 0, $e);
        }
    }

    public function estadisticas(Curso $curso): ?array
    {
        $agregado = DB::table('taller.resenas_satisfaccion')
            ->where('id_curso', $curso->id_curso)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('AVG(contenido) as avg_contenido')
            ->selectRaw('AVG(pedagogico) as avg_pedagogico')
            ->selectRaw('AVG(material) as avg_material')
            ->selectRaw('AVG(organizacion) as avg_organizacion')
            ->first();

        if ((int) $agregado->total === 0) {
            return null;
        }

        $total = (int) $agregado->total;
        $preguntas = [];
        $sumaPromedios = 0.0;

        foreach (self::CAMPOS as $campo) {
            $distribucion = DB::table('taller.resenas_satisfaccion')
                ->where('id_curso', $curso->id_curso)
                ->selectRaw('CAST(' . $campo . ' AS INTEGER) as valor, COUNT(*) as cantidad')
                ->groupBy('valor')
                ->pluck('cantidad', 'valor');

            $favorables = 0;
            foreach ([3, 4, 5] as $nivel) {
                $favorables += (int) ($distribucion[$nivel] ?? 0);
            }

            $promedio = (float) $agregado->{'avg_' . $campo};
            $sumaPromedios += $promedio;

            $preguntas[$campo] = [
                'titulo'   => self::PREGUNTAS[$campo],
                'promedio' => round($promedio, 2),
                'favorables' => $favorables,
                'porcentaje' => (int) round($favorables * 100 / $total),
                'distribucion' => array_map(
                    fn ($nivel) => [
                        'etiqueta' => self::ESCALA[$nivel],
                        'cantidad' => (int) ($distribucion[$nivel] ?? 0),
                    ],
                    [1, 2, 3, 4, 5]
                ),
            ];
        }

        return [
            'total'     => $total,
            'indice'    => round($sumaPromedios / count(self::CAMPOS), 2),
            'preguntas' => $preguntas,
        ];
    }
}
