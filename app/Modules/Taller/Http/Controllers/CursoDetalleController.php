<?php

namespace Modules\Taller\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Modules\Comun\Entities\PersonalData;
use Modules\Taller\Services\CondicionalEstadoCurso;
use App\Constants\SecurityAction;

/**
 * Controlador: CursoDetalleController
 */
class CursoDetalleController extends BaseController
{
    public function show($id)
    {
        $condicional = new CondicionalEstadoCurso();

        $curso = Curso::with([
            'modalidad',
            'contenidos' => function ($query) {
                $query->orderBy('fecha_contenido', 'asc')->orderBy('id_contenido_curso', 'asc');
            },
            'inscripciones.persona',
            'persona.user',
            'estados',
            'observaciones.autor'
        ])->findOrFail($id);

        $datosUsuario = $this->obtenerDatosUsuario();
        $datosCurso = $this->calcularDatosCurso($curso, $datosUsuario);

        $puedeInscribirse = hasPermissionRoute('taller.cursos.index', SecurityAction::INSCRIBIRSE_CURSO);
        $puedeCancelarInscripciones = hasPermissionRoute('taller.cursos.index', SecurityAction::CANCELAR_INSCRIPCION);
        $puedeVerParticipantes = hasPermissionRoute('taller.cursos.index', SecurityAction::VER_PARTICIPANTES);

        // El administrador (gestor) NO actúa como facilitador ni como participante.
        // Solo ve herramientas de gestión y acceso a contenidos como revisor.
        $esParticipante  = $datosCurso['inscripcion'] !== null;
        $esOperativo     = $datosCurso['esFacilitador'];

        if ($datosUsuario['esGestor']) {
            $esParticipante   = false;
            $esOperativo      = false;
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

        return view('taller::a.CursoDetalle', array_merge(
            compact('curso', 'capacidades', 'puedeCancelarInscripciones', 'puedeVerParticipantes'),
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
            'requisitos'
        ])->findOrFail($id);

        $puedeVerParticipantes = hasPermissionRoute('taller.cursos.index', SecurityAction::VER_PARTICIPANTES);

        if (!$puedeVerParticipantes) {
            abort(403, 'No tienes permiso para ver los participantes de este curso.');
        }

        $puedeCancelarInscripciones = hasPermissionRoute('taller.cursos.index', SecurityAction::CANCELAR_INSCRIPCION);

        // Categorización por Workflow de Postulación
        $postulados = $curso->inscripciones->where('estado', Inscripcion::ESTADO_POSTULADO);
        $aprobados  = $curso->inscripciones->where('estado', Inscripcion::ESTADO_APROBADO);
        $rechazados = $curso->inscripciones->where('estado', Inscripcion::ESTADO_RECHAZADO);
        $denegados  = $curso->inscripciones->where('estado', Inscripcion::ESTADO_DENEGADO);

        // Soporte para estados legacy si existen
        $inscripcionesActivas = $aprobados->merge($curso->inscripciones->where('estado', 'activa'));
        $inscripcionesRechazadas = $denegados->merge($curso->inscripciones->where('estado', 'rechazada'));

        return view('taller::a.CursoParticipantes', compact(
            'curso', 
            'puedeCancelarInscripciones',
            'postulados',
            'aprobados',
            'rechazados',
            'denegados',
            'inscripcionesActivas',
            'inscripcionesRechazadas'
        ));
    }

    private function obtenerDatosUsuario(): array
    {
        if (!auth()->check()) {
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
            ? max(0, (int)$curso->cantidad_cupos - $curso->inscripcionesActivas->count()) 
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

        if (!$inscripcion || !$debeMostrarPromedio || !$idPersona) {
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
            if (!$contenido->es_evaluacion) continue;

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