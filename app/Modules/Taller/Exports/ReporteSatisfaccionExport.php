<?php

namespace Modules\Taller\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Services\SatisfaccionCursoService;

/**
 * Reporte de la encuesta de satisfacción de un curso (D2, RF-60).
 *
 * Usa las mismas estadísticas que la pantalla de resultados, para que el
 * reporte y lo que se ve en pantalla no puedan diferir.
 */
class ReporteSatisfaccionExport implements FromArray, ShouldAutoSize, WithTitle
{
    public function __construct(private Curso $curso, private array $estadisticas) {}

    public function array(): array
    {
        $escala = array_values(SatisfaccionCursoService::ESCALA);

        $filas = [
            ['Reporte de la encuesta de satisfacción'],
            ['Curso', $this->curso->nombre],
            ['Código', $this->curso->codigo],
            ['Generado el', now()->format('d/m/Y H:i')],
            ['Total de respuestas', $this->estadisticas['total']],
            ['Índice general (1 a 5)', $this->estadisticas['indice']],
            [],
            array_merge(['Pregunta', 'Promedio (1 a 5)', 'Respuestas favorables (3 a 5)', '% favorable'], $escala),
        ];

        foreach ($this->estadisticas['preguntas'] as $pregunta) {
            $filas[] = array_merge(
                [$pregunta['titulo'], $pregunta['promedio'], $pregunta['favorables'], $pregunta['porcentaje']],
                array_column($pregunta['distribucion'], 'cantidad')
            );
        }

        return $filas;
    }

    public function title(): string
    {
        return 'Satisfacción';
    }
}
