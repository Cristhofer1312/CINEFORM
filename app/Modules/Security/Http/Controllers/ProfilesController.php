<?php

namespace Modules\Security\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Security\Entities\Profile;
use DataTables;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Encryptor;
use App\Constants\SecurityAction;
use App\Services\Security\Auditoria;
use App\Services\Security\Autorizacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfilesController extends Controller {

    public function __construct()
    {
        // Brecha #1 corregida: cada acción requiere el permiso RBAC correspondiente.
        // Sin esto, cualquier usuario autenticado podía acceder a estas rutas directamente.
        $this->middleware('permiso:profiles,' . SecurityAction::VER)
             ->only(['index', 'list']);

        $this->middleware('permiso:profiles,' . SecurityAction::CREAR)
             ->only(['create']);

        $this->middleware('permiso:profiles,' . SecurityAction::EDITAR)
             ->only(['update', 'disabled']);

        $this->middleware('permiso:profiles,' . SecurityAction::GESTIONAR_PERMISOS)
             ->only(['permissions']);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index() {
        return view('security::profiles.index');
    }

    public function list(Request $request) {
        if (\Request::ajax()) {
            if ($request->search == null) {
                $Profiles = Profile::limit(100)
                        ->orderBy("id", "desc")
                        ->get();
            } else {
                $Profiles = Profile::limit(100)
                        ->Where('name', 'ILIKE', '%' . Upper($request->search) . '%')
                        ->orderBy("id", "desc")
                        ->get();
            }



            $data = DataTables::of($Profiles)
                    ->addIndexColumn()
                    ->addColumn('status', function ($row) {
                        if ($row->active == true) {
                            return '<span class="badge badge-primary">' . __('Active') . '</span>';
                        }
                        return '<span class="badge badge-secondary">' . __('Inactive Profile') . '</span>';
                    })
                    ->addColumn('action', function ($row) {
                        // El perfil Administrador (ID 1) es crítico y no debe ser alterado
                        if ((int) $row->id === \App\Constants\Perfil::ADMINISTRADOR) {
                            return '<div class="text-center"><span class="badge badge-secondary">Perfil Principal del Sistema</span></div>';
                        }

                        $actionBtn = '<div class="d-flex justify-content-center">';

                        $actionBtn .= '<a title="' . __('Edit') . '" href="' . route('profiles.update', $row->crypt_id) . '" class="btn btn-icon btn-link btn-secondary"><span class="fa fa-edit"></span></a> ';
                        $actionBtn .= '<a title="' . __('Permissions') . '" href="' . route('profiles.permissions', $row->crypt_id) . '" class="btn btn-icon btn-link btn-secondary"><span class="fa fa-lock"></span></a> ';

                        if ($row->active == true) {
                            $actionBtn .= '<a title="' . __('Deactivate') . '" data-action="deactivate" href="' . route('profiles.disabled', $row->crypt_id) . '" class="btn btn-icon btn-link btn-danger btn-toggle"><span class="fa fa-ban"></span></a> ';
                        } else {
                            $actionBtn .= '<a title="' . __('Activate') . '" data-action="activate" href="' . route('profiles.disabled', $row->crypt_id) . '" class="btn btn-icon btn-link btn-success btn-toggle"><span class="fa fa-check"></span></a> ';
                        }
                        $actionBtn .= '</div>';
                        return $actionBtn;
                    })
                    ->rawColumns(['action', 'status']);

            return $data->toJson();
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request) {
        if (!\Request::ajax()) {
            if (\Request::isMethod('get')) {
                return view('security::profiles.create');
            } else {
                $request->validate([
                    'name' => 'required',
                    'description' => 'required'
                ]);

                $Profile = new Profile();
                $Profile->name = Upper($request->name);
                $Profile->description = $request->description;

                $Profile->active = true;
                $Profile->user_id = Auth::user()->id;
                $Profile->register_date = now();
                $Profile->ip = $request->ip();
                $Profile->save();

                return to_route('profiles')->withSuccess(__('Successfully Created Profile'));
            }
        }
    }

    public function permissions($id, Request $request) {
        // El middleware DecryptIds ya desencripta el parámetro de ruta {id}.
        // Se normaliza por seguridad ante valores no numéricos.
        $profileId = is_numeric($id) ? (int) $id : (int) Encryptor::decrypt($id);

        // Bloquear gestión de permisos del perfil Administrador (ID 1)
        if ($profileId == 1) {
            return to_route('profiles')->withErrors(['error-message' => __('Main System Profile permissions cannot be modified')]);
        }

        $autorizacion = app(Autorizacion::class);

        // Los permisos del usuario son la suma de sus perfiles: editar un perfil
        // propio equivale a editarse a sí mismo (ver la guarda antes de guardar).
        $esPerfilPropio = ! $autorizacion->esRaiz() && in_array($profileId, $autorizacion->perfilesActivosPara(), true);

        if (!\Request::ajax()) {
            if (\Request::isMethod('get')) {
                $profile = Profile::with('permissions')->find($profileId);
                if (!$profile) {
                    abort(404, 'Perfil no encontrado');
                }
                $MODULES = \Modules\Security\Entities\Modulo::with('getMenus.getProcess.permissions')->get();

                return view('security::profiles.permissions', ['id' => Encryptor::encrypt($profileId), 'profile' => $profile, 'MODULES' => $MODULES]);
            } else {
                $Profile = Profile::find($profileId);
                if (!$Profile) {
                    abort(404, 'Perfil no encontrado');
                }

                // ----- LÓGICA DE RBAC: GUARDADO DE PERMISOS -----
                // Quien llega aquí tiene GESTIONAR_PERMISOS (middleware de la ruta) y puede
                // configurar cualquier perfil salvo el Administrador y su propio perfil activo.
                // Catálogo de permisos existentes: [permission_id => (proceso, slug)].
                // Los permisos se definen en migraciones/seeders; aquí nunca se crean.
                $catalogo = DB::table('security.permissions as p')
                    ->join('security.processes as pr', 'pr.id', '=', 'p.process_id')
                    ->get(['p.id', 'p.process_id', 'p.slug', 'pr.codigo'])
                    ->keyBy('id');

                // Permisos marcados en el formulario.
                $solicitados = [];
                foreach ((array) $request->input('permissions', []) as $processId => $acciones) {
                    // La vista envía el crypt_id del proceso: DecryptIds no desencripta las llaves.
                    $pId = (int) Encryptor::decrypt($processId);

                    // Si llega la casilla raíz sin acciones, el valor es un string ("on").
                    if (! is_array($acciones)) {
                        continue;
                    }

                    foreach (array_keys($acciones) as $accion) {
                        $slug = is_numeric($accion) ? SecurityAction::dbString((int) $accion) : $accion;
                        $permiso = $catalogo->first(fn ($p) => (int) $p->process_id === $pId && $p->slug === $slug);
                        if ($permiso) {
                            $solicitados[(int) $permiso->id] = true;
                        }
                    }
                }

                $actuales = DB::table('security.profile_permissions')
                    ->where('profile_id', $profileId)
                    ->pluck('permission_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
                $finales = array_keys($solicitados);

                $aQuitar = array_values(array_diff($actuales, $finales));
                $aAgregar = array_values(array_diff($finales, $actuales));

                // Anti-autopromoción: a un perfil propio solo se le puede dar lo que
                // el usuario ya tiene por la suma de sus perfiles, y no se puede
                // desactivar desde aquí.
                if ($esPerfilPropio) {
                    $nuevosParaMi = array_filter($aAgregar, fn ($id) => ! $autorizacion->puedeEnProceso(
                        $catalogo[$id]->process_id,
                        SecurityAction::stringToId($catalogo[$id]->slug)
                    ));
                    $desactiva = $request->has('active') && ! filter_var($request->active, FILTER_VALIDATE_BOOLEAN);

                    if ($nuevosParaMi !== [] || $desactiva) {
                        return to_route('profiles')->withErrors(['error-message' => __('You cannot grant yourself permissions you do not already have')]);
                    }
                }
                $describir = fn (array $ids) => collect($ids)
                    ->filter(fn ($id) => isset($catalogo[$id]))
                    ->map(fn ($id) => $catalogo[$id]->codigo.':'.$catalogo[$id]->slug)
                    ->sort()->values()->all();

                DB::transaction(function () use ($Profile, $request, $profileId, $aQuitar, $aAgregar, $actuales, $finales, $describir) {
                    $antesPerfil = ['name' => $Profile->name, 'description' => $Profile->description, 'active' => (bool) $Profile->active];

                    if ($request->has('name')) $Profile->name = Upper($request->name);
                    if ($request->has('description')) $Profile->description = $request->description;
                    if ($request->has('active')) $Profile->active = $request->active;
                    $Profile->save();

                    if (! empty($aQuitar)) {
                        DB::table('security.profile_permissions')
                            ->where('profile_id', $profileId)
                            ->whereIn('permission_id', $aQuitar)
                            ->delete();
                    }

                    if (! empty($aAgregar)) {
                        DB::table('security.profile_permissions')->insert(array_map(fn ($id) => [
                            'profile_id' => $profileId,
                            'permission_id' => $id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ], $aAgregar));
                    }

                    $despuesPerfil = ['name' => $Profile->name, 'description' => $Profile->description, 'active' => (bool) $Profile->active];

                    if (! empty($aQuitar) || ! empty($aAgregar) || $antesPerfil !== $despuesPerfil) {
                        app(Auditoria::class)->registrar(
                            'perfil.permisos.actualizar',
                            'perfil',
                            $profileId,
                            $antesPerfil + ['permisos' => $describir($actuales)],
                            $despuesPerfil + ['permisos' => $describir($finales)]
                        );
                    }
                });

                $autorizacion->invalidar();

                return to_route('profiles')->withSuccess(__('Successfully Updated Profile Permissions'));
            }
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update($id, Request $request) {
        // El middleware DecryptIds ya desencripta el parámetro de ruta {id}.
        // Se normaliza por seguridad ante valores no numéricos.
        $profileId = is_numeric($id) ? (int) $id : (int) Encryptor::decrypt($id);

        // Bloquear edición del perfil Administrador (ID 1)
        if ($profileId == 1) {
            return to_route('profiles')->withErrors(['error-message' => __('Main System Profile cannot be modified')]);
        }

        if (!\Request::ajax()) {
            if (\Request::isMethod('get')) {
                $profile = Profile::find($profileId);
                if (!$profile) {
                    abort(404, 'Perfil no encontrado');
                }
                return view('security::profiles.update', ['id' => Encryptor::encrypt($profileId), 'profile' => $profile]);
            } else {
                $request->validate([
                    'name' => 'required',
                    'description' => 'required'
                ]);
                $Profile = Profile::find($profileId);
                $Profile->name = Upper($request->name);
                $Profile->description = $request->description;
                $Profile->save();

                return to_route('profiles')->withSuccess(__('Successfully Updated Profile'));
            }
        }
    }

    public function disabled($id) {
        // El middleware DecryptIds ya desencripta el parámetro de ruta {id}.
        // Se normaliza por seguridad ante valores no numéricos.
        $profileId = is_numeric($id) ? (int) $id : (int) Encryptor::decrypt($id);

        // Bloquear desactivación del perfil Administrador (ID 1) — siempre
        if ($profileId === \App\Constants\Perfil::ADMINISTRADOR) {
            return to_route('profiles')->withErrors(['error-message' => __('Main System Profile cannot be deactivated')]);
        }

        // Quien tiene EDITAR sobre perfiles (middleware de la ruta) puede activar o
        // desactivar cualquier perfil, igual que el administrador; desactivar un perfil
        // cierra la sesión de todos sus usuarios y queda auditado.
        $esRaiz = app(Autorizacion::class)->esRaiz();

        // Bloquear desactivación de un perfil propio (no-admin)
        if (! $esRaiz && in_array($profileId, app(Autorizacion::class)->perfilesActivosPara(), true)) {
            return to_route('profiles')->withErrors(['error-message' => __('You cannot deactivate your currently active profile')]);
        }

        if (!\Request::ajax()) {
            $Profile = Profile::find($profileId);
            if (! $Profile) {
                abort(404, 'Perfil no encontrado');
            }

            DB::transaction(function () use ($Profile, $profileId) {
                $Profile->active = !$Profile->active;
                $Profile->save();

                // Terminar la sesión de todos los usuarios con este perfil.
                // Se regenera el session_token de cada usuario (driver de sesión File);
                // el middleware Authenticate detecta la diferencia y cierra la sesión.
                // Usamos DB::table porque session_token no está en User::$fillable.
                $userIds = DB::table('security.profiles_users')
                    ->where('id_rol', $profileId)
                    ->pluck('id_users');

                if ($userIds->isNotEmpty()) {
                    DB::table('security.users')
                        ->whereIn('id', $userIds)
                        ->update(['session_token' => Str::random(40)]);
                }

                app(Auditoria::class)->registrar(
                    $Profile->active ? 'perfil.activar' : 'perfil.desactivar',
                    'perfil',
                    $profileId,
                    ['active' => ! $Profile->active],
                    ['active' => (bool) $Profile->active, 'sesiones_cerradas' => $userIds->count()]
                );
            });

            $message = $Profile->active
                ? __('Profile Activated Successfully')
                : __('Profile Deactivated Successfully');

            return to_route('profiles')->withSuccess($message);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request) {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id) {
        return view('security::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id) {
        return view('security::edit');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id) {
        //
    }
}