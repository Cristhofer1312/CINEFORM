<?php

namespace Modules\Security\Http\Controllers;

use App\Constants\SecurityAction;
use App\Helpers\Encryptor;
use DataTables;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Security\Entities\User;

class UsersController extends Controller
{
    public function __construct()
    {
        // Brecha #1 corregida: cada acción requiere el permiso RBAC correspondiente.
        // Sin esto, cualquier usuario autenticado podía crear usuarios o cambiar contraseñas ajenas.
        $this->middleware('permiso:users,'.SecurityAction::VER)
            ->only(['index', 'list']);

        $this->middleware('permiso:users,'.SecurityAction::CREAR)
            ->only(['create']);

        $this->middleware('permiso:users,'.SecurityAction::EDITAR)
            ->only(['update', 'disabled']);

        $this->middleware('permiso:users,'.SecurityAction::SEGURIDAD_USUARIO)
            ->only(['password']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index()
    {

        return view('security::users.index');
    }

    public function list(Request $request)
    {
        if (\Request::ajax()) {
            if ($request->search == null) {
                $Users = User::limit(500)
                    ->orderBy('id', 'desc')
                        // ->with('getProfile')
                   //     ->with('perfiles') DEBO AJSUTAR A UN ARRAY DE PERFILES
                    ->get();
            } else {
                $cond = $request->search;
                $Users = User::limit(500)
                    ->where(function ($q) use ($cond) {
                        $q->where('username', 'ILIKE', '%'.Upper($cond).'%')
                            ->orWhere('email', 'ILIKE', '%'.Upper($cond).'%')
                            ->orWhere('document', 'ILIKE', '%'.Upper($cond).'%')
                            ->orWhere('full_name', 'ILIKE', '%'.Upper($cond).'%');
                    })
                    ->with('perfiles')
                    ->orderBy('id', 'desc')
                    ->get();
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
                ->addColumn('status', function ($row) {
                    if ((int) $row->active === 1) {
                        return '<span class="badge badge-soft-success rounded-pill px-3">'.__('Active').'</span>';
                    }

                    return '<span class="badge badge-soft-secondary rounded-pill px-3">'.__('Inactive User').'</span>';
                })
                ->addColumn('action', function ($row) {
                    // El usuario administrador (ID 1) no puede ser editado ni desactivado
                    if (app(\App\Services\Security\Autorizacion::class)->esRaiz($row)) {
                        return '<div class="text-center"><span class="badge badge-soft-secondary rounded-pill px-3">Administrador del Sistema</span></div>';
                    }

                    $actionBtn = '<div class="d-flex justify-content-center gap-1">';

                    if ((int) $row->active === 1) {
                        $actionBtn .= '<a title="'.__('Edit').'" href="'.route('users.update', $row->crypt_id).'" class="btn btn-circle btn-light border"><span class="fa fa-edit"></span></a> ';
                        $actionBtn .= '<a title="'.__('Change Password').'" href="'.route('users.password', $row->crypt_id).'" class="btn btn-circle btn-light border"><span class="fa fa-lock"></span></a> ';
                    } else {
                        $actionBtn .= '<span class="badge badge-soft-secondary rounded-pill px-3">'.__('Inactive User').'</span>';
                    }
                    $actionBtn .= '</div>';

                    return $actionBtn;
                })
                ->rawColumns(['action', 'status']);

            // ->make(true)
            return $data->toJson();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create(Request $request)
    {
        if (! \Request::ajax()) {
            if (\Request::isMethod('get')) {
                $typeDoc = \Modules\Security\Entities\DocumentType::get()->pluck('code', 'crypt_id');
                // D8: solo se ofrecen los perfiles que el actor puede asignar.
                $profiles = \Modules\Security\Entities\Profile::whereIn('id', $this->perfilesAsignables())->get()->pluck('name', 'crypt_id');

                return view('security::users.create', compact('typeDoc', 'profiles'));
            } else {
                // Fix #2: Validar todos los campos obligatorios
                $request->validate([
                    'username' => 'required|max:20',
                    'email' => 'required|email|max:100',
                    'password' => ['required', \Illuminate\Validation\Rules\Password::defaults()],
                    'profile_id' => 'required',
                ]);

                // No se puede asignar un perfil desactivado
                if (! \Modules\Security\Entities\Profile::where('id', (int) $request->profile_id)->where('active', true)->exists()) {
                    return back()->withInput()->withErrors(['error-message' => __('Cannot assign an inactive profile')]);
                }

                // D8: solo perfiles que el actor posee (evita crear un Administrador).
                if (! in_array((int) $request->profile_id, $this->perfilesAsignables(), true)) {
                    abort(403, 'No puedes asignar un perfil que no tienes.');
                }

                // Crear usuario
                $User = new User;
                $User->username = strtolower($request->username);
                $User->email = strtolower($request->email);
                $User->password = Hash::make($request->password);
                $User->active = true;
                $User->user_id = Auth::user()->id;
                $User->register_date = now();
                $User->ip = $request->ip();
                $User->save();

                // Fix #10: Guardar relación en tabla pivote con status numérico
                // El middleware DecryptIds ya desencriptó el profile_id; no se vuelve a desencriptar
                $profileId = (int) $request->profile_id;

                DB::table('security.profiles_users')->insert([
                    'id_users' => $User->id,
                    'id_rol' => $profileId,
                    'status' => 1,             // Fix #10: valor numérico (1 = activo)
                    'creado_por' => Auth::user()->id,
                    'creado_en' => now(),
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
     *
     * @param  int  $id
     * @return Renderable
     */
    public function update($id, Request $request)
    {
        // El middleware DecryptIds ya desencripta el parámetro de ruta {id}.
        // Se normaliza por seguridad ante valores no numéricos.
        $userId = is_numeric($id) ? (int) $id : (int) Encryptor::decrypt($id);

        // Bloquear edición del usuario Admin (ID 1)
        if ($userId == 1) {
            return to_route('users')->withErrors(['error-message' => __('System Administrator cannot be modified')]);
        }

        // Nadie se edita a sí mismo desde administración (perfil, estado): para
        // los datos propios está "Configuración de cuenta".
        if ($userId === (int) Auth::id()) {
            return to_route('users')->withErrors(['error-message' => 'No puedes modificar tu propio usuario desde administración. Usa la configuración de tu cuenta.']);
        }

        if (! \Request::ajax()) {
            if (\Request::isMethod('get')) {
                $user = User::find($userId);
                if (! $user) {
                    abort(404, 'Usuario no encontrado');
                }
                $typeDoc = \Modules\Security\Entities\DocumentType::get()->pluck('code', 'crypt_id');
                $profiles = \Modules\Security\Entities\Profile::whereIn('id', $this->perfilesAsignables())->get()->pluck('name', 'crypt_id');

                return view('security::users.update', ['id' => Encryptor::encrypt($userId), 'user' => $user, 'typeDoc' => $typeDoc, 'profiles' => $profiles]);
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

                // No se puede asignar un perfil desactivado
                if (! \Modules\Security\Entities\Profile::where('id', (int) $request->profile_id)->where('active', true)->exists()) {
                    return back()->withInput()->withErrors(['error-message' => __('Cannot assign an inactive profile')]);
                }

                // D8: solo perfiles que el actor posee.
                if (! in_array((int) $request->profile_id, $this->perfilesAsignables(), true)) {
                    abort(403, 'No puedes asignar un perfil que no tienes.');
                }

                $Country =\Modules\Security\Entities\Countries::where('iso2', $request->country_iso2)->first();

                $User = User::find($userId);
                // El middleware DecryptIds ya desencriptó los valores post (document_type_id, profile_id)
                $User->document_type_id = $request->document_type_id;
                $User->document = $request->document;
                $User->full_name = Upper($request->full_name);
                $User->email = $request->email;

                $User->phone = $request->phone;
                $User->country_id = $Country->id;
                $User->profile_id = $request->profile_id;

                $User->active = $request->active;

                $User->save();

                // Fix #11: Sincronizar también la tabla pivote security_profiles_users
                $newProfileId = (int) $request->profile_id;
                $pivotExists = DB::table('security.profiles_users')
                    ->where('id_users', $User->id)
                    ->where('id_rol', $newProfileId)
                    ->exists();

                if (! $pivotExists) {
                    DB::table('security.profiles_users')->insert([
                        'id_rol' => $newProfileId,
                        'id_users' => $User->id,
                        'status' => 1,
                        'creado_por' => Auth::user()->id,
                        'creado_en' => now(),
                        'actualizado_por' => Auth::user()->id,
                        'actualizado_en' => now(),
                    ]);
                }

                app(\App\Services\Security\Autorizacion::class)->invalidar();

                return to_route('users')->withSuccess(__('User Updated Successfully'));
            }
        }
    }

    public function password($id, Request $request)
    {
        // El middleware DecryptIds ya desencripta el parámetro de ruta {id}.
        $userId = is_numeric($id) ? (int) $id : (int) Encryptor::decrypt($id);

        // Bloquear cambio de contraseña del usuario Admin (ID 1) desde este panel
        if ($userId == 1) {
            return to_route('users')->withErrors(['error-message' => __('System Administrator password cannot be changed from here')]);
        }

        // La contraseña propia se cambia en "Configuración de cuenta", que pide la actual.
        if ($userId === (int) Auth::id()) {
            return to_route('users')->withErrors(['error-message' => 'Cambia tu propia contraseña desde la configuración de tu cuenta.']);
        }

        if (! \Request::ajax()) {
            if (\Request::isMethod('get')) {
                $user = User::find($userId);
                if (! $user) {
                    abort(404, 'Usuario no encontrado');
                }

                return view('security::users.password', ['id' => Encryptor::encrypt($userId), 'user' => $user]);
            } else {

                $request->validate([
                    'new_password' => ['required', 'same:re_password', \Illuminate\Validation\Rules\Password::defaults()],
                    're_password' => 'required',
                ]);

                // Se compara con la contraseña del usuario afectado (antes, con la del actor).
                $afectado = User::findOrFail($userId);
                if (Hash::check($request->new_password, $afectado->password)) {
                    return to_route('users.password', Encryptor::encrypt($userId))->withErrors(['error-message' => __('New Password cannot be same as your current password.')]);
                }
                // Nueva contraseña y nuevo session_token: se cierran las sesiones
                // abiertas del usuario (Authenticate compara el token).
                User::whereId($userId)->update([
                    'password' => Hash::make($request->new_password),
                ]);
                DB::table('security.users')->where('id', $userId)->update(['session_token' => \Illuminate\Support\Str::random(40)]);

                return to_route('users')->withSuccess(__('User Updated Successfully'));
            }
        }
    }

    public function disabled($id)
    {
        // El middleware DecryptIds ya desencripta el parámetro de ruta {id}.
        $userId = is_numeric($id) ? (int) $id : (int) Encryptor::decrypt($id);

        // Bloquear desactivación del usuario Admin (ID 1)
        if ($userId == 1) {
            return to_route('users')->withErrors(['error-message' => __('System Administrator cannot be deactivated')]);
        }

        if (! \Request::ajax()) {
            $User = User::find($userId);
            $User->active = false;
            $User->save();

            return to_route('users')->withSuccess(__('Profile Deactivated Successfully'));
        }
    }

    /** D8: perfiles que el actor puede asignar (los suyos; la raíz, todos). */
    private function perfilesAsignables(): array
    {
        return app(\App\Services\Security\Autorizacion::class)->perfilesAsignables();
    }
}
