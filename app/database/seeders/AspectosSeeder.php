<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AspectosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('taller.aspectos')->insert([
            ['nombre' => 'Guion', 'abreviatura' => 'GN', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Direccion', 'abreviatura' => 'DR', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Maquillaje', 'abreviatura' => 'MQ', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Documental', 'abreviatura' => 'DO', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Fotografia', 'abreviatura' => 'FO', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'IA', 'abreviatura' => 'IA', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Apreciacion', 'abreviatura' => 'AP', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Animacion', 'abreviatura' => 'AN', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Actuacion', 'abreviatura' => 'AC', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Vestuario', 'abreviatura' => 'VT', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Camara', 'abreviatura' => 'CR', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Montaje', 'abreviatura' => 'MT', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Historiografia', 'abreviatura' => 'HG', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Sonido', 'abreviatura' => 'SO', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Realizacion', 'abreviatura' => 'RZ', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Fiscalizacion', 'abreviatura' => 'FZ', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Iluminacion', 'abreviatura' => 'IL', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Produccion', 'abreviatura' => 'PR', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Doblaje', 'abreviatura' => 'DJ', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Utilaria', 'abreviatura' => 'UT', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Escenografia', 'abreviatura' => 'EC', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Foquista', 'abreviatura' => 'FQ', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Edicion', 'abreviatura' => 'ED', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Script', 'abreviatura' => 'SC', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Casting', 'abreviatura' => 'CT', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Dibujo', 'abreviatura' => 'DB', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
