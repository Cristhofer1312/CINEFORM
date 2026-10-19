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

            // Datos de usuario y persona unificados (genera los IDs automáticamente)
            PersonasSeeder::class,

            // Seguridad (dependen de los usuarios creados en PersonasSeeder)
            ProfilesUsersSeeder::class,
            PermissionsSeeder::class,
            ProfilePermissionsSeeder::class,
            AdminPermissionsSeeder::class,

            // Funcionalidades Adicionales (después de roles/permisos base)
            PostulacionFacilitadorPermissionsSeeder::class,

            // Permisos de la interfaz de Estadísticas
            EstadisticasPermissionsSeeder::class,
        ]);
    }
}
