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
        //$Profiles_active = Profile::where("status", 1)->get();

        return view('security::profiles.index');
    }

    public function list(Request $request) {
        if (\Request::ajax()) {
            if ($request->search == null) {
                $Profiles = Profile::limit(100)
                        ->orderBy("id", "desc")
                        ->where('active', 'true')
                        ->get();
            } else {
                $Profiles = Profile::limit(100)
                        ->where('active', 'true')
                        ->Where('name', 'ILIKE', '%' . Upper($request->search) . '%')
                        ->orderBy("id", "desc")
                        ->get();
            }



            $data = DataTables::of($Profiles)
                    ->addIndexColumn()
                    /*
                      ->filter(function ($instance) {

                      return true;

                      if ($input != null) {
                      $instance->collection = $instance->collection->filter(function ($row) use ($input) {
                      if (Str::contains(Str::lower($row['aeronave']['full_nombre_tn']), Str::lower($input))) {
                      return true;
                      } else {
                      if (Str::contains(Str::lower($row['piloto']['full_name']), Str::lower($input))) {
                      return true;
                      } else {
                      if (Str::contains(Str::lower($row['aeropuerto']['full_nombre']), Str::lower($input))) {
                      return true;
                      } else {
                      if (Str::contains(Str::lower($row['fecha_operacion2']), Str::lower($input))) {
                      return true;
                      } else {

                      }
                      }
                      }
                      }
                      return false;
                      });
                      }


                      })
                     * 
                     */
                    ->addColumn('action', function ($row) {
                        // El perfil Administrador (ID 1) es crítico y no debe ser alterado
                        if ($row->id == 1) {
                            return '<div class="text-center"><span class="badge badge-secondary">Perfil Principal del Sistema</span></div>';
                        }

                        $actionBtn = '<div class=" text-center">';

                        if ($row->active == true) {
                            $actionBtn .= '<a  title=""  href="' . route('profiles.update', $row->crypt_id) . '"   class="btn btn-icon btn-link    btn-xs"> <span class="fa fa-edit"></span></a> ';
                            ;
                            $actionBtn .= '<a  title=""  href="' . route('profiles.permissions', $row->crypt_id) . '" class="btn btn-icon btn-link   btn-warning btn-xs"><span class="fa fa-lock"></span></a> ';
                        } else {
                            $actionBtn = __('Inactive Profile');
                        }
                        $actionBtn .= '</div>';
                        return $actionBtn;
                    })
                    ->rawColumns(['action'])
            //->make(true)

            ;
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
        $profileId = Encryptor::decrypt($id);

        // Bloquear gestión de permisos del perfil Administrador (ID 1)
        if ($profileId == 1) {
            return to_route('profiles')->withErrors(['error-message' => __('Main System Profile permissions cannot be modified')]);
        }

        if (!\Request::ajax()) {
            if (\Request::isMethod('get')) {
                $profile = Profile::with('permissions')->find($profileId);
                //dd($profile->toArray());
                $MODULES = \Modules\Security\Entities\Modulo::with('getMenus.getProcess')->get();
                
                return view('security::profiles.permissions', compact('id', 'profile', 'MODULES'));
            } else {
                $decryptedId = $profileId;
                $Profile = Profile::find($decryptedId);
                if ($request->has('name')) $Profile->name = Upper($request->name);
                if ($request->has('description')) $Profile->description = $request->description;
                if ($request->has('active')) $Profile->active = $request->active;
                $Profile->save();

                // ----- LÓGICA DE RBAC: GUARDADO DE PERMISOS PIVOT -----
                // Limpiar asginaciones antiguas para este perfil
                \Illuminate\Support\Facades\DB::table('security.profile_permissions')
                    ->where('profile_id', $decryptedId)
                    ->delete();

                if ($request->has('permissions') && is_array($request->permissions)) {
                    foreach ($request->permissions as $processId => $actionsIdsArray) {
                        // Fix #4: Siempre desencriptar — la vista siempre envía el crypt_id
                        $pId = Encryptor::decrypt($processId);

                        // Fix #13: Si el checkbox raíz se marcó sin acciones, $actionsIdsArray
                        // será un string ("on"), no un array. Ignoraremos ese caso.
                        if (!is_array($actionsIdsArray)) {
                            continue;
                        }

                        foreach (array_keys($actionsIdsArray) as $numId) {
                            // Resolver slug: si es numérico, usar el mapa de SecurityAction;
                            // si ya es string (slug directo), tomarlo tal cual.
                            $slug = is_numeric($numId)
                                ? \App\Constants\SecurityAction::dbString((int)$numId)
                                : $numId;

                            if (!empty($slug)) {
                                // Fix #3: Solo buscar el permiso existente, NUNCA crear uno nuevo.
                                // Los permisos se definen en migraciones / SecurityAction, no aquí.
                                $perm = \Illuminate\Support\Facades\DB::table('security.permissions')
                                    ->where('process_id', $pId)
                                    ->where('slug', $slug)
                                    ->first();

                                if ($perm) {
                                    \Illuminate\Support\Facades\DB::table('security.profile_permissions')->insert([
                                        'profile_id'    => $decryptedId,
                                        'permission_id' => $perm->id,
                                        'created_at'    => now(),
                                        'updated_at'    => now(),
                                    ]);
                                }
                            }
                        }
                    }
                }

                // Limpiar Caché Local de Permisos
                if (session()->get('profile_id') == $decryptedId) {
                    session()->forget("permisos_perfil_" . $decryptedId);
                }

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
        $profileId = Encryptor::decrypt($id);

        // Bloquear edición del perfil Administrador (ID 1)
        if ($profileId == 1) {
            return to_route('profiles')->withErrors(['error-message' => __('Main System Profile cannot be modified')]);
        }

        if (!\Request::ajax()) {
            if (\Request::isMethod('get')) {
                $profile = Profile::find($profileId);
                return view('security::profiles.update', compact('id', 'profile'));
            } else {
                $request->validate([
                    'name' => 'required',
                    'description' => 'required'
                ]);
                $Profile = Profile::find(Encryptor::decrypt($id));
                $Profile->name = Upper($request->name);
                $Profile->description = $request->description;
                $Profile->active = $request->active;
                $Profile->save();

                return to_route('profiles')->withSuccess(__('Successfully Updated Profile'));
            }
        }
    }

    public function disabled($id) {
        $profileId = Encryptor::decrypt($id);

        // Bloquear desactivación del perfil Administrador (ID 1)
        if ($profileId == 1) {
            return to_route('profiles')->withErrors(['error-message' => __('Main System Profile cannot be deactivated')]);
        }

        if (!\Request::ajax()) {
            $Profile = Profile::find($profileId);
            $Profile->active = false;
            $Profile->save();
            return to_route('profiles')->withSuccess(__('Profile Deactivated Successfully'));
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
