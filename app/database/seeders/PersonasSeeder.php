<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PersonasSeeder extends Seeder
{
   
    public function run(): void
    {
        $registros = [
            [
                'user' => [
                    'username' => 'admin',
                    'email' => 'crisclasyt@gmail.com',
                    'password' => Hash::make('58826951'),
                    'register_date' => now(),
                    'active' => 1,
                    'ip' => '127.0.0.1',
                ],
                'persona' => [
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
                    'id_pais' => 210,
                    'id_estado' => 24,
                    'id_municipio' => 335,
                    'id_parroquia' => 1112, // CATEDRAL
                    'direccion' => 'Caracas, Distrito Capital',
                    'creado_por' => 1,
                    'creado_en' => now(),
                    'actualizado_por' => null,
                    'actualizado_en' => now(),
                ]
            ],
            [
                'user' => [
                    'username' => 'Maga88',
                    'email' => 'cformacioncnac@gmail.com',
                    'password' => Hash::make('Paraulata88'),
                    'register_date' => now(),
                    'active' => 1,
                    'ip' => '127.0.0.1',
                ],
                'persona' => [
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
                ]
            ],
            [
                'user' => [
                    'username' => 'Zule1101',
                    'email' => 'zuznder@gmail.com',
                    'password' => Hash::make('Chocolate'),
                    'register_date' => now(),
                    'active' => 1,
                    'ip' => '127.0.0.1',
                ],
                'persona' => [
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
                ]
            ],
            [
                'user' => [
                    'username' => 'LMillan',
                    'email' => 'Llammyp@gmail.com',
                    'password' => Hash::make('242424'),
                    'register_date' => now(),
                    'active' => 1,
                    'ip' => '127.0.0.1',
                ],
                'persona' => [
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
                    'id_parroquia' => 1127, // EL PARAISO
                    'direccion' => 'Caracas, Distrito Capital',
                    'creado_por' => 1,
                    'creado_en' => now(),
                    'actualizado_por' => null,
                    'actualizado_en' => now(),
                ]
            ],
            [
                'user' => [
                    'username' => 'Yeni Sira',
                    'email' => 'Cordinaciondeinvestigacioncnac@gmail.com',
                    'password' => Hash::make('ysira'),
                    'register_date' => now(),
                    'active' => 1,
                    'ip' => '127.0.0.1',
                ],
                'persona' => [
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
                ]
            ],
            [
                'user' => [
                    'username' => 'JCABALLEROM',
                    'email' => 'jcaballerom@cineform.ve',
                    'password' => Hash::make('Dorado!88'),
                    'register_date' => now(),
                    'active' => 1,
                    'ip' => '127.0.0.1',
                ],
                'persona' => [
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
                ]
            ],
            [
                'user' => [
                    'username' => 'NicktheFox',
                    'email' => 'nickthefox08@gmail.com',
                    'password' => Hash::make('FURRY.y.arte.19'),
                    'register_date' => now(),
                    'active' => 1,
                    'ip' => '127.0.0.1',
                ],
                'persona' => [
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
                    'id_parroquia' => 1123, // LA VEGA
                    'direccion' => 'Caracas, Distrito Capital',
                    'creado_por' => 1,
                    'creado_en' => now(),
                    'actualizado_por' => null,
                    'actualizado_en' => now(),
                ]
            ],
        ];

        foreach ($registros as $item) {
            // 1. Inserta el usuario de forma dinámica y obtiene el ID autoincremental generado por la BD
            $userId = DB::table('security.users')->insertGetId($item['user']);

            // 2. Asigna el ID real obtenido a la persona correspondiente
            $item['persona']['user_id'] = $userId;

            // 3. Inserta la persona sin IDs forzados
            DB::table('comun.personas')->insert($item['persona']);
        }
    }
}