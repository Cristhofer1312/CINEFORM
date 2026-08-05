<?php

namespace Modules\Taller\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\ContenidoCurso;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Entities\Asistencia;
use Modules\Taller\Entities\AsistenciaToken;
use Modules\Taller\Entities\Estado;
use Modules\Comun\Entities\PersonalData;
use Modules\Security\Entities\User;
use App\Constants\SecurityAction;
use App\Enums\EstadoCurso;

class AsistenciaController extends BaseController
{
    /**
     * Vista consolidada: lista todos los participantes con indicadores de asistencia por actividad.
     */
    public function consolidado($curso_id)
    {
        $curso = Curso::with([
            'contenidos' => function ($q) {
                $q->where('es_evaluacion', false)
                  ->orderBy('fecha_contenido', 'asc')
                  ->orderBy('id_contenido_curso', 'asc');
            },
            'inscripciones.persona',
        ])->findOrFail($curso_id);

        $this->verificarPermisoGestion($curso);

        $actividades = $curso->contenidos;

        // Asistencias agrupadas: [id_contenido_curso => [id_persona => asistencia]]
        $asistenciasMap = [];
        if ($actividades->isNotEmpty()) {
            $asistencias = Asistencia::whereIn('id_contenido_curso', $actividades->pluck('id_contenido_curso'))

                ->where('activa', true)
                ->get()
                ->keyBy(fn ($a) => $a->id_contenido_curso . '_' . $a->id_persona);

            $asistenciasMap = $asistencias->toArray();
        }

        // Token activo actual (para QR)
        $tokenActivo = $curso->contenidos->first()?->tokenActivo();

        return view('taller::a.AsistenciaConsolidado', compact(
            'curso', 'actividades', 'asistenciasMap', 'tokenActivo'
        ));
    }

    /**
     * Vista individual: muestra asistencias de un participante específico en un curso.
     */
    public function individual($curso_id, $inscripcion_id)
    {
        $curso = Curso::with([
            'contenidos' => function ($q) {
                $q->orderBy('fecha_contenido', 'asc');
            },
        ])->findOrFail($curso_id);

        $inscripcion = Inscripcion::with('persona')
            ->where('id_inscripcion', $inscripcion_id)
            ->where('id_curso', $curso_id)
            ->firstOrFail();

        $this->verificarPermisoGestion($curso);

        $contenidoIds = $curso->contenidos->pluck('id_contenido_curso');
        $asistencias = Asistencia::whereIn('id_contenido_curso', $contenidoIds)
            ->where('id_persona', $inscripcion->id_persona)
            ->get()
            ->keyBy('id_contenido_curso');

        $totalActividades = $curso->contenidos->where('es_evaluacion', false)->count();
        $totalAsistencias = $asistencias->where('activa', true)->count();
        $porcentajeAsistencia = $totalActividades > 0
            ? round(($totalAsistencias / $totalActividades) * 100, 1)
            : 0;

        return view('taller::a.AsistenciaIndividual', compact(
            'curso', 'inscripcion', 'asistencias', 'totalActividades', 'totalAsistencias', 'porcentajeAsistencia'
        ));
    }

    /**
     * Genera un token temporal para una actividad específica.
     * Retorna el enlace y código QR.
     */
    public function generarToken(Request $request, $curso_id, $contenido_id)
    {
        $curso = Curso::findOrFail($curso_id);
        $contenido = ContenidoCurso::where('id_contenido_curso', $contenido_id)
            ->where('id_curso', $curso_id)
            ->firstOrFail();

        $this->verificarPermisoGestion($curso);

        if ($contenido->es_evaluacion) {
            return back()->with('error', 'No se pueden generar tokens de asistencia para evaluaciones.');
        }

        $persona = $this->getUsuarioAutenticado()->personalData;
        $duracion = $request->input('duracion_minutos', 30);

        // Desactivar tokens anteriores de esta actividad
        AsistenciaToken::where('id_contenido_curso', $contenido_id)
            ->where('activo', true)
            ->update(['activo' => false]);

        // Crear nuevo token
        $token = AsistenciaToken::create([
            'id_contenido_curso' => $contenido_id,
            'token' => AsistenciaToken::generarToken(),
            'activo' => true,
            'fecha_expiracion' => now()->addMinutes($duracion),
            'creado_por' => auth()->id(),
        ]);

        $urlAsistencia = route('taller.asistencia.marcar', [
            'curso' => $curso->crypt_id,
            'token' => $token->token,
        ]);

        // QR via api.qrserver.com (consistente con patrón de certificados)
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($urlAsistencia);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'token' => $token->token,
                'url' => $urlAsistencia,
                'qr' => $qrUrl,
                'expira' => $token->fecha_expiracion->format('d/m/Y H:i'),
            ]);
        }

        return back()->with('success', 'Token generado correctamente.')
            ->with('token_url', $urlAsistencia)
            ->with('token_qr', $qrUrl)
            ->with('token_expira', $token->fecha_expiracion->format('d/m/Y H:i'));
    }

    /**
     * Anula una asistencia individual.
     */
    public function anular($curso_id, $asistencia_id)
    {
        $curso = Curso::findOrFail($curso_id);
        $this->verificarPermisoGestion($curso);

        $asistencia = Asistencia::where('id_asistencia', $asistencia_id)
            ->firstOrFail();

        $asistencia->update([
            'activa' => false,
            'anulada_por' => auth()->id(),
            'fecha_anulacion' => now(),
            'motivo_anulacion' => request('motivo', 'Anulación manual'),
        ]);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Asistencia anulada.']);
        }

        return back()->with('success', 'Asistencia anulada correctamente.');
    }

    /**
     * Restaura una asistencia anulada.
     */
    public function restaurar($curso_id, $asistencia_id)
    {
        $curso = Curso::findOrFail($curso_id);
        $this->verificarPermisoGestion($curso);

        $asistencia = Asistencia::where('id_asistencia', $asistencia_id)
            ->firstOrFail();

        $asistencia->update([
            'activa' => true,
            'anulada_por' => null,
            'fecha_anulacion' => null,
            'motivo_anulacion' => null,
        ]);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Asistencia restaurada.']);
        }

        return back()->with('success', 'Asistencia restaurada correctamente.');
    }

    /**
     * Marca asistencia manual para un participante (por facilitador/gestor).
     */
    public function marcarManual(Request $request, $curso_id, $contenido_id)
    {
        $curso = Curso::findOrFail($curso_id);
        $this->verificarPermisoGestion($curso);

        $request->validate([
            'id_persona' => 'required|integer',
        ]);

        $contenido = ContenidoCurso::where('id_contenido_curso', $contenido_id)
            ->where('id_curso', $curso_id)
            ->firstOrFail();

        if ($contenido->es_evaluacion) {
            return back()->with('error', 'No se puede marcar asistencia en una evaluación.');
        }

        $persona = PersonalData::find($request->id_persona);
        if (!$persona) {
            return back()->with('error', 'Participante no encontrado.');
        }

        $inscripcion = Inscripcion::where('id_curso', $curso_id)
            ->where('id_persona', $persona->id_persona)
            ->first();

        if (!$inscripcion) {
            return back()->with('error', 'El participante no está inscrito en este curso.');
        }

        // Verificar si ya existe asistencia
        $existe = Asistencia::where('id_contenido_curso', $contenido_id)
            ->where('id_persona', $persona->id_persona)
            ->first();

        if ($existe) {
            if ($existe->activa) {
                return back()->with('info', 'El participante ya tiene asistencia registrada para esta actividad.');
            }
            // Reactivar si estaba anulada
            $existe->update([
                'activa' => true,
                'anulada_por' => null,
                'fecha_anulacion' => null,
                'motivo_anulacion' => null,
            ]);
        } else {
            Asistencia::create([
                'id_contenido_curso' => $contenido_id,
                'id_persona' => $persona->id_persona,
                'id_inscripcion' => $inscripcion->id_inscripcion,
                'fecha_hora_marcado' => now(),
                'activa' => true,
                'metodo_marcado' => 'manual',
                'ip_marcado' => $request->ip(),
            ]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Asistencia registrada correctamente.']);
        }

        return back()->with('success', 'Asistencia de ' . $persona->nombre_completo . ' registrada correctamente.');
    }

    /**
     * Marca asistencia vía link/QR (requiere autenticación).
     * Flujo: participante abre enlace → login si no autenticado → redirect intencionado → llega aquí → confirmar → marcar.
     */
    public function mostrarConfirmacion($curso, $token)
    {
        $curso = Curso::with('contenidos')->findOrFail($curso);

        $tokenRecord = AsistenciaToken::where('token', $token)
            ->where('activo', true)
            ->first();

        if (!$tokenRecord) {
            return view('taller::a.AsistenciaExpirada', [
                'mensaje' => 'El enlace de asistencia no es válido o ya fue desactivado.',
                'curso' => $curso,
            ]);
        }

        if ($tokenRecord->fecha_expiracion && $tokenRecord->fecha_expiracion->isPast()) {
            return view('taller::a.AsistenciaExpirada', [
                'mensaje' => 'El enlace de asistencia ha expirado. Solicita un nuevo enlace al facilitador.',
                'curso' => $curso,
            ]);
        }

        $contenido = $tokenRecord->actividad;
        $persona = $this->getUsuarioAutenticado()->personalData;

        // Verificar que esté inscrito
        $inscripcion = Inscripcion::where('id_curso', $curso->id_curso)
            ->where('id_persona', $persona->id_persona)
            ->first();

        if (!$inscripcion) {
            return view('taller::a.AsistenciaExpirada', [
                'mensaje' => 'No estás inscrito en este curso. No puedes marcar asistencia.',
                'curso' => $curso,
            ]);
        }

        // Verificar si ya marcó
        $yaMarco = Asistencia::where('id_contenido_curso', $contenido->id_contenido_curso)
            ->where('id_persona', $persona->id_persona)
            ->where('activa', true)
            ->exists();

        if ($yaMarco) {
            return view('taller::a.AsistenciaExpirada', [
                'mensaje' => 'Ya has registrado tu asistencia para esta actividad.',
                'curso' => $curso,
            ]);
        }

        return view('taller::a.AsistenciaConfirmar', compact('curso', 'contenido', 'tokenRecord'));
    }

    /**
     * Procesa el marcado de asistencia desde la pantalla de confirmación.
     */
    public function marcar(Request $request, $curso, $token)
    {
        $curso = Curso::findOrFail($curso);

        $tokenRecord = AsistenciaToken::where('token', $token)
            ->where('activo', true)
            ->first();

        if (!$tokenRecord) {
            return back()->with('error', 'Token no válido.');
        }

        if ($tokenRecord->fecha_expiracion && $tokenRecord->fecha_expiracion->isPast()) {
            return back()->with('error', 'El enlace de asistencia ha expirado.');
        }

        $contenido = $tokenRecord->actividad;
        $persona = $this->getUsuarioAutenticado()->personalData;

        $inscripcion = Inscripcion::where('id_curso', $curso->id_curso)
            ->where('id_persona', $persona->id_persona)
            ->first();

        if (!$inscripcion) {
            return back()->with('error', 'No estás inscrito en este curso.');
        }

        // Verificar duplicado
        $existe = Asistencia::where('id_contenido_curso', $contenido->id_contenido_curso)
            ->where('id_persona', $persona->id_persona)
            ->where('activa', true)
            ->exists();

        if ($existe) {
            return back()->with('info', 'Ya tienes asistencia registrada para esta actividad.');
        }

        Asistencia::create([
            'id_contenido_curso' => $contenido->id_contenido_curso,
            'id_persona' => $persona->id_persona,
            'id_inscripcion' => $inscripcion->id_inscripcion,
            'fecha_hora_marcado' => now(),
            'activa' => true,
            'metodo_marcado' => 'link',
            'ip_marcado' => $request->ip(),
        ]);

        return view('taller::a.AsistenciaExitosa', compact('curso', 'contenido'));
    }

    /**
     * Verifica que el usuario tenga permiso de gestión para el curso.
     */
    private function verificarPermisoGestion(Curso $curso): void
    {
        $personalData = $this->getUsuarioAutenticado()->personalData;
        $esGestor = hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_ASISTENCIA)
            || hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO);
        $esFacilitador = $curso->id_persona == $personalData->id_persona;

        if (!$esGestor && !$esFacilitador) {
            abort(403, 'No tienes permiso para gestionar la asistencia de este curso.');
        }
    }
}
