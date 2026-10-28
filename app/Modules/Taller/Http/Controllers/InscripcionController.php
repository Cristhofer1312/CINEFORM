<?php

namespace Modules\Taller\Http\Controllers;

use App\Enums\EstadoCurso;
use App\Services\Security\Auditoria;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\CursoRequisito;
use Modules\Taller\Entities\Inscripcion;

class InscripcionController extends BaseController
{
    /**
     * Muestra el formulario de inscripción con los requisitos
     */
    public function create($id_curso)
    {
        $curso = Curso::with(['requisitos', 'actividadFormativa'])->findOrFail($id_curso);

        $this->authorize('inscribirse', [Inscripcion::class, $curso]);

        $user = $this->getUsuarioAutenticado();

        if ($this->usuarioSinDatosPersonales()) {
            return redirect()->route('taller.cursos.show', $curso->id_curso)
                ->with('error', 'No se encontraron los datos personales del usuario.');
        }

        // Verificar si ya cuenta con postulación previa
        $inscripcionExistente = Inscripcion::with('respuestas')
            ->where('id_curso', $curso->id_curso)
            ->where('id_persona', $user->personalData->id_persona)
            ->first();

        if ($inscripcionExistente) {
            if ($inscripcionExistente->esAprobado()) {
                return redirect()->route('taller.cursos.show', $curso->id_curso)
                    ->with('info', 'Ya te encuentras formalmente inscrito y admitido en este curso.');
            }
            if ($inscripcionExistente->esPostulado()) {
                return redirect()->route('taller.cursos.show', $curso->id_curso)
                    ->with('info', 'Tu postulación ya ha sido enviada y está en proceso de revisión.');
            }
            if ($inscripcionExistente->esDenegado()) {
                return redirect()->route('taller.cursos.show', $curso->id_curso)
                    ->with('error', 'Tu postulación a este curso fue denegada de forma definitiva.');
            }
            if ($inscripcionExistente->esRetirado()) {
                return redirect()->route('taller.cursos.show', $curso->id_curso)
                    ->with('error', 'Fuiste retirado de este curso por la coordinación.');
            }
            // Si $inscripcionExistente->esRechazado(), se le permite continuar a la vista para corregir postulación (UX-11).
        }

        // Verificar cupos si es inscripción nueva (una postulación rechazada que se corrige ya fue evaluada)
        if (! $inscripcionExistente || ! $inscripcionExistente->esRechazado()) {
            $inscritos = Inscripcion::activas()->where('id_curso', $curso->id_curso)->count();
            if ($curso->cantidad_cupos !== null && $inscritos >= (int) $curso->cantidad_cupos) {
                return redirect()->route('taller.cursos.show', $curso->id_curso)
                    ->with('error', 'No hay cupos disponibles para este curso.');
            }
        }

        return view('taller::a.CursoInscribirse', compact('curso', 'inscripcionExistente'));
    }

    /**
     * Store a newly created resource in storage (Legacy - maintained for backwards compatibility if needed, but not actively used for new flow).
     */
    public function store(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Por favor, utiliza el nuevo formulario de inscripción.'], 400);
    }

    /**
     * Procesa la inscripción con los requisitos
     */
    public function procesarInscripcion(Request $request, $id_curso)
    {
        $curso = Curso::with(['requisitos', 'actividadFormativa'])->findOrFail($id_curso);
        $user = $this->getUsuarioAutenticado();

        // Validaciones básicas de negocio...
        $this->authorize('inscribirse', [Inscripcion::class, $curso]);

        if ($this->usuarioSinDatosPersonales()) {
            return redirect()->back()->with('error', 'No se encontraron los datos personales del usuario.');
        }

        // Validar cupos y estado
        $estadoActualId = $curso->estado_actual?->id_estado ?? 0;
        if ($estadoActualId != EstadoCurso::INSCRIPCION->value) {
            return redirect()->back()->with('error', 'El curso no se encuentra en estado de inscripción.');
        }

        $existente = Inscripcion::with('respuestas')
            ->where('id_curso', $curso->id_curso)
            ->where('id_persona', $user->personalData->id_persona)
            ->first();

        // Si ya tiene una postulación y no está en estado rechazado, bloquear duplicados
        if ($existente && ! $existente->esRechazado()) {
            return redirect()->route('taller.cursos.show', $curso->id_curso)
                ->with('error', 'Ya cuentas con una postulación activa en este programa.');
        }

        if (! $existente || ! $existente->esRechazado()) {
            $inscritos = Inscripcion::activas()->where('id_curso', $curso->id_curso)->count();
            if ($curso->cantidad_cupos !== null && $inscritos >= (int) $curso->cantidad_cupos) {
                return redirect()->back()->with('error', 'No hay cupos disponibles para este curso.');
            }
        }

        $cedula = $user->personalData->dni;
        $actividadSlug = $curso->actividadFormativa ? preg_replace('/[^a-zA-Z0-9]+/', '_', $curso->actividadFormativa->nombre) : 'Taller';
        $codigoProcinec = $curso->codigo;

        $basePath = "{$actividadSlug}/{$codigoProcinec}/{$cedula}";

        DB::beginTransaction();
        try {
            if ($existente && $existente->esRechazado()) {
                // Modo Corrección (UX-11): reutilizar y actualizar la postulación existente
                $inscripcion = $existente;
                $inscripcion->estado = Inscripcion::ESTADO_POSTULADO;
                $inscripcion->fecha_inscripcion = Carbon::now();
                $inscripcion->motivo_estado = null;
                $inscripcion->save();
                $esCorreccion = true;
            } else {
                // Modo Creación: nueva postulación
                $inscripcion = Inscripcion::create([
                    'id_curso' => $curso->id_curso,
                    'id_persona' => $user->personalData->id_persona,
                    'fecha_inscripcion' => Carbon::now(),
                    'estado' => Inscripcion::ESTADO_POSTULADO,
                ]);
                $esCorreccion = false;
            }

            // Procesar respuestas. La carta de permiso solo cuenta en cursos
            // dirigidos a menores; si quedó una fila antigua en un curso para
            // adultos, se ignora en lugar de bloquear la inscripción.
            $esParaMenores = $curso->esParaMenores();
            $respuestasPrevias = $inscripcion->respuestas->keyBy('id_requisito');

            foreach ($curso->requisitos as $req) {
                if (! $esParaMenores && CursoRequisito::esCartaPermisoDocumento($req)) {
                    continue;
                }

                $key = 'req_'.$req->id_requisito;
                $respPrevia = $respuestasPrevias->get($req->id_requisito);

                if ($req->tipo === 'pregunta') {
                    if ($req->obligatorio && ! $request->filled($key) && (! $respPrevia || empty($respPrevia->respuesta_texto))) {
                        throw new \Exception("La pregunta '{$req->titulo}' es obligatoria.");
                    }

                    if ($request->filled($key)) {
                        \Modules\Taller\Entities\InscripcionRespuesta::updateOrCreate(
                            [
                                'id_inscripcion' => $inscripcion->id_inscripcion,
                                'id_requisito' => $req->id_requisito,
                            ],
                            [
                                'respuesta_texto' => $request->input($key),
                            ]
                        );
                    }
                } elseif ($req->tipo === 'documento') {
                    $tieneArchivoPrevio = $respPrevia && ! empty($respPrevia->ruta_archivo);

                    if ($req->obligatorio && ! $request->hasFile($key) && ! $tieneArchivoPrevio) {
                        throw new \Exception("El documento '{$req->titulo}' es obligatorio.");
                    }

                    if ($request->hasFile($key)) {
                        $file = $request->file($key);

                        // Validar archivo seguro (RNF-03 / RF-30)
                        if (! $file->isValid()) {
                            throw new \Exception("El archivo '{$req->titulo}' es inválido o no se pudo cargar.");
                        }

                        $validator = Validator::make(
                            [$key => $file],
                            [$key => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx']
                        );

                        if ($validator->fails()) {
                            throw new \Exception("El archivo '{$req->titulo}' es inválido: ".implode(' ', $validator->errors()->all()));
                        }

                        // Limpiar archivo previo si es corrección y se sube uno nuevo
                        if ($tieneArchivoPrevio && Storage::disk('public')->exists($respPrevia->ruta_archivo)) {
                            Storage::disk('public')->delete($respPrevia->ruta_archivo);
                        }

                        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
                        $finalFileName = Str::uuid().".{$extension}";

                        $path = $file->storeAs($basePath, $finalFileName, 'public');

                        \Modules\Taller\Entities\InscripcionRespuesta::updateOrCreate(
                            [
                                'id_inscripcion' => $inscripcion->id_inscripcion,
                                'id_requisito' => $req->id_requisito,
                            ],
                            [
                                'ruta_archivo' => $path,
                            ]
                        );
                    }
                }
            }

            DB::commit();

            $mensajeExito = $esCorreccion
                ? 'Tu postulación ha sido corregida y enviada nuevamente para revisión.'
                : 'Te has postulado exitosamente en el programa.';

            return redirect()->route('taller.cursos.show', $curso->id_curso)
                ->with('success', $mensajeExito);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * Permite cancelar inscripciones en dos contextos:
     * 1. El participante cancela su propia inscripción (solo en estado Inscripción).
     * 2. Un usuario con permiso CANCELAR_INSCRIPCION retira a cualquier participante
     *    (en estados Inscripción o En Curso).
     */
    public function destroy(Request $request, $id)
    {
        $user = $this->getUsuarioAutenticado();

        $inscripcion = Inscripcion::findOrFail($id);
        $this->authorize('cancelar', $inscripcion);

        $curso = $inscripcion->curso;
        $estadoActual = $curso->estado_actual->id_estado ?? null;

        // ── Rama Administrativa: gestor con permiso de cancelar/rehabilitar inscripciones ──
        $puedeCancelarInscripciones = $user->can('rehabilitar', $inscripcion);

        if ($puedeCancelarInscripciones) {
            $estadosPermitidos = [
                EstadoCurso::INSCRIPCION->value,
                EstadoCurso::EN_CURSO->value,
            ];

            if (! in_array($estadoActual, $estadosPermitidos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden retirar participantes en el estado actual del curso.',
                ], 403);
            }

            $nombreParticipante = $inscripcion->persona
                ? ($inscripcion->persona->primer_nombre.' '.$inscripcion->persona->primer_apellido)
                : 'Participante';

            // El retiro del gestor exige motivo (RF-27)
            $data = $request->validate([
                'motivo' => 'required|string|max:500',
            ]);

            // En lugar de eliminar, se marca como retirada (D6): no corregible por
            // el participante, pero revertible por quien gestiona.
            $inscripcion->update([
                'estado' => Inscripcion::ESTADO_RETIRADO,
                'rechazada_por' => $user->id,
                'fecha_rechazo' => Carbon::now(),
                'motivo_estado' => $data['motivo'],
            ]);

            return response()->json([
                'success' => true,
                'message' => "La inscripción de {$nombreParticipante} ha sido retirada.",
                'cupos_restantes' => $curso->cantidad_cupos !== null
                    ? max(0, (int) $curso->cantidad_cupos - Inscripcion::activas()->where('id_curso', $curso->id_curso)->count())
                    : null,
            ]);
        }

        // ── Rama Normal: el participante cancela su propia inscripción ──
        if ($estadoActual != EstadoCurso::INSCRIPCION->value) {
            return response()->json([
                'success' => false,
                'message' => 'El curso ya ha iniciado, no se puede gestionar el cupo.',
                'data' => $curso,
            ], 403);
        }

        if ($this->usuarioSinDatosPersonales()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron los datos personales del usuario. Por favor, complete su perfil primero.',
            ], 404);
        }

        if ($inscripcion->id_persona != $user->personalData->id_persona) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizada para cancelar esta inscripción.',
            ], 403);
        }

        foreach ($inscripcion->respuestas as $resp) {
            if ($resp->ruta_archivo && Storage::disk('public')->exists($resp->ruta_archivo)) {
                Storage::disk('public')->delete($resp->ruta_archivo);
            }
        }

        $inscripcion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inscripción cancelada correctamente.',
            'cupos_restantes' => $curso->cantidad_cupos !== null
                ? max(0, (int) $curso->cantidad_cupos - Inscripcion::activas()->where('id_curso', $curso->id_curso)->count())
                : null,
        ]);
    }

    /**
     * Rehabilitar una inscripción rechazada
     */
    public function rehabilitar($id)
    {
        $inscripcion = Inscripcion::findOrFail($id);
        $this->authorize('rehabilitar', $inscripcion);
        $curso = $inscripcion->curso;

        if (! $inscripcion->esRecuperable()) {
            return response()->json([
                'success' => false,
                'message' => 'La inscripción no es recuperable.',
            ], 400);
        }

        // Verificar cupos disponibles antes de rehabilitar
        $inscritos = Inscripcion::activas()->where('id_curso', $curso->id_curso)->count();
        if ($curso->cantidad_cupos !== null && $inscritos >= (int) $curso->cantidad_cupos) {
            return response()->json([
                'success' => false,
                'message' => 'No hay cupos disponibles en el curso para rehabilitar esta inscripción.',
            ], 400);
        }

        // Rehabilitar
        $inscripcion->update([
            'estado' => Inscripcion::ESTADO_APROBADO,
            'rechazada_por' => null,
            'fecha_rechazo' => null,
            'motivo_estado' => null,
        ]);

        $nombreParticipante = $inscripcion->persona
            ? ($inscripcion->persona->primer_nombre.' '.$inscripcion->persona->primer_apellido)
            : 'Participante';

        return response()->json([
            'success' => true,
            'message' => "La inscripción de {$nombreParticipante} ha sido rehabilitada correctamente.",
            'cupos_restantes' => $curso->cantidad_cupos !== null
                ? max(0, (int) $curso->cantidad_cupos - Inscripcion::activas()->where('id_curso', $curso->id_curso)->count())
                : null,
        ]);
    }

    /**
     * Aprueba una postulación y la convierte en inscripción activa
     */
    public function aprobar($id)
    {
        $inscripcion = $this->findInscripcionOperativa($id);
        $this->autorizarGestionPostulacion($inscripcion);
        $curso = $inscripcion->curso;

        if (! $inscripcion->esPostulado()) {
            return response()->json([
                'success' => false,
                'message' => 'La postulación no está en estado postulado.',
            ], 400);
        }

        $inscritos = Inscripcion::activas()->where('id_curso', $curso->id_curso)->count();
        if ($curso->cantidad_cupos !== null && $inscritos >= (int) $curso->cantidad_cupos) {
            return response()->json([
                'success' => false,
                'message' => 'No hay cupos disponibles para aprobar esta postulación.',
            ], 409);
        }

        $inscripcion->update([
            'estado' => Inscripcion::ESTADO_APROBADO,
            'motivo_estado' => null,
            'rechazada_por' => null,
            'fecha_rechazo' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Postulación aprobada.',
            'cupos_restantes' => $curso->cantidad_cupos !== null
                ? max(0, (int) $curso->cantidad_cupos - Inscripcion::activas()->where('id_curso', $curso->id_curso)->count())
                : null,
        ]);
    }

    /**
     * Rechaza una postulación para que el participante pueda corregirla
     */
    public function rechazar(Request $request, $id)
    {
        $inscripcion = $this->findInscripcionOperativa($id);
        $this->autorizarGestionPostulacion($inscripcion);

        $data = $request->validate([
            'motivo' => 'required|string|max:500',
        ]);

        if ($inscripcion->esAprobado()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede rechazar una inscripción aprobada; use la acción de retiro.',
            ], 400);
        }

        // Pasar a "rechazado" dejaría al participante volver a postularse: una
        // denegación o un retiro solo se deshace con la acción de revertir.
        if ($inscripcion->esRevertible()) {
            return response()->json([
                'success' => false,
                'message' => 'La postulación está denegada o retirada; use la acción de revertir.',
            ], 400);
        }

        $inscripcion->update([
            'estado' => Inscripcion::ESTADO_RECHAZADO,
            'motivo_estado' => $data['motivo'],
            'rechazada_por' => auth()->id(),
            'fecha_rechazo' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Postulación enviada a subsanar.',
        ]);
    }

    /**
     * Deniega definitivamente una postulación
     */
    public function denegar(Request $request, $id)
    {
        $inscripcion = $this->findInscripcionOperativa($id);
        $this->autorizarGestionPostulacion($inscripcion);

        $data = $request->validate([
            'motivo' => 'required|string|max:500',
        ]);

        if ($inscripcion->esAprobado()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede denegar una inscripción aprobada.',
            ], 400);
        }

        if ($inscripcion->esRetirado()) {
            return response()->json([
                'success' => false,
                'message' => 'La inscripción fue retirada; use la acción de revertir.',
            ], 400);
        }

        $inscripcion->update([
            'estado' => Inscripcion::ESTADO_DENEGADO,
            'motivo_estado' => $data['motivo'],
            'rechazada_por' => auth()->id(),
            'fecha_rechazo' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Postulación rechazada definitivamente.',
        ]);
    }

    /**
     * Revierte una denegación o un retiro (D6).
     *
     * - Denegada en revisión → vuelve a "postulado" para revisarla de nuevo.
     *   La puede revertir quien gestiona postulaciones del curso.
     * - Retirada durante el curso → vuelve a "aprobado" si hay cupo.
     *   La puede revertir quien puede retirar participantes.
     */
    public function revertir(Request $request, $id)
    {
        $inscripcion = $this->findInscripcionOperativa($id);

        // Se autoriza antes de mirar el estado para no revelarlo a quien no gestiona.
        $this->authorize($inscripcion->esRetirado() ? 'rehabilitar' : 'gestionar', $inscripcion);

        if (! $inscripcion->esRevertible()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede revertir una postulación denegada o una inscripción retirada.',
            ], 400);
        }

        $data = $request->validate([
            'motivo' => 'required|string|max:500',
        ]);

        $curso = $inscripcion->curso;
        $estadoCurso = (int) ($curso->estado_actual->id_estado ?? 0);
        if (! in_array($estadoCurso, [EstadoCurso::INSCRIPCION->value, EstadoCurso::EN_CURSO->value], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede revertir mientras el curso está en Inscripción o En Curso.',
            ], 400);
        }

        $destino = $inscripcion->esRetirado() ? Inscripcion::ESTADO_APROBADO : Inscripcion::ESTADO_POSTULADO;

        if ($destino === Inscripcion::ESTADO_APROBADO) {
            $inscritos = Inscripcion::activas()->where('id_curso', $curso->id_curso)->count();
            if ($curso->cantidad_cupos !== null && $inscritos >= (int) $curso->cantidad_cupos) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay cupos disponibles para reincorporar al participante.',
                ], 409);
            }
        }

        $antes = $inscripcion->only(['estado', 'motivo_estado', 'rechazada_por', 'fecha_rechazo']);

        DB::transaction(function () use ($inscripcion, $destino, $antes, $data) {
            $inscripcion->update([
                'estado' => $destino,
                'motivo_estado' => null,
                'rechazada_por' => null,
                'fecha_rechazo' => null,
            ]);

            app(Auditoria::class)->registrar(
                'inscripcion.revertir',
                'taller.inscripciones',
                $inscripcion->id_inscripcion,
                $antes,
                ['estado' => $destino],
                $data['motivo']
            );
        });

        return response()->json([
            'success' => true,
            'message' => $destino === Inscripcion::ESTADO_APROBADO
                ? 'Retiro revertido: el participante vuelve a estar inscrito.'
                : 'Rechazo definitivo revertido: la postulación vuelve a revisión.',
        ]);
    }

    /**
     * Fuerza la visualización del documento adjunto de un requisito
     */
    public function verDocumento($id_respuesta)
    {
        $respuesta = \Modules\Taller\Entities\InscripcionRespuesta::findOrFail($id_respuesta);

        if (empty($respuesta->ruta_archivo) || ! \Illuminate\Support\Facades\Storage::disk('public')->exists($respuesta->ruta_archivo)) {
            abort(404, 'Documento no encontrado.');
        }

        $inscripcion = $respuesta->inscripcion;
        if (! $inscripcion) {
            abort(404, 'Inscripción no encontrada.');
        }

        $this->authorize('verDocumento', $inscripcion);

        // inline: el visor lo carga en un iframe; con download() el navegador lo descargaba.
        return \Illuminate\Support\Facades\Storage::disk('public')->response($respuesta->ruta_archivo);
    }

    /**
     * Muestra las respuestas de un participante a los requisitos del curso
     */
    public function verRespuestas($id_curso, $id_inscripcion)
    {
        $curso = Curso::findOrFail($id_curso);
        $inscripcion = Inscripcion::with(['respuestas.requisito', 'persona'])->findOrFail($id_inscripcion);

        $this->authorize('ver', $inscripcion);

        return view('taller::a.CursoParticipantesRespuestas', compact('curso', 'inscripcion'));
    }

    /**
     * Fuerza la descarga del documento adjunto
     */
    public function descargarDocumento($id_respuesta)
    {
        $respuesta = \Modules\Taller\Entities\InscripcionRespuesta::findOrFail($id_respuesta);

        $inscripcion = Inscripcion::findOrFail($respuesta->id_inscripcion);
        $this->authorize('verDocumento', $inscripcion);

        if (! $respuesta->ruta_archivo || ! \Illuminate\Support\Facades\Storage::disk('public')->exists($respuesta->ruta_archivo)) {
            abort(404, 'El archivo no existe o fue eliminado.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($respuesta->ruta_archivo);
    }

    private function findInscripcionOperativa($id)
    {
        return Inscripcion::with('curso')->findOrFail($id);
    }

    private function autorizarGestionPostulacion(Inscripcion $inscripcion): void
    {
        $this->authorize('gestionar', $inscripcion);
    }
}
