<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Rellena `permissions.description` desde la fuente única de descripciones
 * (database/data/permisos_descripciones.php). Va al final de DatabaseSeeder,
 * cuando todos los permisos ya existen. No sobrescribe textos ya puestos.
 */
class PermisosDescripcionesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (require database_path('data/permisos_descripciones.php') as $slug => $descripcion) {
            DB::table('security.permissions')
                ->where('slug', $slug)
                ->whereNull('description')
                ->update(['description' => $descripcion]);
        }
    }
}
