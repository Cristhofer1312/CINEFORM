<?php

namespace App\Services\Security;

use App\Constants\SecurityAction;
use App\Helpers\Encryptor;
use Illuminate\Database\Eloquent\Builder;
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

    /** Proceso que agrupa los permisos del dominio de cursos. */
    public const PROCESO_CURSOS = 'taller.cursos.index';

    /** @var array<int, array{codigos: array<string, true>, procesos: array<string, true>}> */
    private array $memo = [];

    /** Usuario raíz del sistema: bypass total (mismo criterio que CheckPermission). */
    public function esRaiz($user = null): bool
    {
        if ($user !== null) {
            return isset($user->id) && (int) $user->id === 1;
        }

        return Auth::check() && (int) Auth::id() === 1;
    }

    /** @var array<int, int[]> perfiles activos memoizados por id de usuario */
    private array $memoPerfiles = [];

    /**
     * Perfiles activos del usuario (asignación con status 1 y perfil activo).
     *
     * Desde el 26/10 los permisos de un usuario son la suma de todos sus
     * perfiles activos: ya no se elige un perfil al entrar. Se consulta la BD
     * y no la sesión, así que desactivar un perfil surte efecto en la
     * siguiente petición.
     *
     * @return int[]
     */
    public function perfilesActivosPara($user = null): array
    {
        $user ??= Auth::user();
        if ($user === null || ! isset($user->id)) {
            return [];
        }

        return $this->memoPerfiles[(int) $user->id] ??= DB::table('security.profiles_users as pu')
            ->join('security.profiles as p', 'p.id', '=', 'pu.id_rol')
            ->where('pu.id_users', $user->id)
            ->where('pu.status', 1)
            ->where('p.active', true)
            ->orderBy('pu.id_rol')
            ->pluck('pu.id_rol')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Perfil de mayor jerarquía entre los activos (Administrador > Coordinador >
     * Facilitador > Participante > personalizados por id). Solo se guarda en
     * sesión como dato informativo (auditoría, `profile_id` heredado); no decide
     * permisos.
     */
    public function perfilPrincipalPara($user = null): ?int
    {
        $orden = [1 => 0, 4 => 1, 2 => 2, 3 => 3];
        $perfiles = $this->perfilesActivosPara($user);
        usort($perfiles, fn ($a, $b) => [$orden[$a] ?? 4, -$a] <=> [$orden[$b] ?? 4, -$b]);

        return $perfiles[0] ?? null;
    }

    /** ID del perfil guardado en sesión (solo informativo, p. ej. auditoría). */
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
     * ¿Puede el perfil activo (o el usuario dado) ejecutar $accionId sobre el proceso
     * identificado por su `codigo` estable (ej. 'taller.catalogos.index')?
     *
     * Aplica el bypass del usuario raíz.
     */
    public function puede(string $procesoCodigo, int $accionId, $user = null): bool
    {
        if ($this->esRaiz($user)) {
            return true;
        }

        foreach ($this->perfilesActivosPara($user) as $perfil) {
            if ($this->puedeComoPerfil($perfil, $procesoCodigo, $accionId)) {
                return true;
            }
        }

        return false;
    }

    /** Variante por ID de proceso (compatibilidad con `hasPermission()`). */
    public function puedeEnProceso(int|string $procesoId, int $accionId, $user = null): bool
    {
        if ($this->esRaiz($user)) {
            return true;
        }

        foreach ($this->perfilesActivosPara($user) as $perfil) {
            if ($this->puedeEnProcesoComoPerfil($perfil, $procesoId, $accionId)) {
                return true;
            }
        }

        return false;
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
    public function puedeComoPerfil(?int $perfilId, string $procesoCodigo, int $accionId): bool
    {
        $slug = SecurityAction::dbString($accionId);
        if (!$perfilId || !$slug) {
            return false;
        }

        return isset($this->permisos($perfilId)['codigos'][$procesoCodigo . '|' . $slug]);
    }

    /**
     * Usuarios elegibles como facilitador: cuenta activa y al menos un perfil
     * activo, con asignación activa, que tenga EDITAR_CURSO sobre cursos.
     *
     * Es la única definición de elegibilidad: la usan los listados de candidatos
     * y la validación de reasignación, para que no puedan divergir.
     */
    public function consultaElegiblesFacilitador(): Builder
    {
        $slug = SecurityAction::dbString(SecurityAction::EDITAR_CURSO);

        return \Modules\Security\Entities\User::query()
            ->where('active', 1)
            ->whereHas('perfiles', function ($perfiles) use ($slug) {
                $perfiles->where('security.profiles.active', true)
                    ->where('security.profiles_users.status', 1)
                    ->whereHas('permissions', function ($permisos) use ($slug) {
                        $permisos->where('security.permissions.slug', $slug)
                            ->whereHas('process', fn ($proceso) => $proceso->where('security.processes.codigo', self::PROCESO_CURSOS));
                    });
            });
    }

    public function esElegibleFacilitador($user): bool
    {
        return $user !== null && isset($user->id)
            && $this->consultaElegiblesFacilitador()->whereKey($user->id)->exists();
    }

    /** Registra el uso del bypass raíz (auditoría). */
    public function registrarBypass(string $proceso, int $accionId, $request): void
    {
        Log::channel('single')->info('[ADMIN-BYPASS] Acceso sin verificación RBAC', [
            'user_id' => Auth::id(),
            'process' => $proceso,
            'action_id' => $accionId,
            'ip' => $request->ip(),
            // Solo la ruta: el query string puede llevar búsquedas o tokens.
            'url' => $request->path(),
            'method' => $request->method(),
        ]);
    }

    /**
     * D8: perfiles activos que el usuario puede asignar a otros, que son solo los
     * que él mismo posee (la raíz, todos). Única regla para la asignación de
     * perfiles y para la creación/edición de usuarios.
     *
     * @return int[]
     */
    public function perfilesAsignables($user = null): array
    {
        $user ??= Auth::user();
        if ($this->esRaiz($user)) {
            return DB::table('security.profiles')->where('active', true)
                ->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return $this->perfilesActivosPara($user);
    }

    /** Olvida los perfiles memorizados (inicio de cada petición). */
    public function olvidarPerfiles(): void
    {
        $this->memoPerfiles = [];
    }

    /** Invalida la caché de todos los perfiles (llamar al cambiar `profile_permissions`). */
    public function invalidar(): void
    {
        Cache::add(self::VERSION_KEY, 1);
        Cache::increment(self::VERSION_KEY);
        $this->memo = [];
        $this->memoPerfiles = [];
    }

    /**
     * @return array{codigos: array<string, true>, procesos: array<string, true>}
     */
    private function permisos(int $perfilId): array
    {
        if (isset($this->memo[$perfilId])) {
            return $this->memo[$perfilId];
        }

        $version = Cache::get(self::VERSION_KEY, 1);

        // "codigos" en la clave: la estructura cambió (antes "rutas"); así no se
        // leen entradas antiguas de la caché tras desplegar.
        return $this->memo[$perfilId] = Cache::remember(
            "rbac:codigos:perfil:{$perfilId}:v{$version}",
            self::TTL_SEGUNDOS,
            function () use ($perfilId) {
                // Los procesos se identifican por `codigo` (estable), no por el nombre de ruta.
                $filas = DB::table('security.profile_permissions as pp')
                    ->join('security.permissions as p', 'p.id', '=', 'pp.permission_id')
                    ->join('security.processes as pr', 'pr.id', '=', 'p.process_id')
                    ->where('pp.profile_id', $perfilId)
                    ->get(['pr.id as proceso_id', 'pr.codigo as proceso_codigo', 'p.slug']);

                $codigos = [];
                $procesos = [];
                foreach ($filas as $fila) {
                    $codigos[$fila->proceso_codigo . '|' . $fila->slug] = true;
                    $procesos[$fila->proceso_id . '|' . $fila->slug] = true;
                }

                return ['codigos' => $codigos, 'procesos' => $procesos];
            }
        );
    }
}
