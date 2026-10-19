<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfilesUsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('security.profiles_users')->insert([
            // Admin (ID 1): todos los perfiles
            ['id_rol' => 1, 'id_users' => 1, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            ['id_rol' => 2, 'id_users' => 1, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            ['id_rol' => 3, 'id_users' => 1, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            ['id_rol' => 4, 'id_users' => 1, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            // Mariana Garcia (ID 2): Coordinadora + Participante
            ['id_rol' => 3, 'id_users' => 2, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            ['id_rol' => 4, 'id_users' => 2, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            // Zuleika Tovar (ID 3): Participante
            ['id_rol' => 3, 'id_users' => 3, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            // LLammy Millan (ID 4): Todos los perfiles
            ['id_rol' => 1, 'id_users' => 4, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            ['id_rol' => 2, 'id_users' => 4, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            ['id_rol' => 3, 'id_users' => 4, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            ['id_rol' => 4, 'id_users' => 4, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            // Yenni Sira (ID 5): Facilitadora + Participante
            ['id_rol' => 2, 'id_users' => 5, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            ['id_rol' => 3, 'id_users' => 5, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            // Jennifer Caballero (ID 6): Facilitadora + Participante
            ['id_rol' => 2, 'id_users' => 6, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            ['id_rol' => 3, 'id_users' => 6, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
            // Nicolas Hernandez (ID 7): Participante
            ['id_rol' => 3, 'id_users' => 7, 'status' => 1, 'creado_por' => 1, 'creado_en' => now()],
        ]);
    }
}
