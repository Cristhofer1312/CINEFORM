<?php

namespace Modules\Taller\Http\Controllers;

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Modules\Comun\Entities\PersonalData;
use Modules\Taller\Entities\Asistencia;
use Modules\Taller\Entities\AsistenciaToken;
use Modules\Taller\Entities\ContenidoCurso;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;

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
                ->keyBy(fn ($a) => $a->id_contenido_curso.'_'.$a->id_persona);

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
    public function generarToken(Request $request, $curso_id)
    {
        $curso = Curso::findOrFail($curso_id);

        $request->validate([
            'contenido_id' => 'required|integer',
            'duracion_minutos' => 'nullable|integer|min:1|max:1440',
        ]);

        $contenido = ContenidoCurso::where('id_contenido_curso', $request->contenido_id)
            ->where('id_curso', $curso_id)
            ->firstOrFail();

        $this->verificarPermisoGestion($curso);

        if ($contenido->es_evaluacion) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No se pueden generar tokens de asistencia para evaluaciones.'], 422);
            }

            return back()->with('error', 'No se pueden generar tokens de asistencia para evaluaciones.');
        }

        // Verificar si ya existe un token activo
        $tokenExistente = $contenido->tokenActivo();

        if ($tokenExistente) {
            $urlAsistencia = route('taller.asistencia.confirmar', [
                'curso' => $curso->crypt_id,
                'token' => $tokenExistente->token,
            ]);

            $qrUrl = route('taller.asistencia.qr-image', $tokenExistente->token);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'existente' => true,
                    'token' => $tokenExistente->token,
                    'url' => $urlAsistencia,
                    'qr' => $qrUrl,
                    'expira' => $tokenExistente->fecha_expiracion->format('d/m/Y H:i'),
                    'expira_timestamp' => $tokenExistente->fecha_expiracion->timestamp,
                ]);
            }

            return back()->with('success', 'Ya existe un enlace activo para esta actividad.')
                ->with('token_url', $urlAsistencia)
                ->with('token_qr', $qrUrl)
                ->with('token_expira', $tokenExistente->fecha_expiracion->format('d/m/Y H:i'));
        }

        $duracion = (int) $request->input('duracion_minutos', 30);

        // Crear nuevo token
        $token = AsistenciaToken::create([
            'id_contenido_curso' => $contenido->id_contenido_curso,
            'token' => AsistenciaToken::generarToken(),
            'activo' => true,
            'fecha_expiracion' => now()->addMinutes($duracion),
            'creado_por' => auth()->id(),
        ]);

        $urlAsistencia = route('taller.asistencia.confirmar', [
            'curso' => $curso->crypt_id,
            'token' => $token->token,
        ]);

        $qrUrl = route('taller.asistencia.qr-image', $token->token);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'existente' => false,
                'token' => $token->token,
                'url' => $urlAsistencia,
                'qr' => $qrUrl,
                'expira' => $token->fecha_expiracion->format('d/m/Y H:i'),
                'expira_timestamp' => $token->fecha_expiracion->timestamp,
            ]);
        }

        return back()->with('success', 'Token generado correctamente.')
            ->with('token_url', $urlAsistencia)
            ->with('token_qr', $qrUrl)
            ->with('token_expira', $token->fecha_expiracion->format('d/m/Y H:i'));
    }

    /**
     * Desactiva un token de asistencia antes de su expiración.
     */
    public function desactivarToken(Request $request, $curso_id)
    {
        $curso = Curso::findOrFail($curso_id);
        $this->verificarPermisoGestion($curso);

        $request->validate(['token_id' => 'required|integer']);

        $token = AsistenciaToken::where('id_token', $request->token_id)
            ->whereHas('actividad', fn ($q) => $q->where('id_curso', $curso->id_curso))
            ->where('activo', true)->firstOrFail();

        $token->update(['activo' => false]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Enlace desactivado correctamente.']);
        }

        return back()->with('success', 'Enlace desactivado correctamente.');
    }

    /**
     * Retorna TODOS los tokens activos de un curso (Evita el problema N+1 del Frontend).
     */
    public function todosTokensActivos(Request $request, $curso_id)
    {
        $curso = Curso::findOrFail($curso_id);
        $this->verificarPermisoGestion($curso);

        $tokensActivos = AsistenciaToken::whereHas('actividad', function ($q) use ($curso_id) {
            $q->where('id_curso', $curso_id);
        })
            ->where('activo', true)
            ->where('fecha_expiracion', '>', now())
            ->orderByDesc('fecha_expiracion')
            ->get();

        $resultados = $tokensActivos->map(function ($token) use ($curso) {
            $urlAsistencia = route('taller.asistencia.confirmar', [
                'curso' => $curso->crypt_id,
                'token' => $token->token,
            ]);

            return [
                'token_id' => $token->id_token,
                'token' => $token->token,
                'contenido_id' => $token->id_contenido_curso,
                'url' => $urlAsistencia,
                'qr' => route('taller.asistencia.qr-image', $token->token),
                'expira' => $token->fecha_expiracion->format('d/m/Y H:i'),
                'expira_timestamp' => $token->fecha_expiracion->timestamp,
                'actividad' => $token->actividad->titulo ?? 'Actividad',
            ];
        });

        return response()->json([
            'success' => true,
            'tokens' => $resultados,
        ]);
    }

    /**
     * Genera y sirve la imagen QR al vuelo sin guardarla en disco.
     */
    public function qrImage($token)
    {
        $tokenRecord = AsistenciaToken::where('token', $token)
            ->where('activo', true)
            ->where('fecha_expiracion', '>', now())
            ->firstOrFail();
        $curso = Curso::findOrFail($tokenRecord->actividad->id_curso);

        $urlAsistencia = route('taller.asistencia.confirmar', [
            'curso' => $curso->crypt_id,
            'token' => $tokenRecord->token,
        ]);

        $renderer = new ImageRenderer(
            new RendererStyle(250, 4),
            new ImagickImageBackEnd
        );
        $writer = new Writer($renderer);
        $imagenQR = $writer->writeString($urlAsistencia);

        return response($imagenQR)->header('Content-Type', 'image/png');
    }

    /**
     * Anula una asistencia individual.
     */
    public function anular($curso_id, $asistencia_id)
    {
        $curso = Curso::findOrFail($curso_id);
        $this->verificarPermisoGestion($curso);

        $data = request()->validate([
            'motivo' => 'required|string|max:500',
        ]);

        $asistencia = Asistencia::where('id_asistencia', $asistencia_id)
            ->whereHas('actividad', fn ($q) => $q->where('id_curso', $curso->id_curso))
            ->firstOrFail();

        $asistencia->update([
            'activa' => false,
            'anulada_por' => auth()->id(),
            'fecha_anulacion' => now(),
            'motivo_anulacion' => $data['motivo'],
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
            ->whereHas('actividad', fn ($q) => $q->where('id_curso', $curso->id_curso))
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
        if (! $persona) {
            return back()->with('error', 'Participante no encontrado.');
        }

        $inscripcion = Inscripcion::where('id_curso', $curso_id)
            ->where('id_persona', $persona->id_persona)
            ->first();

        if (! $inscripcion) {
            return back()->with('error', 'El participante no está inscrito en este curso.');
        }

        if (! $inscripcion->esAprobado()) {
            return back()->with('error', 'El participante no está formalmente inscrito y admitido en este curso.');
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

        return back()->with('success', 'Asistencia de '.$persona->nombre_completo.' registrada correctamente.');
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

        if (! $tokenRecord) {
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
        if (! $contenido || (int) $contenido->id_curso !== (int) $curso->id_curso) {
            return view('taller::a.AsistenciaExpirada', [
                'mensaje' => 'El enlace de asistencia no corresponde a este curso.',
                'curso' => $curso,
            ]);
        }

        $persona = $this->getUsuarioAutenticado()->personalData;
        if (! $persona) {
            return view('taller::a.AsistenciaExpirada', [
                'mensaje' => 'No se encontraron los datos personales del usuario.',
                'curso' => $curso,
            ]);
        }

        // Verificar que esté formalmente admitido y aprobado (UX-13 / RF-32)
        $inscripcion = Inscripcion::where('id_curso', $curso->id_curso)
            ->where('id_persona', $persona->id_persona)
            ->first();

        if (! $inscripcion || ! $inscripcion->esAprobado()) {
            return view('taller::a.AsistenciaExpirada', [
                'mensaje' => 'Debes estar formalmente inscrito y admitido en este curso para registrar asistencia.',
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

        if (! $tokenRecord) {
            return back()->with('error', 'Token no válido.');
        }

        if ($tokenRecord->fecha_expiracion && $tokenRecord->fecha_expiracion->isPast()) {
            return back()->with('error', 'El enlace de asistencia ha expirado.');
        }

        $contenido = $tokenRecord->actividad;
        if (! $contenido || (int) $contenido->id_curso !== (int) $curso->id_curso) {
            return back()->with('error', 'El enlace de asistencia no corresponde a este curso.');
        }

        $persona = $this->getUsuarioAutenticado()->personalData;
        if (! $persona) {
            return back()->with('error', 'No se encontraron los datos personales del usuario.');
        }

        $inscripcion = Inscripcion::where('id_curso', $curso->id_curso)
            ->where('id_persona', $persona->id_persona)
            ->first();

        if (! $inscripcion || ! $inscripcion->esAprobado()) {
            return back()->with('error', 'Debes estar formalmente inscrito y admitido en este curso para registrar asistencia.');
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
        $this->authorize('gestionarAsistencia', $curso);
    }
}
