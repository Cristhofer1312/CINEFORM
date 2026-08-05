<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadosGeografiaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('comun.estados')->insert([
            ["description" => 'AMAZONAS'], //1
            ["description" => 'ANZOÁTEGUI'], //2
            ["description" => 'APURE'], //3
            ["description" => 'ARAGUA'], //4
            ["description" => 'BARINAS'], //5
            ["description" => 'BOLIVAR'], //6
            ["description" => 'CARABOBO'],//7
            ["description" => 'COJEDES'],//8
            ["description" => 'DELTA AMACURO'], //9
            ["description" => 'FALCON'], //10
            ["description" => 'GUARICO'], //11
            ["description" => 'LARA'], //12
            ["description" => 'MERIDA'], //13
            ["description" => 'MIRANDA'], //14
            ["description" => 'MONAGAS'],//15
            ["description" => 'NUEVA ESPARTA'],//16
            ["description" => 'PORTUGUESA'],//17
            ["description" => 'SUCRE'],//18
            ["description" => 'TACHIRA'],//19
            ["description" => 'TRUJILLO'], //20
            ["description" => 'LA GUAIRA'], //21
            ["description" => 'YARACUY'], //22
            ["description" => 'ZULIA'], //23
            ["description" => 'DISTRITO CAPITAL'], //24
        ]);
    }
}
