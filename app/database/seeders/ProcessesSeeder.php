<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcessesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('security.processes')->insert([
            [
                'name' => 'Perfiles',
                'description' => 'Administración de perfiles y roles del sistema',
                'icon' => 'fas fa-user-friends',
                'route' => 'profiles',
                'codigo' => 'profiles',
                'order' => 1,
                'active' => true,
                'menu_id' => 1,
            ],
            [
                'name' => 'Usuarios',
                'description' => 'Gestión de cuentas de usuario y credenciales',
                'icon' => 'fas fa-user',
                'route' => 'users',
                'codigo' => 'users',
                'order' => 2,
                'active' => true,
                'menu_id' => 1,
            ],
            [
                'name' => 'Planificar Curso',
                'description' => 'Diseñar y registrar un nuevo plan de curso',
                'icon' => 'fas fa-plus-circle',
                'route' => 'taller.cursos.create',
                'codigo' => 'taller.cursos.create',
                'order' => 1,
                'active' => true,
                'menu_id' => 2,
            ],
            [
                'name' => 'Cursos',
                'description' => 'Gestión Académica de Cursos y Formaciones',
                'icon' => 'fas fa-graduation-cap',
                'route' => 'taller.cursos.index',
                'codigo' => 'taller.cursos.index',
                'order' => 2,
                'active' => true,
                'menu_id' => 2,
            ],
            [
                'name' => 'Mis Cursos',
                'description' => 'Ver los cursos en los que estoy inscrito',
                'icon' => 'fas fa-book-reader',
                'route' => 'taller.mis-cursos',
                'codigo' => 'taller.mis-cursos',
                'order' => 3,
                'active' => true,
                'menu_id' => 2,
            ],
            [
                'name' => 'Asignar Perfil',
                'description' => 'Buscar usuario por DNI y asignar/quitar perfiles',
                'icon' => 'fas fa-user-tag',
                'route' => 'users.asignar_perfil',
                'codigo' => 'users.asignar_perfil',
                'order' => 1,
                'active' => true,
                'menu_id' => 3,
            ],
            [
                'name' => 'Agregar Tipo de Actividad',
                'description' => 'Gestión de catálogos: Actividades Formativas y Aspectos de Formación',
                'icon' => 'fas fa-layer-group',
                'route' => 'taller.catalogos.index',
                'codigo' => 'taller.catalogos.index',
                'order' => 2,
                'active' => true,
                'menu_id' => 3,
            ],
        ]);
    }
}
