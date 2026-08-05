<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Catálogos base (sin dependencias)
            GendersSeeder::class,
            DocumentTypesSeeder::class,
            ModulesMenusSeeder::class,
            ProcessesSeeder::class,
            ProfilesSeeder::class,
            CountriesSeeder::class,
            NivelesEducacionSeeder::class,
            EspecializacionesSeeder::class,
            ModalidadSeeder::class,
            TipoEvaluacionesSeeder::class,
            EstadosCursoSeeder::class,
            ActividadesFormativasSeeder::class,
            ModalidadesEspecialesSeeder::class,
            AspectosSeeder::class,

            // Geografía (catálogos base sin dependencias)
            EstadosGeografiaSeeder::class,
            MunicipiosSeeder::class,
            ParroquiasSeeder::class,

            // Seguridad (dependen de catálogos base)
            UsersSeeder::class,
            ProfilesUsersSeeder::class,
            PermissionsSeeder::class,
            ProfilePermissionsSeeder::class,
            AdminPermissionsSeeder::class,

            // Datos de persona (requerido por CursoEjemploSeeder)
            PersonasSeeder::class,

            // Datos de ejemplo
            CursoEjemploSeeder::class,

            // Funcionalidades Adicionales (después de roles/permisos base)
            PostulacionFacilitadorPermissionsSeeder::class,

            // Permisos de la interfaz de Estadísticas
            EstadisticasPermissionsSeeder::class,

            // Data de prueba para Certificación
            CertificacionTestDataSeeder::class,
        ]);
    }
}
