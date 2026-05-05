<?php

namespace Modules\Security\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Security\Entities\User;
use Illuminate\Support\Facades\Hash;
use DataTables;
use Illuminate\Support\Facades\Auth;

use App\Helpers\Encryptor;
use Illuminate\Support\Facades\DB;
use App\Constants\SecurityAction;

class UsersController extends Controller {

    public function __construct()
    {
        // Brecha #1 corregida: cada acción requiere el permiso RBAC correspondiente.
        // Sin esto, cualquier usuario autenticado podía crear usuarios o cambiar contraseñas ajenas.
        $this->middleware('permiso:users,' . SecurityAction::VER)
             ->only(['index', 'list']);

        $this->middleware('permiso:users,' . SecurityAction::CREAR)
             ->only(['create']);

        $this->middleware('permiso:users,' . SecurityAction::EDITAR)
             ->only(['update', 'disabled']);

        $this->middleware('permiso:users,' . SecurityAction::SEGURIDAD_USUARIO)
             ->only(['password']);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index() {

        return view('security::users.index');
    }

    public function list(Request $request) {
        if (\Request::ajax()) {
            if ($request->search == null) {
                $Users = User::limit(100)
                        ->Where('active', '0')
                        ->orderBy("id", "desc")
                        //->with('getProfile')
                   //     ->with('getPerfiles') DEBO AJSUTAR A UN ARRAY DE PERFILES
                        ->get();
            } else {
                $cond = $request->search;
                $Users = User::limit(100)
                        ->Where('active', '0')
                        /* ->where(function ($q) use ($cond) {
                            $q->orWhere('id', 'ILIKE', '%' . Upper($cond) . '%');
                            $q->orWhere('document', 'ILIKE', '%' . Upper($cond) . '%');
                            $q->orWhere('phone', 'ILIKE', '%' . Upper($cond) . '%');
                        }) */
                        /*

                          ->orderBy("id", "desc")

                         */
                        ->with('getPerfiles')
                        ->get()
                //->toSql()
                ;
                //dd($request->search);
            }



            $data = DataTables::of($Users)
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
                        // El usuario administrador (ID 1) no puede ser editado ni desactivado
                        if ($row->id == 1) {
                            return '<div class="text-center"><span class="badge badge-secondary">Administrador del Sistema</span></div>';
                        }

                        $actionBtn = '<div class=" text-center">';

                        if ($row->active == 0) {
                            $actionBtn .= '<a  title=""  href="' . route('users.update', $row->crypt_id) . '"   class="btn btn-icon btn-link    btn-xs"> <span class="fa fa-edit"></span></a> ';
                            $actionBtn .= '<a  title=""  href="' . route('users.password', $row->crypt_id) . '" class="btn btn-icon btn-link   btn-warning btn-xs"><span class="fa fa-lock"></span></a> ';
                        } else {
                            $actionBtn = __('Inactive User');
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
                $typeDoc = \Modules\Security\Entities\DocumentType::get()->pluck('code', 'crypt_id');
                $profiles = \Modules\Security\Entities\Profile::where('active', true)->get()->pluck('name', 'crypt_id');
                return view('security::users.create', compact('typeDoc', 'profiles'));
            } else {
                // Fix #2: Validar todos los campos obligatorios
                $request->validate([
                    'username'   => 'required|max:20',
                    'email'      => 'required|email|max:100',
                    'password'   => 'required|min:8|max:16',
                    'profile_id' => 'required',
                ]);
            
                // Crear usuario
                $User = new User();
                $User->username = strtolower($request->username);
                $User->email    = strtolower($request->email);
                $User->password = Hash::make($request->password);
                $User->active   = true;
                $User->user_id  = Auth::user()->id;
                $User->register_date = now();
                $User->ip = $request->ip();
                $User->save();
            
                // Fix #10: Guardar relación en tabla pivote con status numérico
                $profileId = Encryptor::decrypt($request->profile_id);
            
                DB::table('security.profiles_users')->insert([
                    'id_users'   => $User->id,
                    'id_rol'     => $profileId,
                    'status'     => 1,             // Fix #10: valor numérico (1 = activo)
                    'creado_por' => Auth::user()->id,
                    'creado_en'  => now(),
                ]);
            
                return to_route('users')->withSuccess(__('Registered participant, please verify your email'));
        
                /* $request->validate([
                    'document_type_id' => 'required',
                    'document' => 'required',
                    'full_name' => 'required',
                    'email' => 'required|email',
                    'username' => 'required',
                    'password' => 'required',
                    'phone' => 'required',
                    'profile_id' => 'required',
                ]);

                $Country = \Modules\Security\Entities\Countries::where('iso2', $request->country_iso2)->first();

                $User = new User();
                $User->document_type_id = Encryptor::decrypt($request->document_type_id);
                $User->document = $request->document;
                $User->full_name = Upper($request->full_name);
                $User->email = $request->email;
                $User->username = $request->username;
                $User->password = Hash::make($request->password);
                $User->phone = $request->phone;
                $User->country_id = $Country->id;
                $User->profile_id = Encryptor::decrypt($request->profile_id);

                $User->active = true;
                $User->user_id = Auth::user()->id;
                $User->register_date = now();
                $User->ip = $request->ip();
                $User->save();

                return to_route('users')->withSuccess(__('Registered participant, please verify your email'));*/
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
        $userId = Encryptor::decrypt($id);
        
        // Bloquear edición del usuario Admin (ID 1)
        if ($userId == 1) {
            return to_route('users')->withErrors(['error-message' => __('System Administrator cannot be modified')]);
        }

        if (!\Request::ajax()) {
            if (\Request::isMethod('get')) {
                $user = User::find($userId);
                $typeDoc = \Modules\Security\Entities\DocumentType::get()->pluck('code', 'crypt_id');
                $profiles = \Modules\Security\Entities\Profile::where('active', true)->get()->pluck('name', 'crypt_id');
                return view('security::users.update', compact('id', 'user', 'typeDoc', 'profiles'));
            } else {
                $request->validate([
                    'document_type_id' => 'required',
                    'document' => 'required',
                    'full_name' => 'required',
                    'email' => 'required|email',
                    'phone' => 'required',
                    'active' => 'required',
                    'profile_id' => 'required',
                ]);

                $Country = \Modules\Security\Entities\Countries::where('iso2', $request->country_iso2)->first();

                $User = User::find(Encryptor::decrypt($id));
                $User->document_type_id = Encryptor::decrypt($request->document_type_id);
                $User->document = $request->document;
                $User->full_name = Upper($request->full_name);
                $User->email = $request->email;

                $User->phone = $request->phone;
                $User->country_id = $Country->id;
                $User->profile_id = Encryptor::decrypt($request->profile_id);

                $User->active = $request->active;

                $User->save();

                // Fix #11: Sincronizar también la tabla pivote security_profiles_users
                $newProfileId = Encryptor::decrypt($request->profile_id);
                $pivotExists = DB::table('security.profiles_users')
                    ->where('id_users', $User->id)
                    ->where('id_rol', $newProfileId)
                    ->exists();

                if (!$pivotExists) {
                    DB::table('security.profiles_users')->insert([
                        'id_rol'        => $newProfileId,
                        'id_users'      => $User->id,
                        'status'        => 1,
                        'creado_por'    => Auth::user()->id,
                        'creado_en'     => now(),
                        'actualizado_por' => Auth::user()->id,
                        'actualizado_en'  => now(),
                    ]);
                }

                return to_route('users')->withSuccess(__('User Updated Successfully'));
            }
        }
    }

    public function password($id, Request $request) {
        $userId = Encryptor::decrypt($id);

        // Bloquear cambio de contraseña del usuario Admin (ID 1) desde este panel
        if ($userId == 1) {
            return to_route('users')->withErrors(['error-message' => __('System Administrator password cannot be changed from here')]);
        }

        if (!\Request::ajax()) {
            if (\Request::isMethod('get')) {
                $user = User::find($userId);
                return view('security::users.password', compact('id', 'user'));
            } else {

                $request->validate([
                    'new_password' => 'required|same:re_password|min:8|max:16',
                    're_password' => 'required',
                ]);

                if (Hash::check($request->new_password, Auth::user()->password)) {
                    return to_route('users.password', $id)->withErrors(['error-message' => __("New Password cannot be same as your current password.")]);
                }
                User::whereId(Encryptor::decrypt($id))->update([
                    'password' => Hash::make($request->new_password)
                ]);
                return to_route('users')->withSuccess(__('User Updated Successfully'));
            }
        }
    }

    public function disabled($id) {
        $userId = Encryptor::decrypt($id);

        // Bloquear desactivación del usuario Admin (ID 1)
        if ($userId == 1) {
            return to_route('users')->withErrors(['error-message' => __('System Administrator cannot be deactivated')]);
        }

        if (!\Request::ajax()) {
            $User = User::find($userId);
            $User->active = false;
            $User->save();
            return to_route('users')->withSuccess(__('Profile Deactivated Successfully'));
        }
    }
}
