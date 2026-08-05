<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfilesUsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('security.profiles_users')->insert([
            ['id_rol' => 1, 'id_users' => 1, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            ['id_rol' => 2, 'id_users' => 1, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            ['id_rol' => 3, 'id_users' => 1, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            ['id_rol' => 4, 'id_users' => 1, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            
            // Perfil para el nuevo usuario 'participante' (ID 2)
            ['id_rol' => 3, 'id_users' => 2, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
        ]);
    }
}
