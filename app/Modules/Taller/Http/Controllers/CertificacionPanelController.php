<?php

namespace Modules\Taller\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Entities\Asistencia;
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

        $actividadIds = $actividades->pluck('id_contenido_curso');
        $asistenciasRaw = Asistencia::whereIn('id_contenido_curso', $actividadIds)
            ->where('activa', true)
            ->get();

        $asistenciasPorPersona = $asistenciasRaw
            ->groupBy('id_persona')
            ->map(fn($group) => $group->count());

        $asistenciaDetallePorPersona = $asistenciasRaw
            ->groupBy('id_persona')
            ->map(fn($group) => $group->pluck('id_contenido_curso')->flip()->keys());

        $totalActividades = $actividades->count();

        $calificacionesRaw = DB::table('taller.calificaciones')
            ->where('id_curso', $curso->id_curso)
            ->get();

        $calificacionesPorPersona = $calificacionesRaw->groupBy('id_persona');

        $resumenParticipantes = [];
        foreach ($inscripciones as $inscripcion) {
            $idPersona = $inscripcion->id_persona;

            $asistencias = $asistenciasPorPersona->get($idPersona, 0);
            $porcentajeAsistencia = $totalActividades > 0
                ? round(($asistencias / $totalActividades) * 100, 1)
                : 0;

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
                'asistencias' => $asistencias,
                'totalActividades' => $totalActividades,
                'porcentajeAsistencia' => $porcentajeAsistencia,
                'clasesAsistidas' => $asistenciaDetallePorPersona->get($idPersona, collect()),
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

        $inscripcion->update([
            'certificado_aprobado' => true,
            'certificado_aprobado_por' => auth()->id(),
            'certificado_fecha_aprobacion' => now(),
            'certificado_motivo_denegacion' => null,
        ]);

        try {
            Mail::send('taller::emails.certificacion_aprobada', [
                'inscripcion' => $inscripcion,
            ], function ($email) use ($inscripcion) {
                $email->subject('Certificación de Curso - Aprobada');
                $email->to($inscripcion->persona->user->email);
            });
        } catch (\Exception $e) {
            // No detenemos el proceso si falla el correo
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
            Mail::send('taller::emails.certificacion_rechazada', [
                'inscripcion' => $inscripcion,
            ], function ($email) use ($inscripcion) {
                $email->subject('Certificación de Curso - Observaciones');
                $email->to($inscripcion->persona->user->email);
            });
        } catch (\Exception $e) {
            // No detenemos el proceso si falla el correo
        }

        return back()->with('success', 'Certificación denegada para ' . $inscripcion->persona->nombre_completo);
    }

    private function verificarPermisoGestion(Curso $curso): void
    {
        $personalData = $this->getUsuarioAutenticado()->personalData;
        $esGestor = hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_ASISTENCIA)
            || hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO);
        $esFacilitador = $curso->id_persona == $personalData->id_persona;

        if (!$esGestor && !$esFacilitador) {
            abort(403, 'No tienes permiso para gestionar la certificación de este curso.');
        }
    }
}
