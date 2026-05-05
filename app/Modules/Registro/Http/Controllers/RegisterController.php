<?php

namespace Modules\Registro\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Security\Entities\User;
use Modules\Comun\Entities\PersonalData;
use Modules\Security\Entities\Profile;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /**
     * Muestra el formulario de registro
     * @return Renderable
     */
    public function index()
    {
        $documentTypes = DB::table('security.document_types')->get();
        $genders       = DB::table('security.genders')->get();

        return view('registro::register', compact('documentTypes', 'genders'));
    }

    /**
     * Guarda el nuevo usuario y perfil
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:30', Rule::unique(User::class, 'username')],
            'email' => ['required', 'email', Rule::unique(User::class, 'email')],
            'password' => 'required|min:6|confirmed',
            'tipo_dni' => 'required',
            'dni' => [
                'required', 
                Rule::unique(PersonalData::class, 'dni')
            ],
            /* 'pasaporte' => [
                'nullable', 
                Rule::unique(PersonalData::class, 'pasaporte')
            ], */
            'rif' => [
                'nullable', 
                Rule::unique(PersonalData::class, 'rif')
            ],
            'genero' => 'required',
            'primer_nombre' => 'required|string|max:100',
            'primer_apellido' => 'required|string|max:100',
            'telefono' => 'required',
        ], [
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique' => 'Este nombre de usuario ya está en uso, por favor elija otro.',
            'dni.unique' => 'Este DNI ya se encuentra registrado en el sistema.',
            'email.unique' => 'Este correo electrónico ya está en uso por otro usuario.',
            'pasaporte.unique' => 'Este pasaporte ya se encuentra registrado.',
            'rif.unique' => 'Este RIF ya se encuentra registrado.',
        ]);

        DB::beginTransaction();

        try {
            // 1. Crear el usuario
            $user = new User([
                'username' => strtolower($request->username), 
                'email'    => strtolower($request->email),
                'password' => Hash::make($request->password),
            ]);
            
            // Campos de auditoría y activación requeridos por el Módulo de Seguridad
            $user->register_date = now();
            $user->ip = $request->ip();
            $user->active = 0; // activo por defecto (según lógica detected en SecurityController)
            $user->save();

            // 2. Asignar perfil de "Participante" (Asumiendo que existe en security_profiles)
            $perfilParticipante = 3;
            if ($perfilParticipante) {
                DB::table('security.profiles_users')->insert([
                    'id_users' => $user->id,
                    'id_rol' => $perfilParticipante,
                    'status' => true,
                    'creado_por' => $user->id,
                    'creado_en' => now()
                ]);
            }

            // 3. Crear registro en personas
            PersonalData::create([
                'user_id' => $user->id,
                'tipo_dni' => $request->tipo_dni,
                'dni' => $request->dni,
                'pasaporte' => $request->tipo_dni == 3 ? $request->dni : null, // Si es pasaporte, lo guardamos en ambas por compatibilidad
                'rif' => $request->rif,
                'genero' => $request->genero,
                'primer_nombre' => $request->primer_nombre,
                'segundo_nombre' => $request->segundo_nombre,
                'primer_apellido' => $request->primer_apellido,
                'segundo_apellido' => $request->segundo_apellido,
                'telefono'          => $request->telefono,
                'telefono_opcional' => $request->telefono_opcional,
                'id_pais'           => 238, // Venezuela por defecto
                'id_estado'         => $request->id_estado,
                'id_municipio' => $request->id_municipio,
                'id_parroquia' => $request->id_parroquia,
                'direccion' => $request->direccion,
                'creado_por' => $user->id,
                'creado_en' => now(),
            ]);

            DB::commit();

            return redirect()->route('login')->with('success', 'Registro completado de forma exitosa. Ahora puede iniciar sesión.');

        }
        catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error en el registro: ' . $e->getMessage())->withInput();
        }
    }

    // --- Métodos AJAX para Ubicación Geográfica ---

    /**
     * Retorna todos los estados (Venezuela).
     * La tabla comun.estados es exclusivamente venezolana; no requiere parámetros.
     * Columnas reales: id (PK), description (nombre del estado)
     */
    public function getEstados()
    {
        try {
            $estados = DB::table('comun.estados')
                ->select('id', 'description as name')
                ->orderBy('description')
                ->get();
        } catch (\Exception $e) {
            $estados = collect();
        }

        return response()->json($estados);
    }

    /**
     * Retorna los municipios de un estado.
     * Columnas reales: id (PK), name, state_id (FK → comun.estados.id)
     */
    public function getMunicipios($estado_id)
    {
        try {
            $municipios = DB::table('comun.municipios')
                ->where('state_id', $estado_id)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            $municipios = collect();
        }

        return response()->json($municipios);
    }

    /**
     * Retorna las parroquias de un municipio.
     * Columnas reales: id (PK), name, municipality_id (FK → comun.municipios.id)
     */
    public function getParroquias($municipio_id)
    {
        try {
            $parroquias = DB::table('comun.parroquias')
                ->where('municipality_id', $municipio_id)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            $parroquias = collect();
        }

        return response()->json($parroquias);
    }
}
