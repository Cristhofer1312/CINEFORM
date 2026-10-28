<?php

namespace Modules\Taller\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Services\CumplimientoAsistencia;
use App\Constants\SecurityAction;

class CertificacionPanelController extends BaseController
{
    public function index($id_curso)
    {
        $curso = Curso::with([
            'contenidos' => function ($q) {
                $q->orderBy('fecha_contenido', 'asc')
                  ->orderBy('id_contenido_curso', 'asc');
            },
            'inscripciones.persona',
        ])->findOrFail($id_curso);

        $this->verificarPermisoGestion($curso);

        $inscripciones = $curso->inscripciones
            ->where('estado', Inscripcion::ESTADO_APROBADO);

        $actividades = $curso->contenidos->where('es_evaluacion', false);
        $evaluaciones = $curso->contenidos->where('es_evaluacion', true);

        // D3: asistencia al 100 %, contando las faltas justificadas.
        $cumplimiento = app(CumplimientoAsistencia::class)->porInscripcion($curso, $inscripciones);

        $calificacionesRaw = DB::table('taller.calificaciones')
            ->where('id_curso', $curso->id_curso)
            ->get();

        $calificacionesPorPersona = $calificacionesRaw->groupBy('id_persona');

        $resumenParticipantes = [];
        foreach ($inscripciones as $inscripcion) {
            $idPersona = $inscripcion->id_persona;
            $asistencia = $cumplimiento[$inscripcion->id_inscripcion];

            $califs = $calificacionesPorPersona->get($idPersona, collect());
            $puntosObtenidos = 0;
            $ponderacionEvaluada = 0;

            foreach ($evaluaciones as $eval) {
                $calif = $califs->firstWhere('id_contenido_curso', $eval->id_contenido_curso);
                if ($calif && isset($calif->calificacion)) {
                    $puntosObtenidos += ($calif->calificacion * $eval->ponderacion) / 100;
                    $ponderacionEvaluada += $eval->ponderacion;
                }
            }

            $promedio = $ponderacionEvaluada > 0
                ? round(($puntosObtenidos / $ponderacionEvaluada) * 100, 2)
                : null;

            $resumenParticipantes[] = [
                'inscripcion' => $inscripcion,
                'asistencias' => $asistencia['asistidas']->count(),
                'justificadas' => $asistencia['justificadas'],
                'faltas' => $asistencia['faltas'],
                'totalActividades' => $asistencia['total'],
                'porcentajeAsistencia' => $asistencia['porcentaje'],
                'cumpleAsistencia' => $asistencia['cumple'],
                'clasesAsistidas' => $asistencia['asistidas'],
                'promedio' => $promedio,
            ];
        }

        return view('taller::a.CertificacionPanel', compact(
            'curso', 'resumenParticipantes', 'actividades', 'evaluaciones'
        ));
    }

    public function aprobar(Request $request, $id_curso, $id_inscripcion)
    {
        $curso = Curso::findOrFail($id_curso);
        $this->verificarPermisoGestion($curso);

        $inscripcion = Inscripcion::where('id_inscripcion', $id_inscripcion)
            ->where('id_curso', $id_curso)
            ->where('estado', Inscripcion::ESTADO_APROBADO)
            ->firstOrFail();

        // D3: exige el 100 % de asistencia; cada falta debe estar justificada.
        $faltas = app(CumplimientoAsistencia::class)->deInscripcion($curso, $inscripcion)['faltas']->count();
        if ($faltas > 0) {
            return back()->with('error', 'No se puede aprobar la certificación de '.$inscripcion->persona->nombre_completo
                .': tiene '.$faltas.' '.($faltas === 1 ? 'falta' : 'faltas').' sin justificar. Se exige el 100 % de asistencia; justifique cada falta para aprobar.');
        }

        $inscripcion->update([
            'certificado_aprobado' => true,
            'certificado_aprobado_por' => auth()->id(),
            'certificado_fecha_aprobacion' => now(),
            'certificado_motivo_denegacion' => null,
        ]);

        try {
            Mail::queue('taller::emails.certificacion_aprobada', [
                'inscripcion' => $inscripcion,
            ], function ($email) use ($inscripcion) {
                $email->subject('Certificación de Curso - Aprobada');
                $email->to($inscripcion->persona->user->email);
            });
        } catch (\Exception $e) {
            // No detenemos el proceso si falla el correo, pero queda registrado.
            \Illuminate\Support\Facades\Log::error('No se pudo encolar el correo de certificación aprobada', [
                'id_inscripcion' => $inscripcion->id_inscripcion,
                'mensaje' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Certificación aprobada para ' . $inscripcion->persona->nombre_completo);
    }

    public function denegar(Request $request, $id_curso, $id_inscripcion)
    {
        $curso = Curso::findOrFail($id_curso);
        $this->verificarPermisoGestion($curso);

        $request->validate([
            'motivo' => 'required|string|max:500',
        ]);

        $inscripcion = Inscripcion::where('id_inscripcion', $id_inscripcion)
            ->where('id_curso', $id_curso)
            ->where('estado', Inscripcion::ESTADO_APROBADO)
            ->firstOrFail();

        $inscripcion->update([
            'certificado_aprobado' => false,
            'certificado_aprobado_por' => auth()->id(),
            'certificado_fecha_aprobacion' => now(),
            'certificado_motivo_denegacion' => $request->motivo,
        ]);

        try {
            Mail::queue('taller::emails.certificacion_rechazada', [
                'inscripcion' => $inscripcion,
            ], function ($email) use ($inscripcion) {
                $email->subject('Certificación de Curso - Observaciones');
                $email->to($inscripcion->persona->user->email);
            });
        } catch (\Exception $e) {
            // No detenemos el proceso si falla el correo, pero queda registrado.
            \Illuminate\Support\Facades\Log::error('No se pudo encolar el correo de certificación denegada', [
                'id_inscripcion' => $inscripcion->id_inscripcion,
                'mensaje' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Certificación denegada para ' . $inscripcion->persona->nombre_completo);
    }

    private function verificarPermisoGestion(Curso $curso): void
    {
        $this->authorize('certificar', $curso);
    }
}
