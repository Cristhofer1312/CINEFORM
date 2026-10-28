<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use App\Enums\EstadoCurso;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\CursoRequisito;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Entities\InscripcionRespuesta;
use Tests\TestCase;

/**
 * "Ver Documento" carga el archivo en un iframe: debe servirse en línea
 * (vista previa) y solo "Descargar" debe forzar la descarga.
 */
class VerDocumentoRequisitoTest extends TestCase
{
    use DatabaseTransactions;

    private User $facilitador;

    private InscripcionRespuesta $respuesta;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->facilitador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::FACILITADOR);
        })->where('id', '>', 1)->first() ?? User::findOrFail(5);

        $participante = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::PARTICIPANTE);
        })->where('id', '>', 1)->first() ?? User::findOrFail(7);

        $curso = Curso::withoutEvents(function () {
            $c = new Curso;
            $c->nombre = 'Taller de Sonido Directo';
            $c->codigo = 'TSD-'.random_int(1000, 9999);
            $c->id_persona = $this->facilitador->personalData->id_persona;
            $c->id_actividad_formativa = 1;
            $c->id_aspecto = 1;
            $c->id_modalidad = 1;
            $c->id_modalidad_especial = 1;
            $c->cantidad_cupos = 25;
            $c->fecha_inicio = now();
            $c->fecha_fin = now()->addMonth();
            $c->save();

            return $c;
        });

        DB::table('taller.curso_estado')->insert([
            'id_curso' => $curso->id_curso,
            'id_estado' => EstadoCurso::INSCRIPCION->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $requisito = CursoRequisito::create([
            'id_curso' => $curso->id_curso,
            'tipo' => 'documento',
            'titulo' => 'Cédula de identidad',
            'obligatorio' => true,
        ]);

        $inscripcion = Inscripcion::create([
            'id_curso' => $curso->id_curso,
            'id_persona' => $participante->personalData->id_persona,
            'user_id' => $participante->id,
            'estado' => Inscripcion::ESTADO_POSTULADO,
            'fecha_inscripcion' => now(),
        ]);

        $ruta = 'Taller/'.$curso->codigo.'/cedula.pdf';
        Storage::disk('public')->put($ruta, "%PDF-1.4\n%%EOF\n");

        $this->respuesta = InscripcionRespuesta::create([
            'id_inscripcion' => $inscripcion->id_inscripcion,
            'id_requisito' => $requisito->id_requisito,
            'ruta_archivo' => $ruta,
        ]);
    }

    private function pedirComoFacilitador(string $ruta)
    {
        return $this->actingAs($this->facilitador)->withSession([
            'profile_id' => Perfil::FACILITADOR,
            'session_token' => $this->facilitador->session_token,
        ])->get(route($ruta, $this->respuesta->id_respuesta));
    }

    public function test_ver_documento_se_sirve_en_linea_para_la_vista_previa(): void
    {
        $respuesta = $this->pedirComoFacilitador('taller.inscripciones.respuestas.ver');

        $respuesta->assertOk();
        $this->assertStringStartsWith('inline', $respuesta->headers->get('Content-Disposition'));
        $this->assertStringContainsString('application/pdf', $respuesta->headers->get('Content-Type'));
    }

    public function test_descargar_documento_sigue_forzando_la_descarga(): void
    {
        $respuesta = $this->pedirComoFacilitador('taller.inscripciones.respuestas.descargar');

        $respuesta->assertOk();
        $this->assertStringStartsWith('attachment', $respuesta->headers->get('Content-Disposition'));
    }
}
