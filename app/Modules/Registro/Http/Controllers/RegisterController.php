<?php

namespace Modules\Registro\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Modules\Comun\Entities\PersonalData;
use Modules\Security\Entities\Codes;
use Modules\Security\Entities\Countries;
use Modules\Security\Entities\User;

class RegisterController extends Controller
{
    /**
     * Prefijos de telefonía móvil de Venezuela, con su operadora. El teléfono se
     * guarda como prefijo + 7 dígitos (11 en total), igual que el resto de
     * teléfonos en comun.personas.
     */
    public const PREFIJOS_TELEFONO = [
        '0412' => 'Digitel',
        '0422' => 'Digitel',
        '0414' => 'Movistar',
        '0424' => 'Movistar',
        '0416' => 'Movilnet',
        '0426' => 'Movilnet',
    ];

    /**
     * Muestra el formulario de registro
     *
     * @return Renderable
     */
    public function index()
    {
        $documentTypes = DB::table('security.document_types')->get();
        $genders = DB::table('security.genders')->get();
        $prefijosTelefono = self::PREFIJOS_TELEFONO;

        return view('registro::register', compact('documentTypes', 'genders', 'prefijosTelefono'));
    }

    /** Regla de validación: un prefijo de la lista seguido de 7 dígitos. */
    private static function reglaTelefono(): string
    {
        return 'regex:/^('.implode('|', array_keys(self::PREFIJOS_TELEFONO)).')\d{7}$/';
    }

    private function generateCode()
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Envía un código de verificación al correo electrónico.
     */
    public function sendCode(Request $request)
    {
        $email = strtolower($request->validate([
            'email' => 'required|string|email|max:100',
        ])['email']);

        $generateCode = $this->generateCode();
        $user = User::where('email', $email)->first();

        if ($user == null) {
            $code = Codes::where('email', $email)->first();
            if ($code != null) {
                $date = Carbon::parse($code->date);
                $minutesSinceLastRequest = $date->diffInMinutes(Carbon::now());
                if ($minutesSinceLastRequest > 15) {
                    $code->date = now();
                    $code->code = $generateCode;
                    $code->save();
                } else {
                    return response()->json(['type' => 'danger', 'message' => __('You have an active code, you must wait 15 minutes'), 'status' => '0']);
                }
            } else {
                $code = new Codes;
                $code->code = $generateCode;
                $code->email = $email;
                $code->date = now();
                $code->save();
            }

            $subject = __('Registration Code');
            $for = $code->email;

            try {
                Mail::send('registro::emails.send_code', ['code' => $generateCode], function ($email) use ($subject, $for) {
                    $email->subject($subject);
                    $email->to($for);
                });
            } catch (\Exception $e) {
                report($e);
                return response()->json(['type' => 'danger', 'message' => __('Error sending email: ').(config('app.debug') ? $e->getMessage() : 'intente nuevamente o contacte al administrador.'), 'status' => '0']);
            }

            return response()->json(['type' => 'success', 'message' => __('A code has been sent to your email'), 'status' => '1']);
        } else {
            return response()->json(['type' => 'danger', 'message' => __('A User Already Exists with That Email'), 'status' => '0']);
        }
    }

    /**
     * Guarda el nuevo usuario y perfil
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $prefijos = implode(', ', array_keys(self::PREFIJOS_TELEFONO));

        // Solo lo validado llega a la BD (sin $request->input sueltos).
        $datos = $request->validate([
            // Cada campo con tipo y largo máximo (el de la columna o menor): sin
            // ellos, un valor largo o un id inexistente llegaba al INSERT y fallaba.
            'username' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique(User::class, 'username')],
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique(User::class, 'email')],
            'code' => 'required|digits:6',
            'password' => ['required', 'string', 'max:72', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'tipo_dni' => ['required', 'integer', Rule::exists('pgsql.security.document_types', 'id')],
            'dni' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9-]+$/',
                Rule::unique(PersonalData::class, 'dni'),
            ],
            /* 'pasaporte' => [
                'nullable',
                Rule::unique(PersonalData::class, 'pasaporte')
            ], */
            'rif' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9-]+$/',
                Rule::unique(PersonalData::class, 'rif'),
            ],
            'genero' => ['required', 'integer', Rule::exists('pgsql.security.genders', 'id')],
            'primer_nombre' => 'required|string|max:100',
            'segundo_nombre' => 'nullable|string|max:100',
            'primer_apellido' => 'required|string|max:100',
            'segundo_apellido' => 'nullable|string|max:100',
            'telefono' => ['required', 'string', self::reglaTelefono()],
            'telefono_opcional' => ['nullable', 'string', self::reglaTelefono()],
            'id_estado' => ['nullable', 'integer', Rule::exists('pgsql.comun.estados', 'id')],
            'id_municipio' => ['nullable', 'integer', Rule::exists('pgsql.comun.municipios', 'id')->where('state_id', $request->input('id_estado'))],
            'id_parroquia' => ['nullable', 'integer', Rule::exists('pgsql.comun.parroquias', 'id')->where('municipality_id', $request->input('id_municipio'))],
            'direccion' => 'nullable|string|max:255',
        ], [
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique' => 'Este nombre de usuario ya está en uso, por favor elija otro.',
            'dni.unique' => 'Este DNI ya se encuentra registrado en el sistema.',
            'email.unique' => 'Este correo electrónico ya está en uso por otro usuario.',
            'pasaporte.unique' => 'Este pasaporte ya se encuentra registrado.',
            'rif.unique' => 'Este RIF ya se encuentra registrado.',
            'code.required' => 'El código de verificación es obligatorio.',
            'telefono.regex' => 'El teléfono principal debe tener un prefijo válido ('.$prefijos.') y 7 dígitos.',
            'telefono_opcional.regex' => 'El otro teléfono debe tener un prefijo válido ('.$prefijos.') y 7 dígitos.',
        ]);

        // Validar el código de verificación. Tras 5 códigos errados para un mismo
        // correo hay que esperar: sin tope, 6 dígitos se adivinan por fuerza bruta.
        $claveIntentos = 'registro-codigo:'.sha1(strtolower($datos['email']));
        if (RateLimiter::tooManyAttempts($claveIntentos, 5)) {
            return back()->with('error', 'Demasiados códigos incorrectos. Espere 15 minutos y solicite un código nuevo.')->withInput();
        }

        $findCodeExists = Codes::where('email', strtolower($datos['email']))
            ->where('code', $datos['code'])
            ->where('processed', false)
            ->first();

        if (! $findCodeExists) {
            RateLimiter::hit($claveIntentos, 900);

            return back()->with('error', 'El código de verificación es inválido o ya ha sido procesado.')->withInput();
        }

        RateLimiter::clear($claveIntentos);

        // El código caduca a los 15 minutos (el correo lo indica así)
        if (Carbon::parse($findCodeExists->date)->diffInMinutes(Carbon::now()) > 15) {
            return back()->with('error', 'El código de verificación ha expirado. Solicita uno nuevo.')->withInput();
        }

        DB::beginTransaction();

        try {
            $venezuela = Countries::where('iso2', 've')->first();

            // 1. Crear el usuario
            $user = new User([
                'username' => strtolower($datos['username']),
                'email' => strtolower($datos['email']),
                'password' => Hash::make($datos['password']),
            ]);

            // Campos de auditoría y activación requeridos por el Módulo de Seguridad
            $user->register_date = now();
            $user->ip = $request->ip();
            $user->active = 1;
            $user->country_id = $venezuela?->id;
            $user->address = $datos['direccion'] ?? null;
            $user->save();

            // Marcar código como procesado
            $findCodeExists->processed = true;
            $findCodeExists->save();

            // 2. Asignar perfil de "Participante" (Asumiendo que existe en security_profiles)
            $perfilParticipante = 3;
            if ($perfilParticipante) {
                DB::table('security.profiles_users')->insert([
                    'id_users' => $user->id,
                    'id_rol' => $perfilParticipante,
                    'status' => true,
                    'creado_por' => $user->id,
                    'creado_en' => now(),
                ]);
            }

            // 3. Crear registro en personas
            PersonalData::create([
                'user_id' => $user->id,
                'tipo_dni' => $datos['tipo_dni'],
                'dni' => $datos['dni'],
                'pasaporte' => $datos['tipo_dni'] == 3 ? $datos['dni'] : null, // Si es pasaporte, lo guardamos en ambas por compatibilidad
                'rif' => $datos['rif'] ?? null,
                'genero' => $datos['genero'],
                'primer_nombre' => $datos['primer_nombre'],
                'segundo_nombre' => $datos['segundo_nombre'] ?? null,
                'primer_apellido' => $datos['primer_apellido'],
                'segundo_apellido' => $datos['segundo_apellido'] ?? null,
                'telefono' => $datos['telefono'],
                'telefono_opcional' => $datos['telefono_opcional'] ?? null,
                'id_pais' => $venezuela?->id, // Venezuela resuelto por iso2
                'id_estado' => $datos['id_estado'] ?? null,
                'id_municipio' => $datos['id_municipio'] ?? null,
                'id_parroquia' => $datos['id_parroquia'] ?? null,
                'direccion' => $datos['direccion'] ?? null,
                'creado_por' => $user->id,
                'creado_en' => now(),
            ]);

            DB::commit();

            // Enviar correo de registro exitoso
            try {
                $subject = 'Registro Exitoso - CINEFORM';
                Mail::send('registro::emails.registro_exitoso', ['user' => $user], function ($m) use ($user, $subject) {
                    $m->to($user->email)->subject($subject);
                });
            } catch (\Exception $e) {
                // No detenemos el proceso si falla el correo de éxito, pero podrías loguearlo.
            }

            return redirect()->route('login')->with('success', 'Registro completado de forma exitosa. Ahora puede iniciar sesión.');

        } catch (\Exception $e) {
            DB::rollBack();

            \Illuminate\Support\Facades\Log::error('Error en el registro de usuario', ['error' => $e->getMessage()]);

            return back()->with('error', 'Ocurrió un error en el registro. Intente de nuevo o contacte al administrador.')->withInput();
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
