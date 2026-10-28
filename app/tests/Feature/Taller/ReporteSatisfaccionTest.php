<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use App\Enums\EstadoCurso;
use App\Services\Security\Autorizacion;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Entities\ResenaSatisfaccion;
use Modules\Taller\Exports\ReporteSatisfaccionExport;
use Tests\TestCase;

/**
 * D2 (RF-59, RF-60): los resultados de satisfacción los consultan el
 * coordinador y el facilitador asignado; el reporte solo lo genera quien tiene
 * el permiso `export_satisfaction` (coordinador).
 */
class ReporteSatisfaccionTest extends TestCase
{
    use DatabaseTransactions;

    private User $coordinador;

    private User $facilitador;

    private User $otroFacilitador;

    private User $participante;

    private Curso $curso;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coordinador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::COORDINADOR);
        })->where('id', '>', 1)->first() ?? User::find(2);
        // Los permisos suman perfiles: cada actor queda solo con el suyo.
        $this->soloConPerfiles($this->coordinador, Perfil::COORDINADOR);

        $facilitadores = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::FACILITADOR);
        })->whereDoesntHave('perfiles', fn ($q) => $q->whereIn('security.profiles.id', [Perfil::ADMINISTRADOR, Perfil::COORDINADOR]))
            ->where('id', '>', 1)->whereNotNull('session_token')->get();

        $this->facilitador = $facilitadores->first() ?? User::findOrFail(5);
        $this->otroFacilitador = $facilitadores->skip(1)->first() ?? User::findOrFail(6);

        $this->participante = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::PARTICIPANTE);
        })->whereDoesntHave('perfiles', fn ($q) => $q->where('security.profiles.id', '!=', Perfil::PARTICIPANTE))
            ->where('id', '>', 1)->first() ?? User::findOrFail(7);

        $this->curso = Curso::withoutEvents(function () {
            $c = new Curso;
            $c->nombre = 'Taller de Guion';
            $c->codigo = 'TDG-'.random_int(1000, 9999);
            $c->id_persona = $this->facilitador->personalData->id_persona;
            $c->id_actividad_formativa = 1;
            $c->id_modalidad = 1;
            $c->es_nacional = true;
            $c->save();

            return $c;
        });

        DB::table('taller.curso_estado')->insert([
            'id_curso' => $this->curso->id_curso,
            'id_estado' => EstadoCurso::FINALIZADO->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function responderEncuesta(): void
    {
        $inscripcion = Inscripcion::create([
            'id_curso' => $this->curso->id_curso,
            'id_persona' => $this->participante->personalData->id_persona,
            'user_id' => $this->participante->id,
            'estado' => Inscripcion::ESTADO_APROBADO,
            'fecha_inscripcion' => now(),
        ]);

        ResenaSatisfaccion::create([
            'id_curso' => $this->curso->id_curso,
            'id_inscripcion' => $inscripcion->id_inscripcion,
            'id_persona' => $inscripcion->id_persona,
            'contenido' => 5,
            'pedagogico' => 4,
            'material' => 3,
            'organizacion' => 2,
        ]);
    }

    private function como(User $user, int $perfil): self
    {
        return $this->actingAs($user)->withSession([
            'profile_id' => $perfil,
            'session_token' => $user->session_token,
        ]);
    }

    private function resultados()
    {
        return route('taller.cursos.satisfaccion.resultados', $this->curso->id_curso);
    }

    private function reporte()
    {
        return route('taller.cursos.satisfaccion.reporte', $this->curso->id_curso);
    }

    public function test_el_coordinador_descarga_el_reporte_con_los_mismos_datos_de_la_pantalla(): void
    {
        Excel::fake();
        $this->responderEncuesta();

        $this->como($this->coordinador, Perfil::COORDINADOR)->get($this->reporte())->assertOk();

        Excel::assertDownloaded(
            'satisfaccion-'.strtolower($this->curso->codigo).'.xlsx',
            function (ReporteSatisfaccionExport $export) {
                $filas = $export->array();

                return $filas[1] === ['Curso', 'Taller de Guion']
                    && $filas[4] === ['Total de respuestas', 1]
                    && $filas[5] === ['Índice general (1 a 5)', 3.5]
                    && $filas[8][1] === 5.0
                    && $filas[11][1] === 2.0;
            }
        );
    }

    public function test_el_coordinador_ve_el_boton_de_reporte(): void
    {
        $this->responderEncuesta();

        $this->como($this->coordinador, Perfil::COORDINADOR)->get($this->resultados())
            ->assertOk()
            ->assertSee('Descargar reporte (Excel)');
    }

    public function test_el_facilitador_asignado_consulta_pero_no_genera_el_reporte(): void
    {
        $this->responderEncuesta();

        $this->como($this->facilitador, Perfil::FACILITADOR)->get($this->resultados())
            ->assertOk()
            ->assertSee('Resultados de la encuesta de satisfacción')
            ->assertDontSee('Descargar reporte (Excel)');

        $this->como($this->facilitador, Perfil::FACILITADOR)->get($this->reporte())->assertForbidden();
    }

    public function test_un_facilitador_de_otro_curso_no_consulta_los_resultados(): void
    {
        $this->responderEncuesta();

        $this->como($this->otroFacilitador, Perfil::FACILITADOR)->get($this->resultados())->assertForbidden();
        $this->como($this->otroFacilitador, Perfil::FACILITADOR)->get($this->reporte())->assertForbidden();
    }

    public function test_sin_el_permiso_el_coordinador_no_genera_el_reporte(): void
    {
        $this->responderEncuesta();

        DB::table('security.profile_permissions')
            ->where('profile_id', Perfil::COORDINADOR)
            ->whereIn('permission_id', DB::table('security.permissions')->where('slug', 'export_satisfaction')->pluck('id'))
            ->delete();
        app(Autorizacion::class)->invalidar();

        $this->como($this->coordinador, Perfil::COORDINADOR)->get($this->resultados())
            ->assertOk()
            ->assertDontSee('Descargar reporte (Excel)');
        $this->como($this->coordinador, Perfil::COORDINADOR)->get($this->reporte())->assertForbidden();
    }

    public function test_sin_respuestas_no_se_genera_el_reporte(): void
    {
        // Una redirección no es una descarga: no hace falta afirmar la ausencia del archivo.
        $this->como($this->coordinador, Perfil::COORDINADOR)->get($this->reporte())
            // crypt_id cambia en cada cifrado: se compara solo el destino.
            ->assertRedirectContains('/satisfaccion/resultados')
            ->assertSessionHas('info', 'Aún no hay respuestas para generar el reporte.');
    }
}
