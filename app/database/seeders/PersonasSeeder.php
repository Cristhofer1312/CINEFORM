<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('comun.personas')->insert([
            [
                'user_id' => 1,
                'tipo_dni' => 1,
                'dni' => '12345678',
                'pasaporte' => '12345678',
                'rif' => '12345678',
                'reg_nac_cine' => '12345678',
                'genero' => 1,
                'primer_nombre' => 'Cristhofer',
                'primer_apellido' => 'Leon',
                'telefono' => '12345678',
                'telefono_opcional' => '12345678',
                'id_pais' => 1,
                'id_estado' => 1,
                'id_municipio' => 1,
                'id_parroquia' => 1,
                'direccion' => '12345678',
                'creado_por' => 1,
                'creado_en' => now(),
                'actualizado_por' => 1,
                'actualizado_en' => now(),
            ],
            [
                'user_id' => 2, // El nuevo usuario 'participante'
                'tipo_dni' => 1,
                'dni' => '87654321',
                'pasaporte' => '87654321',
                'rif' => '87654321',
                'reg_nac_cine' => '87654321',
                'genero' => 1,
                'primer_nombre' => 'Juan',
                'primer_apellido' => 'Participante',
                'telefono' => '87654321',
                'telefono_opcional' => '87654321',
                'id_pais' => 1,
                'id_estado' => 1,
                'id_municipio' => 1,
                'id_parroquia' => 1,
                'direccion' => 'Calle Falsa 123',
                'creado_por' => 1,
                'creado_en' => now(),
                'actualizado_por' => 1,
                'actualizado_en' => now(),
            ]
        ]);
    }
}
