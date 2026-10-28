<?php

namespace Modules\Taller\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Exports\ReporteSatisfaccionExport;
use Modules\Taller\Services\SatisfaccionCursoService;

class SatisfaccionCursoController extends BaseController
{
    protected $satisfaccion;

    public function __construct(SatisfaccionCursoService $satisfaccion)
    {
        $this->satisfaccion = $satisfaccion;
    }

    public function create($id_curso)
    {
        [$curso, $inscripcion] = $this->contexto($id_curso);

        if ($this->satisfaccion->yaRespondio($inscripcion)) {
            return $this->destinoTrasEnviar($curso, $inscripcion)
                ->with('info', 'Ya respondiste la encuesta de este curso.');
        }

        return view('taller::a.CursoSatisfaccionForm', [
            'curso'     => $curso,
            'preguntas' => SatisfaccionCursoService::PREGUNTAS,
            'escala'    => SatisfaccionCursoService::ESCALA,
        ]);
    }

    public function store(Request $request, $id_curso)
    {
        [$curso, $inscripcion] = $this->contexto($id_curso);

        $data = $request->validate([
            'contenido'    => 'required|integer|between:1,5',
            'pedagogico'   => 'required|integer|between:1,5',
            'material'     => 'required|integer|between:1,5',
            'organizacion' => 'required|integer|between:1,5',
        ]);

        // Si ya respondió: ReglaDeNegocioException, y Handler vuelve con el mensaje.
        $this->satisfaccion->registrar($inscripcion, $data);

        return $this->destinoTrasEnviar($curso, $inscripcion)
            ->with('success', '¡Gracias por tu respuesta!');
    }

    public function resultados($id_curso)
    {
        $curso = Curso::findOrFail($id_curso);
        $this->authorize('verSatisfaccion', $curso);

        $estadisticas = $this->satisfaccion->estadisticas($curso);

        return view('taller::a.CursoSatisfaccionResultados', [
            'curso'        => $curso,
            'estadisticas' => $estadisticas,
        ]);
    }

    public function reporte($id_curso)
    {
        $curso = Curso::findOrFail($id_curso);
        $this->authorize('reporteSatisfaccion', $curso);

        $estadisticas = $this->satisfaccion->estadisticas($curso);

        if ($estadisticas === null) {
            return redirect()->route('taller.cursos.satisfaccion.resultados', $curso->crypt_id)
                ->with('info', 'Aún no hay respuestas para generar el reporte.');
        }

        return Excel::download(
            new ReporteSatisfaccionExport($curso, $estadisticas),
            'satisfaccion-'.Str::slug($curso->codigo ?: $curso->nombre).'.xlsx'
        );
    }

    private function contexto($id_curso): array
    {
        $curso = Curso::findOrFail($id_curso);
        $persona = $this->getUsuarioAutenticado()->personalData;

        if ($this->satisfaccion->cursoPermiteEncuesta($curso) === false) {
            abort(404, 'La encuesta no está disponible para este curso.');
        }

        $inscripcion = Inscripcion::where('id_curso', $curso->id_curso)
            ->where('id_persona', $persona->id_persona)
            ->where('estado', Inscripcion::ESTADO_APROBADO)
            ->first();

        if (!$inscripcion) {
            abort(403, 'Solo los participantes con inscripción aprobada pueden responder.');
        }

        return [$curso, $inscripcion];
    }

    private function destinoTrasEnviar(Curso $curso, Inscripcion $inscripcion)
    {
        if ($inscripcion->certificadoAprobado()) {
            return redirect()->route('taller.certificados.descargar', $curso->crypt_id);
        }

        return redirect()->route('taller.cursos.show', $curso->crypt_id);
    }
}
