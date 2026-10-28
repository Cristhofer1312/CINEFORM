<?php

namespace Tests\Feature\Security;

use App\Constants\Perfil;
use App\Constants\SecurityAction;
use App\Helpers\Encryptor;
use App\Services\Security\Autorizacion;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Security\Entities\User;
use Tests\TestCase;

/**
 * Permisos y perfiles:
 * - RF-82 (decisión 1b): quien tiene los permisos de gestión de perfiles puede
 *   otorgar cualquier permiso, igual que el administrador. Perfiles: D8 (26/10),
 *   solo los que el actor posee (DelegacionPerfilesTest).
 * - P2: catálogos separan ver de crear/editar.
 * - P3/P4: guardado atómico, por diferencia y auditado.
 * - P5: el usuario raíz recibe el mismo resultado por cualquier camino.
 * - Elegibilidad única como facilitador.
 */
class PermisosPerfilesSeguridadTest extends TestCase
{
    use DatabaseTransactions;

    /** Usuario no raíz con el perfil Administrador (tiene GESTIONAR_PERMISOS). */
    private User $adminNoRaiz;

    private User $coordinador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminNoRaiz = User::whereHas('perfiles', fn ($q) => $q->where('security.profiles.id', Perfil::ADMINISTRADOR))
            ->where('id', '>', 1)->firstOrFail();

        $this->coordinador = User::whereHas('perfiles', fn ($q) => $q->where('security.profiles.id', Perfil::COORDINADOR))
            ->where('id', '>', 1)
            ->whereKeyNot($this->adminNoRaiz->id)
            ->first() ?? User::findOrFail(2);
    }

    // ── RF-82: gestión de permisos por permiso, sin jerarquía fija ─────────

    public function test_administrador_no_raiz_puede_otorgar_permisos_de_gestion(): void
    {
        $this->comoAdminNoRaiz()
            ->post(route('profiles.permissions', ['id' => Encryptor::encrypt(Perfil::COORDINADOR)]),
                ['permissions' => $this->formularioCon(Perfil::COORDINADOR, ['profiles:permissions'])])
            ->assertRedirect(route('profiles'))
            ->assertSessionHasNoErrors();

        $this->assertContains('profiles:permissions', $this->clavesDelPerfil(Perfil::COORDINADOR));
    }

    public function test_coordinador_con_permiso_de_gestionar_permisos_hace_lo_mismo_que_el_administrador(): void
    {
        // Se le otorga al Coordinador el permiso de gestionar permisos de perfiles.
        $this->otorgarPermiso(Perfil::COORDINADOR, 'profiles', 'permissions');

        // Puede otorgar incluso un permiso que su propio perfil no tiene.
        $clave = 'taller.postulacion-facilitador.landing:apply_facilitator';
        $this->assertNotContains($clave, $this->clavesDelPerfil(Perfil::COORDINADOR));

        $this->como($this->coordinador, Perfil::COORDINADOR)
            ->post(route('profiles.permissions', ['id' => Encryptor::encrypt(Perfil::FACILITADOR)]),
                ['permissions' => $this->formularioCon(Perfil::FACILITADOR, [$clave])])
            ->assertSessionHasNoErrors();

        $this->assertContains($clave, $this->clavesDelPerfil(Perfil::FACILITADOR));
    }

    public function test_sin_permiso_de_gestionar_permisos_recibe_403(): void
    {
        // El Coordinador no tiene profiles:permissions en la BD actual.
        $this->assertNotContains('profiles:permissions', $this->clavesDelPerfil(Perfil::COORDINADOR));

        $this->como($this->coordinador, Perfil::COORDINADOR)
            ->post(route('profiles.permissions', ['id' => Encryptor::encrypt(Perfil::FACILITADOR)]),
                ['permissions' => $this->formularioCon(Perfil::FACILITADOR)])
            ->assertForbidden();
    }

    public function test_desmarcar_un_permiso_lo_quita(): void
    {
        $clave = 'taller.postulacion-facilitador.landing:apply_facilitator';
        $this->assertContains($clave, $this->clavesDelPerfil(Perfil::PARTICIPANTE));

        $this->comoAdminNoRaiz()
            ->post(route('profiles.permissions', ['id' => Encryptor::encrypt(Perfil::PARTICIPANTE)]),
                ['permissions' => $this->formularioCon(Perfil::PARTICIPANTE, [], [$clave])])
            ->assertSessionHasNoErrors();

        $this->assertNotContains($clave, $this->clavesDelPerfil(Perfil::PARTICIPANTE));
    }

    public function test_cambio_permitido_se_guarda_y_queda_auditado(): void
    {
        $clave = 'taller.catalogos.index:view';
        $this->assertNotContains($clave, $this->clavesDelPerfil(Perfil::FACILITADOR));

        $this->comoAdminNoRaiz()
            ->post(route('profiles.permissions', ['id' => Encryptor::encrypt(Perfil::FACILITADOR)]),
                ['permissions' => $this->formularioCon(Perfil::FACILITADOR, [$clave])])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profiles'));

        $this->assertContains($clave, $this->clavesDelPerfil(Perfil::FACILITADOR));

        $registro = DB::table('security.auditoria')
            ->where('accion', 'perfil.permisos.actualizar')
            ->where('entidad_id', (string) Perfil::FACILITADOR)
            ->latest('id')->first();

        $this->assertNotNull($registro, 'El cambio de permisos debe quedar auditado.');
        $this->assertSame($this->adminNoRaiz->id, (int) $registro->user_id);
        $this->assertNotContains($clave, json_decode($registro->antes, true)['permisos']);
        $this->assertContains($clave, json_decode($registro->despues, true)['permisos']);
    }

    public function test_pantalla_de_permisos_lista_las_acciones_de_la_tabla_de_permisos(): void
    {
        $procesoCatalogos = DB::table('security.processes')->where('codigo', 'taller.catalogos.index')->value('id');

        $html = $this->comoAdminNoRaiz()
            ->get(route('profiles.permissions', ['id' => Encryptor::encrypt(Perfil::COORDINADOR)]))
            ->assertOk()
            ->getContent();

        // El id cifrado del proceso cambia en cada cifrado (IV aleatorio): se descifra.
        preg_match_all('/name="permissions\[([^\]]+)\]\[create\]"/', $html, $m);
        $procesos = array_map(fn ($c) => (int) Encryptor::decrypt($c), $m[1]);

        $this->assertContains((int) $procesoCatalogos, $procesos, 'La casilla "create" de catálogos debe salir de security.permissions.');
    }

    // ── RF-83: catálogo único y procesos con código estable ────────────────

    public function test_pantalla_muestra_nombre_y_descripcion_del_permiso_desde_la_bd(): void
    {
        $permisoId = DB::table('security.permissions as p')
            ->join('security.processes as pr', 'pr.id', '=', 'p.process_id')
            ->where('pr.codigo', 'taller.catalogos.index')->where('p.slug', 'create')
            ->value('p.id');
        DB::table('security.permissions')->where('id', $permisoId)
            ->update(['name' => 'Nombre de prueba RF-83', 'description' => 'Descripción de prueba RF-83']);

        $this->comoAdminNoRaiz()
            ->get(route('profiles.permissions', ['id' => Encryptor::encrypt(Perfil::COORDINADOR)]))
            ->assertOk()
            ->assertSee('Nombre de prueba RF-83')
            ->assertSee('Descripción de prueba RF-83');
    }

    public function test_proceso_fuera_del_menu_no_aparece_en_el_menu_pero_sus_permisos_funcionan(): void
    {
        DB::table('security.processes')->where('codigo', 'taller.catalogos.index')->update(['en_menu' => false]);

        $this->actingAs($this->coordinador);
        session(['profile_id' => Perfil::COORDINADOR]);

        $codigosEnMenu = collect($this->coordinador->captureMenu())
            ->flatMap(fn ($menu) => $menu['processes'])
            ->pluck('codigo');

        $this->assertNotContains('taller.catalogos.index', $codigosEnMenu);
        $this->assertTrue(app(Autorizacion::class)->puede('taller.catalogos.index', SecurityAction::VER));
    }

    public function test_la_autorizacion_no_depende_del_nombre_de_ruta(): void
    {
        DB::table('security.processes')->where('codigo', 'taller.catalogos.index')->update(['route' => 'ruta.renombrada']);
        app(Autorizacion::class)->invalidar();

        $this->actingAs($this->coordinador);
        session(['profile_id' => Perfil::COORDINADOR]);

        $this->assertTrue(app(Autorizacion::class)->puede('taller.catalogos.index', SecurityAction::CREAR));
    }

    // ── P2: catálogos ──────────────────────────────────────────────────────

    public function test_ver_catalogos_no_permite_crear_ni_editar(): void
    {
        $this->quitarPermiso(Perfil::COORDINADOR, 'taller.catalogos.index', ['create', 'edit']);

        $comoCoordinador = fn () => $this->como($this->coordinador, Perfil::COORDINADOR);

        $comoCoordinador()->get(route('taller.catalogos.index'))
            ->assertOk()
            ->assertDontSee('data-bs-target="#modalCrearActividad"', false)
            ->assertSee('Solo lectura');

        $comoCoordinador()->post(route('taller.catalogos.actividades.store'), ['nombre' => 'Intento', 'abreviatura' => 'INT'])
            ->assertForbidden();

        $this->assertDatabaseMissing('taller.actividades_formativas', ['abreviatura' => 'INT']);
    }

    public function test_con_permiso_de_crear_se_muestra_el_boton(): void
    {
        $this->como($this->coordinador, Perfil::COORDINADOR)
            ->get(route('taller.catalogos.index'))
            ->assertOk()
            ->assertSee('data-bs-target="#modalCrearActividad"', false);
    }

    // ── RF-82: asignación de perfiles ──────────────────────────────────────

    public function test_asignacion_deja_los_perfiles_marcados_conserva_los_ajenos_y_queda_auditada(): void
    {
        $destino = $this->adminNoRaiz;
        $this->assertNotSame($destino->id, $this->coordinador->id);

        // D8: el coordinador tiene Facilitador, Participante y Coordinador, pero no
        // Administrador, así que no puede quitarlo. Se fija aquí para no depender de los datos.
        DB::table('security.profiles_users')->where('id_users', $this->coordinador->id)->delete();
        DB::table('security.profiles_users')->insert(array_map(fn ($idRol) => [
            'id_rol' => $idRol, 'id_users' => $this->coordinador->id, 'status' => 1,
            'creado_por' => 1, 'creado_en' => now(), 'actualizado_por' => 1, 'actualizado_en' => now(),
        ], [Perfil::FACILITADOR, Perfil::PARTICIPANTE, Perfil::COORDINADOR]));
        DB::table('security.profiles_users')->where('id_users', $destino->id)->delete();
        DB::table('security.profiles_users')->insert(array_map(fn ($idRol) => [
            'id_rol' => $idRol, 'id_users' => $destino->id, 'status' => 1,
            'creado_por' => 1, 'creado_en' => now(), 'actualizado_por' => 1, 'actualizado_en' => now(),
        ], [Perfil::ADMINISTRADOR, Perfil::FACILITADOR]));
        $antes = [Perfil::ADMINISTRADOR, Perfil::FACILITADOR];

        $this->como($this->coordinador, Perfil::COORDINADOR)
            ->post(route('users.assign_profiles'), [
                'id_persona' => $destino->personalData->id_persona,
                'perfiles' => [Perfil::PARTICIPANTE, Perfil::COORDINADOR],
            ])
            ->assertJson(['success' => true]);

        $this->assertSame([Perfil::ADMINISTRADOR, Perfil::PARTICIPANTE, Perfil::COORDINADOR], $this->perfilesDe($destino->id));

        $registro = DB::table('security.auditoria')
            ->where('accion', 'usuario.perfiles.asignar')
            ->where('entidad_id', (string) $destino->id)
            ->latest('id')->first();
        $this->assertNotNull($registro);
        $this->assertSame($antes, json_decode($registro->antes, true)['perfiles']);
    }

    public function test_no_se_pueden_modificar_los_perfiles_propios(): void
    {
        $this->como($this->coordinador, Perfil::COORDINADOR)
            ->post(route('users.assign_profiles'), [
                'id_persona' => $this->coordinador->personalData->id_persona,
                'perfiles' => [Perfil::ADMINISTRADOR],
            ])
            ->assertForbidden();
    }

    // ── P4: auditoría inmutable ────────────────────────────────────────────

    public function test_auditoria_no_admite_modificaciones(): void
    {
        DB::table('security.auditoria')->insert(['accion' => 'prueba', 'entidad' => 'test', 'created_at' => now()]);

        $rechazado = false;
        try {
            // Transacción anidada = savepoint: el error no invalida la transacción del test.
            DB::transaction(fn () => DB::table('security.auditoria')->where('accion', 'prueba')->update(['accion' => 'alterada']));
        } catch (QueryException $e) {
            $rechazado = str_contains($e->getMessage(), 'solo inserción');
        }

        $this->assertTrue($rechazado, 'La tabla de auditoría debe rechazar UPDATE.');
        $this->assertDatabaseHas('security.auditoria', ['accion' => 'prueba']);
    }

    // ── P5: usuario raíz consistente ───────────────────────────────────────

    public function test_raiz_tiene_el_mismo_resultado_en_helpers_y_servicio(): void
    {
        $this->actingAs(User::findOrFail(1));

        $this->assertTrue(app(Autorizacion::class)->puede('taller.cursos.index', SecurityAction::GESTIONAR_CURSO));
        $this->assertTrue(hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO));
    }

    // ── Elegibilidad única como facilitador ────────────────────────────────

    public function test_elegibilidad_de_facilitador_depende_del_permiso_y_de_la_asignacion_activa(): void
    {
        $autorizacion = app(Autorizacion::class);

        $facilitador = User::whereHas('perfiles', fn ($q) => $q->where('security.profiles.id', Perfil::FACILITADOR))
            ->where('id', '>', 1)->firstOrFail();
        $participante = User::findOrFail(7);

        $this->assertTrue($autorizacion->esElegibleFacilitador($facilitador));
        $this->assertFalse($autorizacion->esElegibleFacilitador($participante));

        // Con todas sus asignaciones inactivas deja de ser elegible.
        DB::table('security.profiles_users')->where('id_users', $facilitador->id)->update(['status' => 0]);
        $this->assertFalse($autorizacion->esElegibleFacilitador($facilitador));
    }

    // ── Utilidades ─────────────────────────────────────────────────────────

    private function comoAdminNoRaiz()
    {
        return $this->como($this->adminNoRaiz, Perfil::ADMINISTRADOR);
    }

    /**
     * Sesión autenticada con perfil activo. Incluye el session_token del usuario:
     * Authenticate cierra la sesión si el usuario tiene uno en BD y no coincide.
     */
    private function como(User $user, int $perfil)
    {
        return $this->actingAs($user)->withSession([
            'profile_id' => $perfil,
            'session_token' => $user->session_token,
        ]);
    }

    /** Claves "ruta:slug" que tiene un perfil, ordenadas. */
    private function clavesDelPerfil(int $perfilId): array
    {
        return DB::table('security.profile_permissions as pp')
            ->join('security.permissions as p', 'p.id', '=', 'pp.permission_id')
            ->join('security.processes as pr', 'pr.id', '=', 'p.process_id')
            ->where('pp.profile_id', $perfilId)
            ->orderBy('pr.codigo')->orderBy('p.slug')
            ->get(['pr.codigo', 'p.slug'])
            ->map(fn ($f) => $f->codigo.':'.$f->slug)
            ->all();
    }

    /**
     * Arma el arreglo `permissions[crypt_proceso][slug]` como lo envía la pantalla:
     * los permisos actuales del perfil, más $agregar, menos $omitir.
     */
    private function formularioCon(int $perfilId, array $agregar = [], array $omitir = []): array
    {
        $claves = array_values(array_diff(array_unique(array_merge($this->clavesDelPerfil($perfilId), $agregar)), $omitir));
        $procesos = DB::table('security.processes')->pluck('id', 'codigo');

        $formulario = [];
        foreach ($claves as $clave) {
            [$ruta, $slug] = explode(':', $clave);
            $formulario[Encryptor::encrypt($procesos[$ruta])][$slug] = 'on';
        }

        return $formulario;
    }

    private function otorgarPermiso(int $perfilId, string $ruta, string $slug): void
    {
        $permisoId = DB::table('security.permissions as p')
            ->join('security.processes as pr', 'pr.id', '=', 'p.process_id')
            ->where('pr.codigo', $ruta)->where('p.slug', $slug)
            ->value('p.id');

        DB::table('security.profile_permissions')->insertOrIgnore([
            'profile_id' => $perfilId,
            'permission_id' => $permisoId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        app(Autorizacion::class)->invalidar();
    }

    private function quitarPermiso(int $perfilId, string $ruta, array $slugs): void
    {
        $ids = DB::table('security.permissions as p')
            ->join('security.processes as pr', 'pr.id', '=', 'p.process_id')
            ->where('pr.codigo', $ruta)->whereIn('p.slug', $slugs)
            ->pluck('p.id');

        DB::table('security.profile_permissions')->where('profile_id', $perfilId)->whereIn('permission_id', $ids)->delete();
        app(Autorizacion::class)->invalidar();
    }

    private function perfilesDe(int $userId): array
    {
        return DB::table('security.profiles_users')->where('id_users', $userId)
            ->orderBy('id_rol')->pluck('id_rol')->map(fn ($id) => (int) $id)->all();
    }
}
