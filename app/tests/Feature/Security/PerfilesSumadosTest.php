<?php

namespace Tests\Feature\Security;

use App\Constants\Perfil;
use App\Constants\SecurityAction;
use App\Helpers\Encryptor;
use App\Services\Security\Autorizacion;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Security\Entities\User;
use Tests\TestCase;

/**
 * 26/10: se elimina la selección de perfil. Los permisos de un usuario son la
 * suma de todos sus perfiles activos, sin importar el `profile_id` de sesión.
 */
class PerfilesSumadosTest extends TestCase
{
    use DatabaseTransactions;

    private const POSTULAR = ['taller.postulacion-facilitador.landing', 'apply_facilitator'];

    private User $usuario;

    private User $coordinador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::findOrFail(5);
        $this->coordinador = User::findOrFail(2);
    }

    public function test_los_permisos_suman_todos_los_perfiles_activos(): void
    {
        $autorizacion = app(Autorizacion::class);

        $this->soloConPerfiles($this->usuario, Perfil::FACILITADOR);
        $this->assertTrue($autorizacion->puede(Autorizacion::PROCESO_CURSOS, SecurityAction::EDITAR_CURSO, $this->usuario));
        $this->assertFalse($this->puedePostular());

        $this->soloConPerfiles($this->usuario, Perfil::FACILITADOR, Perfil::PARTICIPANTE);
        $this->assertTrue($autorizacion->puede(Autorizacion::PROCESO_CURSOS, SecurityAction::EDITAR_CURSO, $this->usuario));
        $this->assertTrue($this->puedePostular());
    }

    public function test_el_perfil_de_sesion_no_restringe_los_permisos(): void
    {
        $this->soloConPerfiles($this->usuario, Perfil::FACILITADOR, Perfil::PARTICIPANTE);

        // Aunque la sesión diga Participante, sigue pudiendo lo del Facilitador.
        $this->actingAs($this->usuario)->withSession([
            'profile_id' => Perfil::PARTICIPANTE,
            'session_token' => $this->usuario->session_token,
        ])->get(route('home'));

        $this->assertTrue(app(Autorizacion::class)->puede(Autorizacion::PROCESO_CURSOS, SecurityAction::EDITAR_CURSO));
    }

    public function test_un_perfil_desactivado_deja_de_sumar(): void
    {
        $this->soloConPerfiles($this->usuario, Perfil::FACILITADOR, Perfil::PARTICIPANTE);
        DB::table('security.profiles_users')->where('id_users', $this->usuario->id)
            ->where('id_rol', Perfil::PARTICIPANTE)->update(['status' => 0]);
        app(Autorizacion::class)->invalidar();

        $this->assertFalse($this->puedePostular());
    }

    public function test_el_menu_es_la_union_de_los_perfiles(): void
    {
        $procesos = fn () => collect($this->usuario->fresh()->captureMenu())
            ->flatMap(fn ($menu) => array_column($menu['processes'], 'id'))->sort()->values()->all();

        $this->soloConPerfiles($this->usuario, Perfil::FACILITADOR);
        $soloFacilitador = $procesos();
        $this->soloConPerfiles($this->usuario, Perfil::PARTICIPANTE);
        $soloParticipante = $procesos();
        $this->soloConPerfiles($this->usuario, Perfil::FACILITADOR, Perfil::PARTICIPANTE);

        $this->assertEqualsCanonicalizing(
            array_values(array_unique(array_merge($soloFacilitador, $soloParticipante))),
            $procesos()
        );
    }

    public function test_el_middleware_guarda_el_perfil_principal_sin_pedir_eleccion(): void
    {
        $this->soloConPerfiles($this->usuario, Perfil::FACILITADOR, Perfil::PARTICIPANTE);

        $this->actingAs($this->usuario)->withSession(['session_token' => $this->usuario->session_token])
            ->get(route('taller.mis-cursos'))
            ->assertOk()
            ->assertSessionHas('profile_id', Perfil::FACILITADOR);
    }

    public function test_mis_cursos_ofrece_asignados_e_inscritos_a_quien_facilita(): void
    {
        $this->soloConPerfiles($this->usuario, Perfil::FACILITADOR, Perfil::PARTICIPANTE);
        $sesion = ['session_token' => $this->usuario->session_token];

        $this->actingAs($this->usuario)->withSession($sesion)->get(route('taller.mis-cursos'))
            ->assertOk()->assertViewHas('esFacilitador', true)->assertSee('Inscritos');

        $this->actingAs($this->usuario)->withSession($sesion)->get(route('taller.mis-cursos', ['vista' => 'inscritos']))
            ->assertOk()->assertViewHas('esFacilitador', false);
    }

    // ── Anti-autopromoción con perfiles sumados ────────────────────────────

    public function test_no_se_otorga_a_un_perfil_propio_un_permiso_que_no_tiene(): void
    {
        $this->prepararCoordinadorQueGestionaPermisos();
        [$proceso, $slug] = self::POSTULAR;

        $this->comoCoordinador()
            ->post(route('profiles.permissions', ['id' => Encryptor::encrypt(Perfil::FACILITADOR)]),
                ['permissions' => $this->formularioCon(Perfil::FACILITADOR, ["{$proceso}:{$slug}"])])
            ->assertRedirect(route('profiles'))
            ->assertSessionHasErrors(['error-message' => __('You cannot grant yourself permissions you do not already have')]);

        $this->assertNotContains("{$proceso}:{$slug}", $this->clavesDelPerfil(Perfil::FACILITADOR));
    }

    public function test_a_un_perfil_propio_se_le_puede_dar_lo_que_ya_se_tiene(): void
    {
        $this->prepararCoordinadorQueGestionaPermisos();

        // supervise_courses lo tiene el Coordinador; dárselo al Facilitador no lo eleva.
        $clave = Autorizacion::PROCESO_CURSOS.':supervise_courses';
        $this->assertNotContains($clave, $this->clavesDelPerfil(Perfil::FACILITADOR));

        $this->comoCoordinador()
            ->post(route('profiles.permissions', ['id' => Encryptor::encrypt(Perfil::FACILITADOR)]),
                ['permissions' => $this->formularioCon(Perfil::FACILITADOR, [$clave])])
            ->assertSessionHasNoErrors();

        $this->assertContains($clave, $this->clavesDelPerfil(Perfil::FACILITADOR));
    }

    public function test_no_se_desactiva_un_perfil_propio(): void
    {
        $this->soloConPerfiles($this->coordinador, Perfil::COORDINADOR, Perfil::FACILITADOR);

        $this->comoCoordinador()
            ->get(route('profiles.disabled', ['id' => Encryptor::encrypt(Perfil::FACILITADOR)]))
            ->assertRedirect(route('profiles'))
            ->assertSessionHasErrors('error-message');

        $this->assertTrue((bool) DB::table('security.profiles')->where('id', Perfil::FACILITADOR)->value('active'));
    }

    // ── Utilidades ─────────────────────────────────────────────────────────

    private function puedePostular(): bool
    {
        [$proceso, $slug] = self::POSTULAR;

        return app(Autorizacion::class)->puede($proceso, SecurityAction::stringToId($slug), $this->usuario);
    }

    private function prepararCoordinadorQueGestionaPermisos(): void
    {
        $this->soloConPerfiles($this->coordinador, Perfil::COORDINADOR, Perfil::FACILITADOR);

        $permisoId = DB::table('security.permissions as p')
            ->join('security.processes as pr', 'pr.id', '=', 'p.process_id')
            ->where('pr.codigo', 'profiles')->where('p.slug', 'permissions')
            ->value('p.id');
        DB::table('security.profile_permissions')->insertOrIgnore([
            'profile_id' => Perfil::COORDINADOR, 'permission_id' => $permisoId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        app(Autorizacion::class)->invalidar();
    }

    private function comoCoordinador()
    {
        return $this->actingAs($this->coordinador)->withSession([
            'profile_id' => Perfil::COORDINADOR,
            'session_token' => $this->coordinador->session_token,
        ]);
    }

    private function clavesDelPerfil(int $perfilId): array
    {
        return DB::table('security.profile_permissions as pp')
            ->join('security.permissions as p', 'p.id', '=', 'pp.permission_id')
            ->join('security.processes as pr', 'pr.id', '=', 'p.process_id')
            ->where('pp.profile_id', $perfilId)
            ->get(['pr.codigo', 'p.slug'])
            ->map(fn ($f) => $f->codigo.':'.$f->slug)
            ->all();
    }

    /** `permissions[crypt_proceso][slug]` como lo envía la pantalla. */
    private function formularioCon(int $perfilId, array $agregar): array
    {
        $procesos = DB::table('security.processes')->pluck('id', 'codigo');
        $formulario = [];
        foreach (array_unique(array_merge($this->clavesDelPerfil($perfilId), $agregar)) as $clave) {
            [$ruta, $slug] = explode(':', $clave);
            $formulario[Encryptor::encrypt($procesos[$ruta])][$slug] = 'on';
        }

        return $formulario;
    }
}
