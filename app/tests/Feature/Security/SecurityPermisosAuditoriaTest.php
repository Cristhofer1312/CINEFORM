<?php

namespace Tests\Feature\Security;

use App\Constants\Perfil;
use App\Helpers\Encryptor;
use App\Services\Security\Autorizacion;
use FPDF;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Security\Entities\User;
use Tests\TestCase;

/**
 * Pruebas de autorización y endurecimiento del módulo Security:
 * - file_admin / show_pdf: permiso administrativo y prevención de path traversal.
 * - UsersController::password: con el permiso de la ruta basta (sin comparar perfiles del objetivo).
 * - ProfilesController::permissions: impedir otorgar permisos no poseídos a cualquier perfil.
 * - SecurityController::show_avatar: aceptar solo ID cifrado.
 */
class SecurityPermisosAuditoriaTest extends TestCase
{
    use DatabaseTransactions;

    private User $raiz;

    private User $adminNoRaiz;

    private User $coordinador;

    private User $participante;

    protected function setUp(): void
    {
        parent::setUp();

        $this->raiz = User::findOrFail(1);
        $this->adminNoRaiz = User::whereHas('perfiles', fn ($q) => $q->where('security.profiles.id', Perfil::ADMINISTRADOR))
            ->where('id', '>', 1)
            ->firstOrFail();
        $this->coordinador = User::findOrFail(2);
        $this->participante = User::findOrFail(7);
    }

    // ── 1. file_admin y show_pdf ────────────────────────────────────────────

    public function test_file_admin_responde_403_a_usuarios_sin_permiso(): void
    {
        $this->soloConPerfiles($this->participante, Perfil::PARTICIPANTE);
        $this->soloConPerfiles($this->coordinador, Perfil::COORDINADOR);

        $this->como($this->participante)
            ->get(route('security.admin', ['id' => 'cualquier_archivo']))
            ->assertForbidden();

        $this->como($this->coordinador)
            ->get(route('security.admin', ['id' => 'cualquier_archivo']))
            ->assertForbidden();
    }

    public function test_file_admin_funciona_para_administrador_y_bloquea_symlink_externo(): void
    {
        $nombre = 'qa_file_admin_'.Str::lower(Str::random(8));
        $pdfPath = storage_path($nombre.'.pdf');
        $enlace = 'qa_symlink_'.Str::lower(Str::random(8));
        $enlacePath = storage_path($enlace.'.pdf');
        $externo = tempnam(sys_get_temp_dir(), 'cineform_ext_');

        $this->crearPdfValido($pdfPath);
        file_put_contents($externo, '%PDF-1.4 externo');
        symlink($externo, $enlacePath);

        try {
            $this->como($this->adminNoRaiz)
                ->get(route('security.admin', ['id' => $nombre]))
                ->assertOk();

            $this->como($this->raiz)
                ->get(route('security.admin', ['id' => $nombre]))
                ->assertOk();

            // Un symlink que apunte fuera de storage/ debe ser rechazado por realpath.
            $this->como($this->adminNoRaiz)
                ->get(route('security.admin', ['id' => $enlace]))
                ->assertNotFound();
        } finally {
            @unlink($pdfPath);
            @unlink($enlacePath);
            @unlink($externo);
        }
    }

    public function test_show_pdf_responde_403_sin_permiso_y_permite_a_administrador(): void
    {
        $this->soloConPerfiles($this->participante, Perfil::PARTICIPANTE);
        $this->soloConPerfiles($this->coordinador, Perfil::COORDINADOR);

        $nombre = 'qa_show_pdf_'.Str::lower(Str::random(8));
        $pdfPath = storage_path($nombre.'.pdf');
        $enlace = 'qa_show_symlink_'.Str::lower(Str::random(8));
        $enlacePath = storage_path($enlace.'.pdf');
        $externo = tempnam(sys_get_temp_dir(), 'cineform_ext_');

        $this->crearPdfValido($pdfPath);
        file_put_contents($externo, '%PDF-1.4 externo');
        symlink($externo, $enlacePath);

        try {
            $this->como($this->participante)
                ->get(route('show_pdf', ['pdf' => $nombre, 'page' => 0]))
                ->assertForbidden();

            $this->como($this->coordinador)
                ->get(route('show_pdf', ['pdf' => $nombre, 'page' => 0]))
                ->assertForbidden();

            // Página fuera de rango verifica autorización y lectura del PDF sin depender de policy.xml de Imagick.
            $this->como($this->adminNoRaiz)
                ->get(route('show_pdf', ['pdf' => $nombre, 'page' => 99]))
                ->assertStatus(400)
                ->assertJson(['error' => 'page not found']);

            $this->como($this->adminNoRaiz)
                ->get(route('show_pdf', ['pdf' => $enlace, 'page' => 0]))
                ->assertNotFound();
        } finally {
            @unlink($pdfPath);
            @unlink($enlacePath);
            @unlink($externo);
        }
    }

    // ── 2. UsersController::password ───────────────────────────────────────

    public function test_users_password_coordinador_con_permiso_cambia_contrasena_sin_importar_perfiles_del_objetivo(): void
    {
        // Decisión del dueño (24/11/2026): basta el permiso de la ruta; no se compara la jerarquía de perfiles.
        $this->soloConPerfiles($this->coordinador, Perfil::COORDINADOR);
        $this->soloConPerfiles($this->adminNoRaiz, Perfil::ADMINISTRADOR);
        $this->otorgarPermiso(Perfil::COORDINADOR, 'users', 'view');
        $this->otorgarPermiso(Perfil::COORDINADOR, 'users', 'security');

        $idObjetivo = Encryptor::encrypt($this->adminNoRaiz->id);

        $this->como($this->coordinador)
            ->get(route('users.password', ['id' => $idObjetivo]))
            ->assertOk();

        $this->como($this->coordinador)
            ->post(route('users.password', ['id' => $idObjetivo]), [
                'new_password' => 'Clave-Coordinador-2026!',
                're_password' => 'Clave-Coordinador-2026!',
            ])
            ->assertRedirect(route('users'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('Clave-Coordinador-2026!', User::findOrFail($this->adminNoRaiz->id)->password));
    }

    public function test_users_password_permite_subconjunto_de_perfiles_y_usuario_raiz(): void
    {
        $this->soloConPerfiles($this->coordinador, Perfil::COORDINADOR, Perfil::PARTICIPANTE);
        $this->soloConPerfiles($this->participante, Perfil::PARTICIPANTE);
        $this->soloConPerfiles($this->adminNoRaiz, Perfil::ADMINISTRADOR);
        $this->otorgarPermiso(Perfil::COORDINADOR, 'users', 'view');
        $this->otorgarPermiso(Perfil::COORDINADOR, 'users', 'security');

        $idParticipante = Encryptor::encrypt($this->participante->id);

        $this->como($this->coordinador)
            ->get(route('users.password', ['id' => $idParticipante]))
            ->assertOk();

        $this->como($this->coordinador)
            ->post(route('users.password', ['id' => $idParticipante]), [
                'new_password' => 'Clave-Permitida-2026!',
                're_password' => 'Clave-Permitida-2026!',
            ])
            ->assertRedirect(route('users'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('Clave-Permitida-2026!', User::findOrFail($this->participante->id)->password));

        // El usuario raíz puede cambiar contraseñas de cualquier cuenta (salvo sí mismo).
        $idAdmin = Encryptor::encrypt($this->adminNoRaiz->id);
        $this->como($this->raiz)
            ->post(route('users.password', ['id' => $idAdmin]), [
                'new_password' => 'Clave-Raiz-Ok-2026!',
                're_password' => 'Clave-Raiz-Ok-2026!',
            ])
            ->assertRedirect(route('users'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('Clave-Raiz-Ok-2026!', User::findOrFail($this->adminNoRaiz->id)->password));
    }

    // ── 3. ProfilesController::permissions ─────────────────────────────────

    public function test_profiles_permissions_responde_403_al_otorgar_permiso_ajeno_a_otro_perfil(): void
    {
        // Actor aislado a solo Coordinador (no tiene Facilitador ni apply_facilitator).
        $this->soloConPerfiles($this->coordinador, Perfil::COORDINADOR);
        $this->otorgarPermiso(Perfil::COORDINADOR, 'profiles', 'permissions');

        $clave = 'taller.postulacion-facilitador.landing:apply_facilitator';
        $this->assertNotContains($clave, $this->clavesDelPerfil(Perfil::COORDINADOR));
        $this->assertNotContains($clave, $this->clavesDelPerfil(Perfil::FACILITADOR));

        $this->como($this->coordinador)
            ->post(route('profiles.permissions', ['id' => Encryptor::encrypt(Perfil::FACILITADOR)]), [
                'permissions' => $this->formularioCon(Perfil::FACILITADOR, [$clave]),
            ])
            ->assertForbidden();

        $this->assertNotContains($clave, $this->clavesDelPerfil(Perfil::FACILITADOR));
    }

    public function test_profiles_permissions_permite_otorgar_permiso_propio_y_al_raiz(): void
    {
        $this->soloConPerfiles($this->coordinador, Perfil::COORDINADOR);
        $this->otorgarPermiso(Perfil::COORDINADOR, 'profiles', 'permissions');

        // Permiso que el Coordinador sí posee: puede otorgarlo al perfil Facilitador.
        $clavePropia = Autorizacion::PROCESO_CURSOS.':supervise_courses';
        $this->assertContains($clavePropia, $this->clavesDelPerfil(Perfil::COORDINADOR));
        $this->assertNotContains($clavePropia, $this->clavesDelPerfil(Perfil::FACILITADOR));

        $this->como($this->coordinador)
            ->post(route('profiles.permissions', ['id' => Encryptor::encrypt(Perfil::FACILITADOR)]), [
                'permissions' => $this->formularioCon(Perfil::FACILITADOR, [$clavePropia]),
            ])
            ->assertRedirect(route('profiles'))
            ->assertSessionHasNoErrors();

        $this->assertContains($clavePropia, $this->clavesDelPerfil(Perfil::FACILITADOR));

        // El usuario raíz puede otorgar cualquier permiso a otro perfil.
        $claveAjena = 'taller.postulacion-facilitador.landing:apply_facilitator';
        $this->como($this->raiz)
            ->post(route('profiles.permissions', ['id' => Encryptor::encrypt(Perfil::FACILITADOR)]), [
                'permissions' => $this->formularioCon(Perfil::FACILITADOR, [$claveAjena]),
            ])
            ->assertRedirect(route('profiles'))
            ->assertSessionHasNoErrors();

        $this->assertContains($claveAjena, $this->clavesDelPerfil(Perfil::FACILITADOR));
    }

    // ── 4. SecurityController::show_avatar ─────────────────────────────────

    public function test_show_avatar_rechaza_id_numerico_en_claro_y_acepta_id_cifrado(): void
    {
        $usuario = new User;
        $usuario->username = 'qa_avatar_'.random_int(1000, 9999);
        $usuario->email = $usuario->username.'@example.test';
        $usuario->password = Hash::make('Clave-Avatar-2026!');
        $usuario->active = true;
        $usuario->register_date = now();
        $usuario->ip = '127.0.0.1';
        $usuario->user_id = 1;
        $usuario->session_token = Str::random(40);
        $usuario->save();
        $this->soloConPerfiles($usuario, Perfil::PARTICIPANTE);

        $avatarDir = storage_path('app/public/img/avatars');
        if (! is_dir($avatarDir)) {
            mkdir($avatarDir, 0755, true);
        }
        $avatarPath = $avatarDir.DIRECTORY_SEPARATOR.$usuario->id.'.png';
        // PNG válido de 1x1 píxel.
        file_put_contents(
            $avatarPath,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', true)
        );

        try {
            // ID numérico en claro: rechazado.
            $this->como($usuario)
                ->get('/security/show-avatar/'.$usuario->id)
                ->assertNotFound();

            // Valor no válido: rechazado.
            $this->como($usuario)
                ->get('/security/show-avatar/valor_invalido')
                ->assertNotFound();

            // ID cifrado: permitido.
            $this->como($usuario)
                ->get(route('show_avatar', $usuario->crypt_id))
                ->assertOk()
                ->assertHeader('Content-Type', 'image/png');
        } finally {
            @unlink($avatarPath);
        }
    }

    // ── Utilidades ─────────────────────────────────────────────────────────

    private function como(User $user)
    {
        return $this->actingAs($user)->withSession([
            'session_token' => $user->fresh()->session_token,
        ]);
    }

    private function crearPdfValido(string $ruta): void
    {
        $pdf = new FPDF;
        $pdf->AddPage();
        $pdf->Output('F', $ruta);
    }

    private function otorgarPermiso(int $perfilId, string $codigo, string $slug): void
    {
        $permisoId = DB::table('security.permissions as p')
            ->join('security.processes as pr', 'pr.id', '=', 'p.process_id')
            ->where('pr.codigo', $codigo)
            ->where('p.slug', $slug)
            ->value('p.id');

        DB::table('security.profile_permissions')->insertOrIgnore([
            'profile_id' => $perfilId,
            'permission_id' => $permisoId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(Autorizacion::class)->invalidar();
    }

    /**
     * @return array<int, string>
     */
    private function clavesDelPerfil(int $perfilId): array
    {
        return DB::table('security.profile_permissions as pp')
            ->join('security.permissions as p', 'p.id', '=', 'pp.permission_id')
            ->join('security.processes as pr', 'pr.id', '=', 'p.process_id')
            ->where('pp.profile_id', $perfilId)
            ->orderBy('pr.codigo')
            ->orderBy('p.slug')
            ->get(['pr.codigo', 'p.slug'])
            ->map(fn ($f) => $f->codigo.':'.$f->slug)
            ->all();
    }

    /**
     * @param  array<int, string>  $agregar
     * @return array<string, array<string, string>>
     */
    private function formularioCon(int $perfilId, array $agregar = []): array
    {
        $claves = array_values(array_unique(array_merge($this->clavesDelPerfil($perfilId), $agregar)));
        $procesos = DB::table('security.processes')->pluck('id', 'codigo');

        $formulario = [];
        foreach ($claves as $clave) {
            [$codigo, $slug] = explode(':', $clave);
            $formulario[Encryptor::encrypt($procesos[$codigo])][$slug] = 'on';
        }

        return $formulario;
    }
}
