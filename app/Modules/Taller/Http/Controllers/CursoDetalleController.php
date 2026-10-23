<?php

namespace Modules\Taller\Http\Controllers;

use App\Constants\SecurityAction;
use Illuminate\Support\Facades\DB;
use Modules\Comun\Entities\PersonalData;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Services\CondicionalEstadoCurso;

/**
 * Controlador: CursoDetalleController
 */
class CursoDetalleController extends BaseController
{
    public function show($id)
    {
        $id = $this->decryptId($id);
        $condicional = new CondicionalEstadoCurso;

        $curso = Curso::with([
            'modalidad',
            'contenidos' => function ($query) {
                $query->orderBy('fecha_contenido', 'asc')->orderBy('id_contenido_curso', 'asc');
            },
            'inscripciones.persona',
            'persona.user',
            'estados',
            'observaciones.autor',
        ])->findOrFail($id);

        $datosUsuario = $this->obtenerDatosUsuario();
        $datosCurso = $this->calcularDatosCurso($curso, $datosUsuario);

        $user = auth()->user();
        $puedeInscribirse = $user ? $user->can('inscribirse', [Inscripcion::class, $curso]) : false;
        $puedeCancelarInscripciones = hasPermissionRoute('taller.cursos.index', SecurityAction::CANCELAR_INSCRIPCION);
        $puedeVerParticipantes = $user ? $user->can('verParticipantes', $curso) : false;

        // El administrador (gestor) NO actúa como facilitador ni como participante.
        // Solo ve herramientas de gestión y acceso a contenidos como revisor.
        $inscripcion = $datosCurso['inscripcion'];
        $esParticipante = $inscripcion !== null && (
            ($curso->estado_actual->id_estado ?? 0) === \App\Enums\EstadoCurso::INSCRIPCION->value
                ? true
                : $inscripcion->esAprobado()
        );
        $esOperativo = $datosCurso['esFacilitador'];

        if ($datosUsuario['esGestor']) {
            $esParticipante = false;
            $esOperativo = false;
            $puedeInscribirse = false;
        }

        $capacidades = $condicional->obtenerCapacidades(
            $curso->estado_actual->id_estado ?? 0,
            $esParticipante,
            $esOperativo,
            $datosUsuario['esGestor'],
            $datosCurso['CuposDisponibles'],
            $puedeInscribirse,
            $puedeVerParticipantes
        );

        $satisfaccion = app(\Modules\Taller\Services\SatisfaccionCursoService::class);

        $contextoSatisfaccion = [
            'disponible' => $satisfaccion->cursoPermiteEncuesta($curso) && $datosCurso['inscripcion'] !== null && $datosCurso['inscripcion']->esAprobado(),
            'respondida' => ($datosCurso['inscripcion'] && $datosCurso['inscripcion']->esAprobado()) ? $satisfaccion->yaRespondio($datosCurso['inscripcion']) : false,
        ];

        $facilitadores = [];
        if ($datosUsuario['esGestor']) {
            $facilitadores = app(\App\Services\Security\Autorizacion::class)
                ->consultaElegiblesFacilitador()
                ->with('personalData')
                ->get();
        }

        return view('taller::a.CursoDetalle', array_merge(
            compact('curso', 'capacidades', 'puedeCancelarInscripciones', 'puedeVerParticipantes', 'contextoSatisfaccion', 'facilitadores'),
            $datosUsuario,
            $datosCurso
        ));
    }

    public function participantes($id)
    {
        $curso = Curso::with([
            'inscripciones.persona',
            'inscripciones.rechazadoPor',
            'persona.user',
            'estados',
            'requisitos',
        ])->findOrFail($id);

        $this->authorize('verParticipantes', $curso);

        $puedeCancelarInscripciones = hasPermissionRoute('taller.cursos.index', SecurityAction::CANCELAR_INSCRIPCION);

        // Categorización por Workflow de Postulación
        $postulados = $curso->inscripciones->where('estado', Inscripcion::ESTADO_POSTULADO);
        $aprobados = $curso->inscripciones->where('estado', Inscripcion::ESTADO_APROBADO);
        $rechazados = $curso->inscripciones->where('estado', Inscripcion::ESTADO_RECHAZADO);
        $denegados = $curso->inscripciones->where('estado', Inscripcion::ESTADO_DENEGADO);

        return view('taller::a.CursoParticipantes', compact(
            'curso',
            'puedeCancelarInscripciones',
            'postulados',
            'aprobados',
            'rechazados',
            'denegados'
        ));
    }

    private function obtenerDatosUsuario(): array
    {
        if (! auth()->check()) {
            return [
                'user' => null,
                'idPersona' => null,
                'esGestor' => false,
            ];
        }

        $user = auth()->user();
        $personalData = PersonalData::where('user_id', $user->id)->first();
        $idPersona = $personalData ? $personalData->id_persona : null;

        return [
            'user' => $user,
            'idPersona' => $idPersona,
            'esGestor' => hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO),
            'personalData' => $personalData,
        ];
    }

    private function calcularDatosCurso(Curso $curso, array $datosUsuario): array
    {
        $idPersona = $datosUsuario['idPersona'];
        $esFacilitador = $idPersona && $curso->id_persona == $idPersona;

        $inscripcion = $idPersona
            ? Inscripcion::where('id_curso', $curso->id_curso)
                ->where('id_persona', $idPersona)
                ->first()
            : null;

        $cuposDisponibles = $curso->cantidad_cupos !== null
            ? max(0, (int) $curso->cantidad_cupos - $curso->inscripcionesActivas->count())
            : null;

        $datosPromedio = $this->calcularPromedioEstudiante($curso, $idPersona, $inscripcion);

        return [
            'esFacilitador' => $esFacilitador,
            'inscripcion' => $inscripcion,
            'CuposDisponibles' => $cuposDisponibles,
            'puntosObtenidos' => $datosPromedio['puntosObtenidos'],
            'ponderacionEvaluada' => $datosPromedio['ponderacionEvaluada'],
            'promedioEstudiante' => $datosPromedio['promedioEstudiante'],
            'debeMostrarPromedio' => $datosPromedio['debeMostrarPromedio'],
        ];
    }

    private function calcularPromedioEstudiante(Curso $curso, ?int $idPersona, $inscripcion): array
    {
        $estadosPromedio = [7, 8, 9];
        $estadoId = $curso->estado_actual->id_estado ?? 0;
        $debeMostrarPromedio = in_array($estadoId, $estadosPromedio);

        $datosPromedio = [
            'puntosObtenidos' => 0,
            'ponderacionEvaluada' => 0,
            'promedioEstudiante' => 0,
            'debeMostrarPromedio' => $debeMostrarPromedio,
        ];

        if (! $inscripcion || ! $inscripcion->esAprobado() || ! $debeMostrarPromedio || ! $idPersona) {
            return $datosPromedio;
        }

        $calificacionesEstudiante = DB::table('taller.calificaciones')
            ->where('id_persona', $idPersona)
            ->where('id_curso', $curso->id_curso)
            ->get()
            ->keyBy('id_contenido_curso');

        $puntosObtenidos = 0;
        $ponderacionEvaluada = 0;

        foreach ($curso->contenidos as $contenido) {
            if (! $contenido->es_evaluacion) {
                continue;
            }

            $calificacion = $calificacionesEstudiante->get($contenido->id_contenido_curso);
            if ($calificacion && isset($calificacion->calificacion)) {
                $puntosObtenidos += ($calificacion->calificacion * $contenido->ponderacion) / 100;
                $ponderacionEvaluada += $contenido->ponderacion;
            }
        }

        $promedioEstudiante = $ponderacionEvaluada > 0 ? ($puntosObtenidos / $ponderacionEvaluada) * 100 : 0;

        return [
            'puntosObtenidos' => $puntosObtenidos,
            'ponderacionEvaluada' => $ponderacionEvaluada,
            'promedioEstudiante' => round($promedioEstudiante, 2),
            'debeMostrarPromedio' => $debeMostrarPromedio,
        ];
    }
}
