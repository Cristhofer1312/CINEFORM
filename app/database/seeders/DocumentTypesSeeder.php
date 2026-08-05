<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('security.document_types')->insert([
            ['code' => 'V', 'name' => 'Venezolano', 'description' => 'Venezolano', 'is_natural' => true],
            ['code' => 'E', 'name' => 'Extranjero', 'description' => 'Extranjero', 'is_natural' => true],
            ['code' => 'P', 'name' => 'Pasaporte', 'description' => 'Pasaporte', 'is_natural' => true],
            ['code' => 'V', 'name' => 'Firma Personal', 'description' => 'Firma Personal', 'is_natural' => false],
            ['code' => 'J', 'name' => 'Jurídico', 'description' => 'Jurídico', 'is_natural' => false],
            ['code' => 'G', 'name' => 'Gobierno', 'description' => 'Gobierno', 'is_natural' => false],
            ['code' => 'C', 'name' => 'Comuna', 'description' => 'Comuna', 'is_natural' => false],
        ]);
    }
}
