<?php

namespace Tests\Feature\Taller;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Services\BorradorWizardService;
use Tests\TestCase;

/**
 * Cobertura de la Tanda 4 — Wizard de creación de curso:
 * - UX-01: Resiliencia a pérdida de sesión (rehidratación desde BD).
 * - UX-02: Guardar borrador y salir funcional con persistencia parcial.
 * - UX-06: Identificación del bloque en errores de horario de temario.
 * - Reinicio limpio de borrador (wizard.nuevo).
 */
class WizardBorradorTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private BorradorWizardService $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::find(1);
        if (empty($this->admin->session_token)) {
            $this->admin->session_token = \Illuminate\Support\Str::random(40);
            $this->admin->save();
        }
        $this->servicio = app(BorradorWizardService::class);
    }

    private function actingAsAdmin(array $extraSession = [])
    {
        return $this->actingAs($this->admin)->withSession(array_merge([
            'profile_id' => 1,
            'session_token' => $this->admin->session_token,
        ], $extraSession));
    }

    public function test_recupera_borrador_en_bd_si_la_sesion_ha_expirado_ux_01(): void
    {
        // Guardar paso 1 normalmente
        $payloadPaso1 = [
            'nombre' => 'Curso Resiliencia BD',
            'id_actividad_formativa' => 1,
            'id_aspecto' => 1,
            'id_modalidad' => 1,
            'id_modalidad_especial' => 1,
            'nivel' => 'BASICO',
            'descripcion' => 'Descripción de prueba para resiliencia.',
        ];

        $respPaso1 = $this->actingAsAdmin()->post(route('taller.cursos.wizard.paso1.store'), $payloadPaso1);
        $respPaso1->assertRedirect(route('taller.cursos.wizard.paso2.create'));

        // Simular que la sesión del wizard se borró / expiró
        session()->forget('wizard_curso');

        // Al acceder a paso 2 sin la sesión previa, debe rehidratar desde BD y responder 200
        $respPaso2 = $this->actingAsAdmin()->get(route('taller.cursos.wizard.paso2.create'));
        $respPaso2->assertOk();
        $this->assertEquals('Curso Resiliencia BD', $respPaso2->viewData('borrador')->payload['paso1']['nombre'] ?? null);
        $this->assertNotEmpty(session('wizard_curso.token'));
    }

    public function test_guardar_borrador_y_salir_en_paso1_persiste_datos_parciales_ux_02(): void
    {
        // Enviar solo el nombre y la acción de salir (faltando campos obligatorios estándar)
        $resp = $this->actingAsAdmin()->post(route('taller.cursos.wizard.paso1.store'), [
            'nombre' => 'Borrador Incompleto Paso 1',
            'accion' => 'salir',
        ]);

        $resp->assertRedirect(route('taller.cursos.index'));
        $resp->assertSessionHas('success');

        $borrador = $this->servicio->actual($this->admin->id);
        $this->assertNotNull($borrador);
        $this->assertEquals('Borrador Incompleto Paso 1', $borrador->payload['paso1']['nombre'] ?? null);
    }

    public function test_guardar_borrador_y_salir_en_paso2_persiste_datos_parciales_ux_02(): void
    {
        // Inicializar paso 1 en borrador
        $borrador = $this->servicio->actual($this->admin->id);
        $this->servicio->guardarSlice($this->admin->id, 'paso1', [
            'nombre' => 'Curso Paso 2 Parcial',
            'id_actividad_formativa' => 1,
            'id_aspecto' => 1,
            'id_modalidad' => 1,
            'id_modalidad_especial' => 1,
        ], 2);

        // Guardar parcialmente paso 2 (solo fecha_inicio y accion = salir)
        $resp = $this->actingAsAdmin()->post(route('taller.cursos.wizard.paso2.store'), [
            'fecha_inicio' => '2026-05-10',
            'accion' => 'salir',
        ]);

        $resp->assertRedirect(route('taller.cursos.index'));
        $resp->assertSessionHas('success');

        $borradorActual = $this->servicio->actual($this->admin->id);
        $this->assertEquals('2026-05-10', $borradorActual->payload['paso2']['fecha_inicio'] ?? null);
    }

    public function test_guardar_borrador_y_salir_en_paso3_omite_validacion_de_rango_ux_02(): void
    {
        $borrador = $this->servicio->actual($this->admin->id);
        $this->servicio->guardarSlice($this->admin->id, 'paso1', ['nombre' => 'Curso Paso 3'], 2);
        $this->servicio->guardarSlice($this->admin->id, 'paso2', [
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-06-30',
        ], 3);

        $resp = $this->actingAsAdmin()->post(route('taller.cursos.wizard.paso3.store'), [
            'accion' => 'salir',
            'contenidos' => [
                [
                    'titulo' => 'Bloque fuera de rango',
                    'fecha_contenido' => '2026-07-15', // Fuera de rango de fechas pero debe permitir guardar y salir
                    'hora_inicio' => '09:00',
                    'hora_fin' => '12:00',
                ],
            ],
        ]);

        $resp->assertRedirect(route('taller.cursos.index'));
        $resp->assertSessionHas('success');
    }

    public function test_validacion_de_rango_de_horas_identifica_bloque_con_error_ux_06(): void
    {
        $borrador = $this->servicio->actual($this->admin->id);
        $this->servicio->guardarSlice($this->admin->id, 'paso1', ['nombre' => 'Curso Paso 3'], 2);
        $this->servicio->guardarSlice($this->admin->id, 'paso2', [
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-06-30',
        ], 3);

        // Envío regular (sin accion=salir) con hora fin anterior a hora inicio
        $resp = $this->actingAsAdmin()->post(route('taller.cursos.wizard.paso3.store'), [
            'contenidos' => [
                [
                    'titulo' => 'Introducción',
                    'fecha_contenido' => '2026-06-05',
                    'hora_inicio' => '08:00',
                    'hora_fin' => '10:00',
                ],
                [
                    'titulo' => 'Taller de Guión Cinematográfico',
                    'fecha_contenido' => '2026-06-10',
                    'hora_inicio' => '16:00',
                    'hora_fin' => '14:00', // Inválido: fin antes de inicio
                ],
            ],
        ]);

        $resp->assertSessionHasErrors();
        $errores = session('errors')->all();
        $encontrado = false;
        foreach ($errores as $err) {
            if (str_contains($err, 'Taller de Guión Cinematográfico')) {
                $encontrado = true;
                break;
            }
        }
        $this->assertTrue($encontrado, 'El mensaje de error de validación debe mencionar el título del bloque afectado.');
    }

    public function test_reiniciar_borrador_limpia_estado_previo(): void
    {
        // Crear datos previos
        $this->servicio->guardarSlice($this->admin->id, 'paso1', ['nombre' => 'Curso Para Borrar'], 2);
        $this->assertNotEmpty($this->servicio->actual($this->admin->id)->payload);

        // Solicitar reinicio
        $resp = $this->actingAsAdmin()->get(route('taller.cursos.wizard.nuevo'));
        $resp->assertRedirect(route('taller.cursos.wizard.paso1.create'));
        $resp->assertSessionHas('info');

        // El nuevo borrador debe tener payload vacío
        $borradorNuevo = $this->servicio->actual($this->admin->id);
        $this->assertEmpty($borradorNuevo->payload);
    }

    public function test_ciclo_de_vida_foto_en_borrador_persistencia_recarga_y_reemplazo(): void
    {
        // 1. Guardar paso 1 con foto subida
        $foto = UploadedFile::fake()->image('portada.jpg', 600, 400);
        $respPaso1 = $this->actingAsAdmin()->post(route('taller.cursos.wizard.paso1.store'), [
            'nombre' => 'Curso Con Portada',
            'id_actividad_formativa' => 1,
            'id_aspecto' => 1,
            'id_modalidad' => 1,
            'id_modalidad_especial' => 1,
            'foto' => $foto,
        ]);
        $respPaso1->assertRedirect(route('taller.cursos.wizard.paso2.create'));

        $borrador = $this->servicio->actual($this->admin->id);
        $this->assertNotNull($borrador->foto_path);
        $this->assertStringContainsString('foto.jpg', $borrador->foto_path);

        // 2. Al reingresar al paso 1, la vista debe recibir la URL controlada sin extensión Nginx
        $respVer = $this->actingAsAdmin()->get(route('taller.cursos.wizard.paso1.create'));
        $respVer->assertOk();
        $fotoUrl = $respVer->viewData('fotoUrl');
        $this->assertNotNull($fotoUrl);
        $this->assertStringContainsString('/taller/crear-curso/foto/'.$borrador->token, $fotoUrl);

        // 3. Probar la ruta que sirve la imagen directamente
        $respFoto = $this->actingAsAdmin()->get(route('taller.cursos.wizard.foto', ['token' => $borrador->token]));
        $respFoto->assertOk();
        $this->assertStringContainsString('image/jpeg', $respFoto->headers->get('Content-Type'));

        // 4. Guardar paso 1 de nuevo SIN enviar nueva foto (debe conservar la foto existente)
        $respPaso1Re = $this->actingAsAdmin()->post(route('taller.cursos.wizard.paso1.store'), [
            'nombre' => 'Curso Con Portada Actualizado',
            'id_actividad_formativa' => 1,
            'id_aspecto' => 1,
            'id_modalidad' => 1,
            'id_modalidad_especial' => 1,
        ]);
        $respPaso1Re->assertRedirect(route('taller.cursos.wizard.paso2.create'));
        $borradorRe = $this->servicio->actual($this->admin->id);
        $this->assertEquals($borrador->foto_path, $borradorRe->foto_path);

        // 5. Reemplazar la foto por una PNG
        $nuevaFoto = UploadedFile::fake()->image('nueva_portada.png', 400, 400);
        $respReemplazo = $this->actingAsAdmin()->post(route('taller.cursos.wizard.paso1.store'), [
            'nombre' => 'Curso Con Portada PNG',
            'id_actividad_formativa' => 1,
            'id_aspecto' => 1,
            'id_modalidad' => 1,
            'id_modalidad_especial' => 1,
            'foto' => $nuevaFoto,
        ]);
        $respReemplazo->assertRedirect(route('taller.cursos.wizard.paso2.create'));
        $borradorPng = $this->servicio->actual($this->admin->id);
        $this->assertStringContainsString('foto.png', $borradorPng->foto_path);

        // 6. Eliminar la foto con eliminar_foto = 1
        $respEliminar = $this->actingAsAdmin()->post(route('taller.cursos.wizard.paso1.store'), [
            'nombre' => 'Curso Sin Portada',
            'id_actividad_formativa' => 1,
            'id_aspecto' => 1,
            'id_modalidad' => 1,
            'id_modalidad_especial' => 1,
            'eliminar_foto' => 1,
        ]);
        $respEliminar->assertRedirect(route('taller.cursos.wizard.paso2.create'));
        $borradorSinFoto = $this->servicio->actual($this->admin->id);
        $this->assertNull($borradorSinFoto->foto_path);
    }

    public function test_paso2_rechaza_fecha_fin_igual_o_anterior_a_fecha_inicio(): void
    {
        // 1. Caso igual: fecha_fin == fecha_inicio
        $respIgual = $this->actingAsAdmin()->post(route('taller.cursos.wizard.paso2.store'), [
            'id_persona' => 1,
            'fecha_inicio' => '2026-06-15',
            'fecha_fin' => '2026-06-15',
            'correlativo' => 1,
            'cantidad_cupos' => 20,
            'direccion_corta' => 'Sede Principal',
            'es_nacional' => 1,
        ]);
        $respIgual->assertSessionHasErrors(['fecha_fin']);

        // 2. Caso anterior: fecha_fin < fecha_inicio
        $respAnterior = $this->actingAsAdmin()->post(route('taller.cursos.wizard.paso2.store'), [
            'id_persona' => 1,
            'fecha_inicio' => '2026-06-15',
            'fecha_fin' => '2026-06-10',
            'correlativo' => 1,
            'cantidad_cupos' => 20,
            'direccion_corta' => 'Sede Principal',
            'es_nacional' => 1,
        ]);
        $respAnterior->assertSessionHasErrors(['fecha_fin']);

        // 3. Caso válido: fecha_fin > fecha_inicio
        $respValido = $this->actingAsAdmin()->post(route('taller.cursos.wizard.paso2.store'), [
            'id_persona' => 1,
            'fecha_inicio' => '2026-06-15',
            'fecha_fin' => '2026-06-16',
            'correlativo' => 1,
            'cantidad_cupos' => 20,
            'direccion_corta' => 'Sede Principal',
            'es_nacional' => 1,
        ]);
        $respValido->assertSessionDoesntHaveErrors(['fecha_fin']);
        $respValido->assertRedirect(route('taller.cursos.wizard.paso3.create'));
    }

    public function test_paso3_rechaza_bloques_con_fecha_fuera_del_rango_del_curso(): void
    {
        $this->servicio->guardarSlice($this->admin->id, 'paso1', ['nombre' => 'Curso Rango Test'], 2);
        $this->servicio->guardarSlice($this->admin->id, 'paso2', [
            'fecha_inicio' => '2026-08-01',
            'fecha_fin' => '2026-08-31',
        ], 3);

        // Envío con fecha anterior al inicio
        $respAntes = $this->actingAsAdmin()->post(route('taller.cursos.wizard.paso3.store'), [
            'contenidos' => [
                [
                    'titulo' => 'Clase Previa',
                    'fecha_contenido' => '2026-07-31',
                    'hora_inicio' => '09:00',
                    'hora_fin' => '11:00',
                ],
            ],
        ]);
        $respAntes->assertSessionHasErrors(['contenidos.0.fecha_contenido']);

        // Envío con fecha posterior al fin
        $respDespues = $this->actingAsAdmin()->post(route('taller.cursos.wizard.paso3.store'), [
            'contenidos' => [
                [
                    'titulo' => 'Clase Tardía',
                    'fecha_contenido' => '2026-09-01',
                    'hora_inicio' => '09:00',
                    'hora_fin' => '11:00',
                ],
            ],
        ]);
        $respDespues->assertSessionHasErrors(['contenidos.0.fecha_contenido']);

        // Envío sin fecha de clase
        $respVacia = $this->actingAsAdmin()->post(route('taller.cursos.wizard.paso3.store'), [
            'contenidos' => [
                [
                    'titulo' => 'Clase Sin Fecha',
                    'fecha_contenido' => '',
                    'hora_inicio' => '09:00',
                    'hora_fin' => '11:00',
                ],
            ],
        ]);
        $respVacia->assertSessionHasErrors(['contenidos.0.fecha_contenido']);

        // Envío con fecha válida dentro del rango
        $respOk = $this->actingAsAdmin()->post(route('taller.cursos.wizard.paso3.store'), [
            'contenidos' => [
                [
                    'titulo' => 'Clase Válida 1',
                    'fecha_contenido' => '2026-08-01', // Límite inferior
                    'hora_inicio' => '09:00',
                    'hora_fin' => '11:00',
                ],
                [
                    'titulo' => 'Clase Válida 2',
                    'fecha_contenido' => '2026-08-31', // Límite superior
                    'hora_inicio' => '14:00',
                    'hora_fin' => '16:00',
                ],
            ],
        ]);
        $respOk->assertSessionDoesntHaveErrors();
        $respOk->assertRedirect(route('taller.cursos.wizard.plantilla.create'));
    }
}
