<?php

namespace Modules\Taller\Http\Controllers;

use App\Enums\EstadoCurso;
use Illuminate\Http\Request;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Entities\Curso;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Taller\Http\Controllers\BaseController;

class InscripcionController extends BaseController
{
    /**
     * Muestra el formulario de inscripción con los requisitos
     */
    public function create($id_curso)
    {
        $curso = Curso::with('requisitos')->findOrFail($id_curso);

        // Verificar permiso formal de inscripción
        if (!hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::INSCRIBIRSE_CURSO)) {
            abort(403, 'No tienes los permisos necesarios para inscribirte en este programa.');
        }

        $user = $this->getUsuarioAutenticado();

        if ($this->usuarioSinDatosPersonales()) {
            return redirect()->back()->with('error', 'No se encontraron los datos personales del usuario.');
        }

        // Verificar si ya está inscrito
        $inscripcionExistente = Inscripcion::where('id_curso', $curso->id_curso)
            ->where('id_persona', $user->personalData->id_persona)
            ->first();

        if ($inscripcionExistente) {
            if ($inscripcionExistente->esRechazada()) {
                return redirect()->back()->with('error', 'Tu inscripción fue rechazada o retirada previamente por un administrador. Si consideras que es un error, por favor contacta a la coordinación.');
            }
            return redirect()->back()->with('error', 'Ya estás inscrito en este curso.');
        }

        // Verificar cupos
        $inscritos = Inscripcion::activas()->where('id_curso', $curso->id_curso)->count();
        if ($curso->cantidad_cupos !== null && $inscritos >= (int) $curso->cantidad_cupos) {
            return redirect()->back()->with('error', 'No hay cupos disponibles para este curso.');
        }

        $datos = array("nombre" => $user->personalData->first_name);
        $subject = 'Inscripcion Exitosa';
        $for = [$user->personalData->email];
        \Mail::send('security::users.correo', $datos, function ($email) use ($subject, $for) {
            $email->subject($subject);
            $email->to($for);
            /*$email->attachData($pdfcontent, "Certificado.pdf", [
                'mime' => 'application/pdf',

            ]);*/
        });
        return view('taller::a.CursoInscribirse', compact('curso'));
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
        if (!hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::INSCRIBIRSE_CURSO)) {
            return redirect()->back()->with('error', 'No tienes los permisos necesarios para inscribirte en este programa.');
        }

        if ($this->usuarioSinDatosPersonales()) {
            return redirect()->back()->with('error', 'No se encontraron los datos personales del usuario.');
        }

        // Validar cupos y estado
        $estadoActualId = $curso->estado_actual?->id_estado ?? $curso->id_estado;
        if ($estadoActualId != EstadoCurso::INSCRIPCION->value) {
            return redirect()->back()->with('error', 'El curso no se encuentra en estado de inscripción.');
        }

        $inscritos = Inscripcion::activas()->where('id_curso', $curso->id_curso)->count();
        if ($curso->cantidad_cupos !== null && $inscritos >= (int) $curso->cantidad_cupos) {
            return redirect()->back()->with('error', 'No hay cupos disponibles para este curso.');
        }

        $cedula = $user->personalData->dni;
        $actividadSlug = $curso->actividadFormativa ? preg_replace('/[^a-zA-Z0-9]+/', '_', $curso->actividadFormativa->nombre) : 'Taller';
        $codigoProcinec = $curso->codigo;

        $basePath = "{$actividadSlug}/{$codigoProcinec}/{$cedula}";

        DB::beginTransaction();
        try {
            // Crear inscripción principal
            $inscripcion = Inscripcion::create([
                'id_curso' => $curso->id_curso,
                'id_persona' => $user->personalData->id_persona,
                'fecha_inscripcion' => Carbon::now(),
                'estado' => 'activa'
            ]);

            // Procesar respuestas
            foreach ($curso->requisitos as $req) {
                if ($req->tipo === 'pregunta') {
                    $key = 'req_' . $req->id_requisito;
                    if ($req->obligatorio && !$request->filled($key)) {
                        throw new \Exception("La pregunta '{$req->titulo}' es obligatoria.");
                    }

                    if ($request->filled($key)) {
                        \Modules\Taller\Entities\InscripcionRespuesta::create([
                            'id_inscripcion' => $inscripcion->id_inscripcion,
                            'id_requisito' => $req->id_requisito,
                            'respuesta_texto' => $request->input($key)
                        ]);
                    }
                } elseif ($req->tipo === 'documento') {
                    $key = 'req_' . $req->id_requisito;
                    if ($req->obligatorio && !$request->hasFile($key)) {
                        throw new \Exception("El documento '{$req->titulo}' es obligatorio.");
                    }

                    if ($request->hasFile($key)) {
                        $file = $request->file($key);

                        // Validar archivo
                        if (!$file->isValid()) {
                            throw new \Exception("El archivo '{$req->titulo}' es inválido o no se pudo cargar.");
                        }

                        $safeFileName = preg_replace('/[^a-zA-Z0-9_.-]+/', '_', $req->titulo);
                        $extension = $file->getClientOriginalExtension();
                        $finalFileName = "{$safeFileName}.{$extension}";

                        $path = $file->storeAs("public/{$basePath}", $finalFileName);

                        \Modules\Taller\Entities\InscripcionRespuesta::create([
                            'id_inscripcion' => $inscripcion->id_inscripcion,
                            'id_requisito' => $req->id_requisito,
                            'ruta_archivo' => str_replace('public/', '', $path)
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('taller.cursos.show', $curso->id_curso)
                ->with('success', 'Te has inscrito exitosamente en el programa.');

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
    public function destroy($id)
    {
        $user = $this->getUsuarioAutenticado();

        $inscripcion = Inscripcion::findOrFail($id);
        $curso = $inscripcion->curso;
        $estadoActual = $curso->estado_actual->id_estado ?? null;

        // ── Rama Administrativa: usuario con permiso de cancelar inscripciones ──
        $puedeCancelarInscripciones = hasPermissionRoute(
            'taller.cursos.index',
            \App\Constants\SecurityAction::CANCELAR_INSCRIPCION
        );

        if ($puedeCancelarInscripciones) {
            $estadosPermitidos = [
                EstadoCurso::INSCRIPCION->value,
                EstadoCurso::EN_CURSO->value,
            ];

            if (!in_array($estadoActual, $estadosPermitidos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden retirar participantes en el estado actual del curso.',
                ], 403);
            }

            $nombreParticipante = $inscripcion->persona
                ? ($inscripcion->persona->primer_nombre . ' ' . $inscripcion->persona->primer_apellido)
                : 'Participante';

            // En lugar de eliminar, marcamos como rechazada
            $inscripcion->update([
                'estado' => 'rechazada',
                'rechazada_por' => $user->id,
                'fecha_rechazo' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'message' => "La inscripción de {$nombreParticipante} ha sido rechazada/retirada.",
                'cupos_restantes' => $curso->cantidad_cupos !== null
                    ? max(0, (int) $curso->cantidad_cupos - Inscripcion::activas()->where('id_curso', $curso->id_curso)->count())
                    : null
            ]);
        }

        // ── Rama Normal: el participante cancela su propia inscripción ──
        if ($estadoActual != EstadoCurso::INSCRIPCION->value) {
            return response()->json([
                'success' => false,
                'message' => 'El curso ya ha iniciado, no se puede gestionar el cupo.',
                'data' => $curso
            ], 403);
        }

        if ($this->usuarioSinDatosPersonales()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron los datos personales del usuario. Por favor, complete su perfil primero.'
            ], 404);
        }

        if ($inscripcion->id_persona != $user->personalData->id_persona) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizada para cancelar esta inscripción.'
            ], 403);
        }

        $inscripcion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inscripción cancelada correctamente.',
            'cupos_restantes' => $curso->cantidad_cupos !== null
                ? max(0, (int) $curso->cantidad_cupos - Inscripcion::activas()->where('id_curso', $curso->id_curso)->count())
                : null
        ]);
    }

    /**
     * Rehabilitar una inscripción rechazada
     */
    public function rehabilitar($id)
    {
        $inscripcion = Inscripcion::findOrFail($id);
        $curso = $inscripcion->curso;

        // Verificar permisos
        $puedeCancelarInscripciones = hasPermissionRoute(
            'taller.cursos.index',
            \App\Constants\SecurityAction::CANCELAR_INSCRIPCION
        );

        if (!$puedeCancelarInscripciones) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para rehabilitar participantes.'
            ], 403);
        }

        if (!$inscripcion->esRechazada()) {
            return response()->json([
                'success' => false,
                'message' => 'La inscripción no está rechazada.'
            ], 400);
        }

        // Verificar cupos disponibles antes de rehabilitar
        $inscritos = Inscripcion::activas()->where('id_curso', $curso->id_curso)->count();
        if ($curso->cantidad_cupos !== null && $inscritos >= (int) $curso->cantidad_cupos) {
            return response()->json([
                'success' => false,
                'message' => 'No hay cupos disponibles en el curso para rehabilitar esta inscripción.'
            ], 400);
        }

        // Rehabilitar
        $inscripcion->update([
            'estado' => 'activa',
            'rechazada_por' => null,
            'fecha_rechazo' => null
        ]);

        $nombreParticipante = $inscripcion->persona
            ? ($inscripcion->persona->primer_nombre . ' ' . $inscripcion->persona->primer_apellido)
            : 'Participante';

        return response()->json([
            'success' => true,
            'message' => "La inscripción de {$nombreParticipante} ha sido rehabilitada correctamente.",
            'cupos_restantes' => $curso->cantidad_cupos !== null
                ? max(0, (int) $curso->cantidad_cupos - Inscripcion::activas()->where('id_curso', $curso->id_curso)->count())
                : null
        ]);
    }

    /**
     * Muestra las respuestas de un participante a los requisitos del curso
     */
    public function verRespuestas($id_curso, $id_inscripcion)
    {
        $curso = Curso::findOrFail($id_curso);
        $inscripcion = Inscripcion::with(['respuestas.requisito', 'persona'])->findOrFail($id_inscripcion);

        // Validar permisos (Gestores, o Facilitador del curso)
        $esGestor = hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::GESTIONAR_CURSO);
        $esFacilitador = $curso->id_persona == optional($this->getUsuarioAutenticado()->personalData)->id_persona;

        if (!$esGestor && !$esFacilitador) {
            abort(403, 'No tienes permisos para ver estos documentos.');
        }

        return view('taller::a.CursoParticipantesRespuestas', compact('curso', 'inscripcion'));
    }

    /**
     * Fuerza la descarga del documento adjunto
     */
    public function descargarDocumento($id_respuesta)
    {
        $respuesta = \Modules\Taller\Entities\InscripcionRespuesta::findOrFail($id_respuesta);

        $inscripcion = Inscripcion::findOrFail($respuesta->id_inscripcion);
        $curso = Curso::findOrFail($inscripcion->id_curso);

        $esGestor = hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::GESTIONAR_CURSO);
        $esFacilitador = $curso->id_persona == optional($this->getUsuarioAutenticado()->personalData)->id_persona;

        if (!$esGestor && !$esFacilitador) {
            abort(403, 'No tienes permisos para descargar este documento.');
        }

        if (!$respuesta->ruta_archivo || !\Illuminate\Support\Facades\Storage::disk('public')->exists($respuesta->ruta_archivo)) {
            abort(404, 'El archivo no existe o fue eliminado.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($respuesta->ruta_archivo);
    }
}
