<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulesMenusSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('security.modules')->insert([
            ['name' => 'Security', 'description' => '', 'icon' => 'fas fa-user-lock', 'order' => 1],
        ]);

        DB::table('security.menus')->insert([
            [
                'name' => 'Seguridad',
                'description' => 'Gestión de perfiles, usuarios y permisos',
                'icon' => 'fas fa-user-shield',
                'order' => 3,
                'active' => true,
                'module_id' => 1,
            ],
            [
                'name' => 'Formación',
                'description' => 'Gestión de cursos, talleres y formaciones',
                'icon' => 'fas fa-graduation-cap',
                'order' => 2,
                'active' => true,
                'module_id' => 1,
            ],
            [
                'name' => 'Administración',
                'description' => 'Herramientas administrativas del sistema',
                'icon' => 'fas fa-cogs',
                'order' => 1,
                'active' => true,
                'module_id' => 1,
            ],
        ]);
    }
}
