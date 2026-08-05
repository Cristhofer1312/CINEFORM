<?php

namespace Modules\Taller\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Modules\Taller\Entities\PostulacionFacilitador;
use Modules\Taller\Entities\PostulacionRespuesta;
use Modules\Taller\Entities\RequisitoFacilitador;
use Modules\Taller\Http\Requests\PostulacionFacilitadorRequest;
use Modules\Taller\Http\Requests\StoreRequisitoRequest;

class PostulacionFacilitadorController extends BaseController
{
    /**
     * Landing informativo para participantes
     */
    public function landing()
    {
        $user = $this->getUsuarioAutenticado();
        $persona = $user->personalData;

        $requisitos = RequisitoFacilitador::activos()->orderBy('orden')->get();

        $postulacionPendiente = null;
        $ultimaPostulacionRechazada = null;

        if ($persona) {
            $postulacionPendiente = PostulacionFacilitador::where('id_persona', $persona->id_persona)
                ->where('estado', PostulacionFacilitador::ESTADO_PENDIENTE)
                ->first();

            $ultimaPostulacionRechazada = PostulacionFacilitador::where('id_persona', $persona->id_persona)
                ->where('estado', PostulacionFacilitador::ESTADO_RECHAZADA)
                ->latest('fecha_revision')
                ->first();
        }

        return view('taller::a.PostulacionFacilitadorLanding', compact(
            'requisitos',
            'postulacionPendiente',
            'ultimaPostulacionRechazada'
        ));
    }

    /**
     * Formulario de postulación
     */
    public function formulario()
    {
        $user = $this->getUsuarioAutenticado();

        if ($this->usuarioSinDatosPersonales()) {
            return redirect()->route('taller.postulacion-facilitador.landing')
                ->with('error', 'Debes completar tus datos personales antes de postularte.');
        }

        $persona = $user->personalData;

        $postulacionPendiente = PostulacionFacilitador::where('id_persona', $persona->id_persona)
            ->where('estado', PostulacionFacilitador::ESTADO_PENDIENTE)
            ->first();

        if ($postulacionPendiente) {
            return redirect()->route('taller.postulacion-facilitador.landing')
                ->with('info', 'Ya tienes una postulación en revisión. Espera la respuesta del coordinador.');
        }

        $requisitos = RequisitoFacilitador::activos()->orderBy('orden')->get();

        return view('taller::a.PostulacionFacilitadorFormulario', compact('requisitos'));
    }

    /**
     * Procesar la postulación
     */
    public function postular(PostulacionFacilitadorRequest $request)
    {
        $user = $this->getUsuarioAutenticado();
        $persona = $user->personalData;
        $cedula = $persona->dni;

        DB::beginTransaction();
        try {
            // Verificar que no tenga postulación pendiente
            $existente = PostulacionFacilitador::where('id_persona', $persona->id_persona)
                ->where('estado', PostulacionFacilitador::ESTADO_PENDIENTE)
                ->first();

            if ($existente) {
                DB::rollBack();
                return redirect()->back()->withInput()->with('error', 'Ya tienes una postulación pendiente.');
            }

            // Crear postulación
            $postulacion = PostulacionFacilitador::create([
                'id_persona' => $persona->id_persona,
                'estado' => PostulacionFacilitador::ESTADO_PENDIENTE,
            ]);

            // Procesar respuestas
            $requisitos = RequisitoFacilitador::activos()->get();

            foreach ($requisitos as $req) {
                $key = 'req_' . $req->id_requisito_facilitador;

                if ($req->tipo === 'recurso') {
                    // Los recursos son informativos, no requieren respuesta
                    continue;
                }

                if ($req->tipo === 'pregunta') {
                    if ($req->obligatorio && !$request->filled($key)) {
                        throw new \Exception("La pregunta '{$req->titulo}' es obligatoria.");
                    }

                    if ($request->filled($key)) {
                        PostulacionRespuesta::create([
                            'id_postulacion' => $postulacion->id_postulacion,
                            'id_requisito_facilitador' => $req->id_requisito_facilitador,
                            'respuesta_texto' => $request->input($key),
                        ]);
                    }
                } elseif ($req->tipo === 'documento') {
                    if ($req->obligatorio && !$request->hasFile($key)) {
                        throw new \Exception("El documento '{$req->titulo}' es obligatorio.");
                    }

                    if ($request->hasFile($key)) {
                        $file = $request->file($key);

                        if (!$file->isValid()) {
                            throw new \Exception("El archivo '{$req->titulo}' es inválido o no se pudo cargar.");
                        }

                        $safeFileName = preg_replace('/[^a-zA-Z0-9_.-]+/', '_', $req->titulo);
                        $extension = $file->getClientOriginalExtension();
                        $finalFileName = "{$safeFileName}.{$extension}";

                        $basePath = "postulaciones_facilitador/{$cedula}";
                        $path = $file->storeAs("public/{$basePath}", $finalFileName);

                        PostulacionRespuesta::create([
                            'id_postulacion' => $postulacion->id_postulacion,
                            'id_requisito_facilitador' => $req->id_requisito_facilitador,
                            'ruta_archivo' => str_replace('public/', '', $path),
                        ]);
                    }
                }
            }

            // Enviar email de confirmación
            try {
                Mail::send('taller::emails.postulacion_facilitador_recibida', [
                    'postulacion' => $postulacion,
                ], function ($email) use ($postulacion, $user) {
                    $email->subject('Postulación como Facilitador - Recibida');
                    $email->to($user->email);
                });
            } catch (\Exception $e) {
                // No detenemos el proceso si falla el correo
            }

            DB::commit();

            return redirect()->route('taller.postulacion-facilitador.landing')
                ->with('success', 'Tu postulación ha sido enviada exitosamente. Recibirás un correo de confirmación.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Panel administrativo: requisitos + postulaciones
     */
    public function adminIndex()
    {
        $requisitos = RequisitoFacilitador::orderBy('orden')->get();
        $postulaciones = PostulacionFacilitador::with(['persona', 'revisor'])
            ->orderBy('creado_en', 'desc')
            ->get();

        return view('taller::a.PostulacionFacilitadorAdmin', compact('requisitos', 'postulaciones'));
    }

    /**
     * Crear requisito
     */
    public function storeRequisito(StoreRequisitoRequest $request)
    {
        $user = $this->getUsuarioAutenticado();

        RequisitoFacilitador::create([
            'titulo' => $request->titulo,
            'tipo' => $request->tipo,
            'descripcion' => $request->descripcion,
            'obligatorio' => $request->boolean('obligatorio', true),
            'orden' => $request->integer('orden', 0),
            'activo' => true,
            'creado_por' => $user->id,
        ]);

        return redirect()->route('taller.postulacion-facilitador.admin')
            ->with('success', 'Requisito creado exitosamente.');
    }

    /**
     * Actualizar requisito
     */
    public function updateRequisito(StoreRequisitoRequest $request, $id)
    {
        $requisito = RequisitoFacilitador::findOrFail($id);

        $requisito->update([
            'titulo' => $request->titulo,
            'tipo' => $request->tipo,
            'descripcion' => $request->descripcion,
            'obligatorio' => $request->boolean('obligatorio', true),
            'orden' => $request->integer('orden', 0),
        ]);

        return redirect()->route('taller.postulacion-facilitador.admin')
            ->with('success', 'Requisito actualizado exitosamente.');
    }

    /**
     * Activar/desactivar requisito
     */
    public function toggleRequisito($id)
    {
        $requisito = RequisitoFacilitador::findOrFail($id);
        $requisito->update(['activo' => !$requisito->activo]);

        return redirect()->route('taller.postulacion-facilitador.admin')
            ->with('success', 'Requisito ' . ($requisito->activo ? 'activado' : 'desactivado') . ' exitosamente.');
    }

    /**
     * Aprobar postulación → asignar perfil Facilitador
     */
    public function aprobar($id)
    {
        $postulacion = PostulacionFacilitador::findOrFail($id);

        if (!$postulacion->esPendiente()) {
            return back()->with('error', 'Solo se pueden aprobar postulaciones pendientes.');
        }

        DB::beginTransaction();
        try {
            // Verificar que el usuario NO tenga ya el perfil Facilitador
            $user = $postulacion->persona->user;
            if (!$user) {
                DB::rollBack();
                return back()->with('error', 'No se encontró el usuario asociado a esta postulación.');
            }

            if ($user->perfiles()->where('security.profiles.id', 2)->exists()) {
                DB::rollBack();
                return back()->with('error', 'Este usuario ya tiene el perfil de Facilitador.');
            }

            // Actualizar estado
            $postulacion->update([
                'estado' => PostulacionFacilitador::ESTADO_APROBADA,
                'revisada_por' => auth()->id(),
                'fecha_revision' => Carbon::now(),
            ]);

            // Insertar perfil Facilitador (ID 2) en profiles_users
            DB::table('security.profiles_users')->insert([
                'id_rol' => 2, // Facilitador
                'id_users' => $user->id,
                'status' => 1,
                'fecha_aprobacion' => Carbon::now(),
                'aprobado_por' => auth()->id(),
                'creado_por' => auth()->id(),
                'creado_en' => Carbon::now(),
            ]);

            // Enviar email de aprobación
            try {
                Mail::send('taller::emails.postulacion_facilitador_aprobada', [
                    'postulacion' => $postulacion,
                ], function ($email) use ($postulacion) {
                    $email->subject('Postulación como Facilitador - Aprobada');
                    $email->to($postulacion->persona->user->email);
                });
            } catch (\Exception $e) {
                // No detenemos el proceso si falla el correo
            }

            DB::commit();

            return back()->with('success', 'Postulación aprobada. El participante ahora es Facilitador.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al aprobar: ' . $e->getMessage());
        }
    }

    /**
     * Rechazar postulación con motivo → eliminar documentos
     */
    public function rechazar(Request $request, $id)
    {
        $request->validate([
            'motivo_rechazo' => 'required|string|max:1000',
        ]);

        $postulacion = PostulacionFacilitador::findOrFail($id);

        if (!$postulacion->esPendiente()) {
            return back()->with('error', 'Solo se pueden rechazar postulaciones pendientes.');
        }

        DB::beginTransaction();
        try {
            // Actualizar estado
            $postulacion->update([
                'estado' => PostulacionFacilitador::ESTADO_RECHAZADA,
                'motivo_rechazo' => $request->motivo_rechazo,
                'revisada_por' => auth()->id(),
                'fecha_revision' => Carbon::now(),
            ]);

            // Eliminar documentos del storage
            $postulacion->respuestas->each(function ($respuesta) {
                if ($respuesta->ruta_archivo) {
                    $filePath = storage_path('app/public/' . $respuesta->ruta_archivo);
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
            });

            // Enviar email de rechazo
            try {
                Mail::send('taller::emails.postulacion_facilitador_rechazada', [
                    'postulacion' => $postulacion,
                ], function ($email) use ($postulacion) {
                    $email->subject('Postulación como Facilitador - Observaciones');
                    $email->to($postulacion->persona->user->email);
                });
            } catch (\Exception $e) {
                // No detenemos el proceso si falla el correo
            }

            DB::commit();

            return back()->with('success', 'Postulación rechazada. El participante puede corregir y volver a postularse.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al rechazar: ' . $e->getMessage());
        }
    }

    /**
     * Ver documentos del postulante
     */
    public function verDocumentos($id)
    {
        $postulacion = PostulacionFacilitador::with(['respuestas.requisito', 'persona'])->findOrFail($id);

        // Verificar propiedad o permiso
        $user = $this->getUsuarioAutenticado();
        if ($user->personalData->id_persona !== $postulacion->id_persona && !hasPermissionRoute('taller.postulacion-facilitador.admin', \App\Constants\SecurityAction::GESTIONAR_POSTULACIONES_FACILITADOR)) {
            abort(403, 'No autorizado para ver estos documentos.');
        }

        return view('taller::a.PostulacionFacilitadorDocumentos', compact('postulacion'));
    }

    /**
     * Descargar archivo adjunto
     */
    public function descargarDocumento($id)
    {
        $respuesta = PostulacionRespuesta::with(['postulacion.persona'])->findOrFail($id);
        $postulacion = $respuesta->postulacion;

        // Verificar propiedad o permiso
        $user = $this->getUsuarioAutenticado();
        if ($user->personalData->id_persona !== $postulacion->id_persona && !hasPermissionRoute('taller.postulacion-facilitador.admin', \App\Constants\SecurityAction::GESTIONAR_POSTULACIONES_FACILITADOR)) {
            abort(403, 'No autorizado para descargar este documento.');
        }

        if (!$respuesta->ruta_archivo) {
            abort(404, 'Archivo no encontrado.');
        }

        $filePath = storage_path('app/public/' . $respuesta->ruta_archivo);

        if (!file_exists($filePath)) {
            abort(404, 'Archivo no encontrado en el servidor.');
        }

        return response()->download($filePath);
    }

    /**
     * Vista previa del landing (para coordinadores)
     */
    public function previewLanding()
    {
        $requisitos = RequisitoFacilitador::activos()->orderBy('orden')->get();

        return view('taller::a.PostulacionFacilitadorLanding', [
            'requisitos' => $requisitos,
            'postulacionPendiente' => null,
            'ultimaPostulacionRechazada' => null,
            'isPreview' => true,
        ]);
    }
}