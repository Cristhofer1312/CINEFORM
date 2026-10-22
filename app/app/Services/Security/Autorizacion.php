<?php

namespace App\Services\Security;

use App\Constants\SecurityAction;
use App\Helpers\Encryptor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Punto único de decisión RBAC.
 *
 * Es el único código que lee `security.processes / permissions / profile_permissions`
 * para responder "¿puede el perfil activo hacer esta acción?". Carga todos los
 * permisos del perfil en una sola consulta, los cachea por versión y los memoiza
 * durante la request.
 *
 * Los helpers `hasPermission()` / `hasPermissionRoute()` y el middleware
 * `CheckPermission` delegan aquí.
 */
class Autorizacion
{
    /** Clave que se incrementa para invalidar todos los perfiles a la vez. */
    private const VERSION_KEY = 'rbac:version';

    private const TTL_SEGUNDOS = 600;

    /** @var array<int, array{rutas: array<string, true>, procesos: array<string, true>}> */
    private array $memo = [];

    /** Usuario raíz del sistema: bypass total (mismo criterio que CheckPermission). */
    public function esRaiz($user = null): bool
    {
        if ($user !== null) {
            return isset($user->id) && (int) $user->id === 1;
        }

        return Auth::check() && (int) Auth::id() === 1;
    }

    /** ID del perfil activo en sesión, o null si aún no se eligió uno. */
    public function perfilActivo(): ?int
    {
        $perfil = session()->get('profile_id');
        if (!$perfil) {
            return null;
        }

        if (!is_numeric($perfil)) {
            $perfil = Encryptor::decrypt($perfil);
        }

        return is_numeric($perfil) ? (int) $perfil : null;
    }

    /**
     * Resuelve el perfil activo para un usuario dado.
     *
     * - Si $user es null o es el usuario autenticado actual: usa la sesión activa.
     * - Si $user es un usuario distinto (e.g. Gate::forUser($otro)): consulta la BD
     *   directamente usando los atributos raw del modelo (sin pasar por el accessor
     *   getProfileIdAttribute, que caería a la sesión del actor).
     *
     * Para usuarios con varios perfiles asignados se toma el de mayor jerarquía
     *   (Administrador > Coordinador > Facilitador > Participante y, después,
     *   el de mayor id) como criterio determinista. El orden por id solo no
     *   sirve: un Facilitador que también es Participante (perfil 3) quedaría
     *   resuelto como Participante.
     */
    public function perfilActivoPara($user = null): ?int
    {
        // Caso 1: sin usuario especificado o es el usuario autenticado actual → sesión
        if ($user === null || (Auth::check() && (int) $user->id === (int) Auth::id())) {
            $perfilSesion = $this->perfilActivo();
            if ($perfilSesion !== null) {
                return $perfilSesion;
            }
        }

        if ($user === null || !isset($user->id)) {
            return null;
        }

        // Caso 2: usuario distinto al actor → consultar la BD directamente.
        // NO usar $user->profile_id porque getProfileIdAttribute() cae a session().
        return DB::table('security.profiles_users as pu')
            ->join('security.profiles as p', 'p.id', '=', 'pu.id_rol')
            ->where('pu.id_users', $user->id)
            ->where('pu.status', 1)    // columna del pivote (int 1 = activo)
            ->where('p.active', true)  // columna del perfil (boolean)
            ->orderByRaw(
                'CASE pu.id_rol WHEN 1 THEN 0 WHEN 4 THEN 1 WHEN 2 THEN 2 WHEN 3 THEN 3 ELSE 4 END'
            )
            ->orderByDesc('pu.id_rol') // desempate determinista para perfiles personalizados
            ->value('pu.id_rol');
    }

    /**
     * ¿Puede el perfil activo (o el usuario dado) ejecutar $accionId sobre el proceso identificado
     * por su `route` (ej. 'taller.cursos.index')?
     *
     * A diferencia de los helpers legacy, aquí SÍ aplica el bypass del usuario 1.
     */
    public function puede(string $procesoRoute, int $accionId, $user = null): bool
    {
        if ($this->esRaiz($user)) {
            return true;
        }

        $perfil = $this->perfilActivoPara($user);

        return $this->puedeComoPerfil($perfil, $procesoRoute, $accionId);
    }

    /** Variante por ID de proceso (compatibilidad con `hasPermission()`). */
    public function puedeEnProceso(int|string $procesoId, int $accionId, $user = null): bool
    {
        if ($this->esRaiz($user)) {
            return true;
        }

        $perfil = $this->perfilActivoPara($user);

        return $perfil !== null && $this->puedeEnProcesoComoPerfil($perfil, $procesoId, $accionId);
    }

    /** Sin bypass: evalúa un perfil concreto contra un proceso identificado por ID. */
    public function puedeEnProcesoComoPerfil(int $perfilId, int|string $procesoId, int $accionId): bool
    {
        $slug = SecurityAction::dbString($accionId);
        if (!$slug) {
            return false;
        }

        return isset($this->permisos($perfilId)['procesos'][$procesoId . '|' . $slug]);
    }

    /**
     * Sin bypass: evalúa un perfil concreto. Útil para tests y para el middleware
     * que ya resolvió el bypass por su cuenta.
     */
    public function puedeComoPerfil(?int $perfilId, string $procesoRoute, int $accionId): bool
    {
        $slug = SecurityAction::dbString($accionId);
        if (!$perfilId || !$slug) {
            return false;
        }

        return isset($this->permisos($perfilId)['rutas'][$procesoRoute . '|' . $slug]);
    }

    /** Registra el uso del bypass raíz (auditoría). */
    public function registrarBypass(string $proceso, int $accionId, $request): void
    {
        Log::channel('single')->info('[ADMIN-BYPASS] Acceso sin verificación RBAC', [
            'user_id' => Auth::id(),
            'process' => $proceso,
            'action_id' => $accionId,
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
        ]);
    }

    /** Invalida la caché de todos los perfiles (llamar al cambiar `profile_permissions`). */
    public function invalidar(): void
    {
        Cache::add(self::VERSION_KEY, 1);
        Cache::increment(self::VERSION_KEY);
        $this->memo = [];
    }

    /**
     * @return array{rutas: array<string, true>, procesos: array<string, true>}
     */
    private function permisos(int $perfilId): array
    {
        if (isset($this->memo[$perfilId])) {
            return $this->memo[$perfilId];
        }

        $version = Cache::get(self::VERSION_KEY, 1);

        return $this->memo[$perfilId] = Cache::remember(
            "rbac:perfil:{$perfilId}:v{$version}",
            self::TTL_SEGUNDOS,
            function () use ($perfilId) {
                $filas = DB::table('security.profile_permissions as pp')
                    ->join('security.permissions as p', 'p.id', '=', 'pp.permission_id')
                    ->join('security.processes as pr', 'pr.id', '=', 'p.process_id')
                    ->where('pp.profile_id', $perfilId)
                    ->get(['pr.id as proceso_id', 'pr.route as proceso_route', 'p.slug']);

                $rutas = [];
                $procesos = [];
                foreach ($filas as $fila) {
                    $rutas[$fila->proceso_route . '|' . $fila->slug] = true;
                    $procesos[$fila->proceso_id . '|' . $fila->slug] = true;
                }

                return ['rutas' => $rutas, 'procesos' => $procesos];
            }
        );
    }
}
