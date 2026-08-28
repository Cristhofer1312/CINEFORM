<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonasSeeder extends Seeder
{
    /**
     * Todos los usuarios son de Caracas (Distrito Capital):
     * - id_pais = 1 (Venezuela)
     * - id_estado = 24 (Distrito Capital)
     * - id_municipio = 335 (Libertador)
     * - id_parroquia = varía entre las 22 parroquias del municipio Libertador
     *
     * Parroquias de Caracas (municipio 335) en orden del ParroquiasSeeder:
     *   1109 = ALTAGRACIA       1115 = SAN JOSE          1121 = LA VEGA
     *   1110 = ANTIMANO          1116 = SAN JUAN          1122 = MACARAO
     *   1111 = CANDELARIA        1117 = SANTA ROSALIA     1123 = CARICUAO
     *   1112 = CATEDRAL          1118 = SANTA TERESA      1124 = EL JUNQUITO
     *   1113 = LA PASTORA        1119 = SUCRE             1125 = EL PARAISO
     *   1114 = SAN AGUSTIN       1120 = 23 DE ENERO       1126 = SAN BERNARDINO
     *                                                      1127 = SAN PEDRO
     *                                                      1128 = SANTA ROSA DE LIMA
     *                            1129 = EL RECREO
     *                            1130 = EL VALLE
     */
    public function run(): void
    {
        DB::table('comun.personas')->insert([
            // Persona 1: Admin (user_id = 1)
            [
                'user_id' => 1,
                'tipo_dni' => 1, // Venezolano
                'dni' => '12345678',
                'pasaporte' => null,
                'rif' => null,
                'reg_nac_cine' => null,
                'genero' => 1, // Masculino
                'primer_nombre' => 'Cristhofer',
                'segundo_nombre' => null,
                'primer_apellido' => 'Leon',
                'segundo_apellido' => null,
                'telefono' => '04121234567',
                'telefono_opcional' => null,
                'id_pais' => 1,
                'id_estado' => 24,
                'id_municipio' => 335,
                'id_parroquia' => 1112, // CATEDRAL
                'direccion' => 'Caracas, Distrito Capital',
                'creado_por' => 1,
                'creado_en' => now(),
                'actualizado_por' => null,
                'actualizado_en' => now(),
            ],
            // Persona 2: Mariana García (user_id = 2)
            [
                'user_id' => 2,
                'tipo_dni' => 1,
                'dni' => '4774494',
                'pasaporte' => null,
                'rif' => null,
                'reg_nac_cine' => null,
                'genero' => 2, // Femenino
                'primer_nombre' => 'Mariana',
                'segundo_nombre' => null,
                'primer_apellido' => 'García',
                'segundo_apellido' => null,
                'telefono' => '04141234567',
                'telefono_opcional' => null,
                'id_pais' => 1,
                'id_estado' => 24,
                'id_municipio' => 335,
                'id_parroquia' => 1111, // CANDELARIA
                'direccion' => 'Caracas, Distrito Capital',
                'creado_por' => 1,
                'creado_en' => now(),
                'actualizado_por' => null,
                'actualizado_en' => now(),
            ],
            // Persona 3: Zuleika Yanireth Tovar (user_id = 3)
            [
                'user_id' => 3,
                'tipo_dni' => 1,
                'dni' => '20418757',
                'pasaporte' => null,
                'rif' => null,
                'reg_nac_cine' => null,
                'genero' => 2,
                'primer_nombre' => 'Zuleika',
                'segundo_nombre' => 'Yanireth',
                'primer_apellido' => 'Tovar',
                'segundo_apellido' => null,
                'telefono' => '04241234567',
                'telefono_opcional' => null,
                'id_pais' => 1,
                'id_estado' => 24,
                'id_municipio' => 335,
                'id_parroquia' => 1119, // SUCRE
                'direccion' => 'Caracas, Distrito Capital',
                'creado_por' => 1,
                'creado_en' => now(),
                'actualizado_por' => null,
                'actualizado_en' => now(),
            ],
            // Persona 4: LLammy Millan Petit (user_id = 4)
            [
                'user_id' => 4,
                'tipo_dni' => 1,
                'dni' => '12280007',
                'pasaporte' => null,
                'rif' => null,
                'reg_nac_cine' => null,
                'genero' => 2,
                'primer_nombre' => 'LLammy',
                'segundo_nombre' => null,
                'primer_apellido' => 'Millan',
                'segundo_apellido' => 'Petit',
                'telefono' => '04161234567',
                'telefono_opcional' => null,
                'id_pais' => 1,
                'id_estado' => 24,
                'id_municipio' => 335,
                'id_parroquia' => 1125, // EL PARAISO
                'direccion' => 'Caracas, Distrito Capital',
                'creado_por' => 1,
                'creado_en' => now(),
                'actualizado_por' => null,
                'actualizado_en' => now(),
            ],
            // Persona 5: Yenni Sira Piñango (user_id = 5)
            [
                'user_id' => 5,
                'tipo_dni' => 1,
                'dni' => '11937060',
                'pasaporte' => null,
                'rif' => null,
                'reg_nac_cine' => null,
                'genero' => 2,
                'primer_nombre' => 'Yenni',
                'segundo_nombre' => null,
                'primer_apellido' => 'Sira',
                'segundo_apellido' => 'Piñango',
                'telefono' => '04261234567',
                'telefono_opcional' => null,
                'id_pais' => 1,
                'id_estado' => 24,
                'id_municipio' => 335,
                'id_parroquia' => 1109, // ALTAGRACIA
                'direccion' => 'Caracas, Distrito Capital',
                'creado_por' => 1,
                'creado_en' => now(),
                'actualizado_por' => null,
                'actualizado_en' => now(),
            ],
            // Persona 6: Jennifer Lourdes Caballero Moreno (user_id = 6)
            [
                'user_id' => 6,
                'tipo_dni' => 1,
                'dni' => '10542321',
                'pasaporte' => null,
                'rif' => null,
                'reg_nac_cine' => null,
                'genero' => 2,
                'primer_nombre' => 'Jennifer',
                'segundo_nombre' => 'Lourdes',
                'primer_apellido' => 'Caballero',
                'segundo_apellido' => 'Moreno',
                'telefono' => '04121234568',
                'telefono_opcional' => null,
                'id_pais' => 1,
                'id_estado' => 24,
                'id_municipio' => 335,
                'id_parroquia' => 1113, // LA PASTORA
                'direccion' => 'Caracas, Distrito Capital',
                'creado_por' => 1,
                'creado_en' => now(),
                'actualizado_por' => null,
                'actualizado_en' => now(),
            ],
            // Persona 7: Nicolás Hernández (user_id = 7)
            [
                'user_id' => 7,
                'tipo_dni' => 1,
                'dni' => '29602136',
                'pasaporte' => null,
                'rif' => null,
                'reg_nac_cine' => null,
                'genero' => 1, // Masculino
                'primer_nombre' => 'Nicolás',
                'segundo_nombre' => null,
                'primer_apellido' => 'Hernández',
                'segundo_apellido' => null,
                'telefono' => '04141234568',
                'telefono_opcional' => null,
                'id_pais' => 1,
                'id_estado' => 24,
                'id_municipio' => 335,
                'id_parroquia' => 1121, // LA VEGA
                'direccion' => 'Caracas, Distrito Capital',
                'creado_por' => 1,
                'creado_en' => now(),
                'actualizado_por' => null,
                'actualizado_en' => now(),
            ],
        ]);
    }
}
