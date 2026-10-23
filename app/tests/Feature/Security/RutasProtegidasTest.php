<?php

namespace Tests\Feature\Security;

use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Red de seguridad RBAC (RF-05).
 *
 * Toda ruta mutante (POST/PUT/PATCH/DELETE) de Taller o Security que exija
 * autenticación debe estar protegida por el middleware `permiso:` o figurar
 * de forma explícita en PENDIENTES_DE_MIGRAR (a la espera de pasar a Policies).
 *
 * La lista solo debe ENCOGER: el test falla si una ruta ya protegida sigue en
 * la lista (obliga a limpiarla) y si aparece una ruta nueva sin protección.
 */
class RutasProtegidasTest extends TestCase
{
    /**
     * Rutas que aún dependen de comprobaciones dentro del controlador.
     * Fase 5 del plan: migrar a `$this->authorize()` y vaciar esta lista.
     * No añadir entradas nuevas: protege la ruta con `permiso:`.
     *
     * @var array<int, string>
     */
    private const PENDIENTES_DE_MIGRAR = [
        'security.admin',
        'taller.asistencia.anular',
        'taller.asistencia.desactivar-token',
        'taller.asistencia.generar-token',
        'taller.asistencia.marcar',
        'taller.asistencia.marcar-manual',
        'taller.asistencia.restaurar',
        'taller.calificaciones.store',
        'taller.certificacion.aprobar',
        'taller.certificacion.denegar',
        'taller.cursos.finalizarEdicion',
        'taller.cursos.plantilla.store',
        'taller.cursos.reasignarFacilitador',
        'taller.cursos.requisitos.store',
        'taller.cursos.requisitos.update',
        'taller.inscripciones.aprobar',
        'taller.inscripciones.denegar',
        'taller.inscripciones.destroy',
        'taller.inscripciones.procesar',
        'taller.inscripciones.rechazar',
        'taller.inscripciones.rehabilitar',
        'taller.inscripciones.store',
    ];

    /**
     * Rutas autenticadas que son legítimamente accesibles para cualquier usuario
     * con sesión; la autorización fina vive en el controlador y está justificada.
     *
     * @var array<int, string>
     */
    private const ABIERTAS_A_AUTENTICADOS = [
        'logout',
        'taller.asistencia.confirmar-marcar',
        'taller.certificados.prueba.wizard',
        'taller.cursos.satisfaccion.store',
        'taller.postulacion-facilitador.postular',
        'update_profile',
        'update_profile.send_code',
        'usuario.set_perfil',
    ];

    /**
     * Rutas sin `permiso:` cuya autorización vive en el controlador mediante
     * `$this->authorize()` y terminan en 403/404 reales (no en un redirect).
     *
     * @var array<int, string>
     */
    private const AUTORIZADAS_EN_CONTROLADOR = [
        'taller.cursos.update',
        'taller.cursos.updateStatus',
    ];

    public function test_toda_ruta_mutante_autenticada_esta_protegida_o_listada(): void
    {
        $sinProteger = $this->rutasMutantesSinPermiso();

        $permitidas = array_merge(
            self::PENDIENTES_DE_MIGRAR,
            self::ABIERTAS_A_AUTENTICADOS,
            self::AUTORIZADAS_EN_CONTROLADOR
        );
        $nuevas = array_values(array_diff($sinProteger, $permitidas));

        $this->assertSame(
            [],
            $nuevas,
            "Rutas mutantes autenticadas sin middleware `permiso:`:\n - " . implode("\n - ", $nuevas)
        );
    }

    public function test_la_lista_de_pendientes_no_contiene_rutas_ya_protegidas(): void
    {
        $sinProteger = $this->rutasMutantesSinPermiso();
        $obsoletas = array_values(array_diff(self::PENDIENTES_DE_MIGRAR, $sinProteger));

        $this->assertSame(
            [],
            $obsoletas,
            "Quitar de PENDIENTES_DE_MIGRAR (ya protegidas o inexistentes):\n - " . implode("\n - ", $obsoletas)
        );
    }

    public function test_las_rutas_de_administracion_de_facilitadores_exigen_permiso(): void
    {
        $nombres = [
            'taller.postulacion-facilitador.admin',
            'taller.postulacion-facilitador.admin.preview',
            'taller.postulacion-facilitador.requisitos.store',
            'taller.postulacion-facilitador.requisitos.update',
            'taller.postulacion-facilitador.requisitos.toggle',
            'taller.postulacion-facilitador.aprobar',
            'taller.postulacion-facilitador.rechazar',
        ];

        foreach ($nombres as $nombre) {
            $ruta = Route::getRoutes()->getByName($nombre);
            $this->assertNotNull($ruta, "No existe la ruta {$nombre}");
            $this->assertTrue($this->tienePermiso($ruta), "{$nombre} debe llevar middleware permiso:");
        }
    }

    public function test_la_ruta_de_asignar_perfiles_exige_permiso(): void
    {
        $ruta = Route::getRoutes()->getByName('users.assign_profiles');
        $this->assertNotNull($ruta, 'No existe la ruta users.assign_profiles');
        $this->assertTrue($this->tienePermiso($ruta), 'users.assign_profiles debe llevar middleware permiso:');
    }

    public function test_las_rutas_get_sensibles_exigen_autenticacion_y_estan_catalogadas(): void
    {
        $rutasGetSensibles = [
            'taller.cursos.participantes',
            'taller.inscripciones.respuestas',
            'taller.inscripciones.respuestas.descargar',
            'taller.inscripciones.respuestas.ver',
            'taller.cursos.satisfaccion.resultados',
            'taller.asistencia.consolidado',
            'taller.asistencia.individual',
            'taller.asistencia.tokens-activos',
            'taller.certificados.descargar',
        ];

        foreach ($rutasGetSensibles as $nombre) {
            $ruta = Route::getRoutes()->getByName($nombre);
            $this->assertNotNull($ruta, "No existe la ruta {$nombre}");
            $this->assertTrue(
                in_array('auth', $this->middlewares($ruta), true),
                "La ruta sensible {$nombre} debe exigir middleware auth."
            );
        }
    }

    /**
     * @return array<int, string> nombres de ruta (o URI si no tiene nombre), ordenados
     */
    private function rutasMutantesSinPermiso(): array
    {
        $resultado = [];

        foreach (Route::getRoutes()->getRoutes() as $ruta) {
            if (!$this->esDeInteres($ruta) || !$this->esMutante($ruta)) {
                continue;
            }

            if (!in_array('auth', $this->middlewares($ruta), true)) {
                continue;
            }

            if ($this->tienePermiso($ruta)) {
                continue;
            }

            $resultado[] = $ruta->getName() ?: $ruta->uri();
        }

        $resultado = array_values(array_unique($resultado));
        sort($resultado);

        return $resultado;
    }

    private function esDeInteres(LaravelRoute $ruta): bool
    {
        $uri = $ruta->uri();

        return str_starts_with($uri, 'taller/')
            || str_starts_with($uri, 'security/')
            || str_starts_with($uri, 'usuarios/')
            || str_starts_with($uri, 'registro/');
    }

    private function esMutante(LaravelRoute $ruta): bool
    {
        return count(array_intersect($ruta->methods(), ['POST', 'PUT', 'PATCH', 'DELETE'])) > 0;
    }

    /** @return array<int, string> */
    private function middlewares(LaravelRoute $ruta): array
    {
        return array_map('strval', $ruta->gatherMiddleware());
    }

    private function tienePermiso(LaravelRoute $ruta): bool
    {
        foreach ($this->middlewares($ruta) as $middleware) {
            if (str_starts_with($middleware, 'permiso:')) {
                return true;
            }
        }

        return false;
    }
}
