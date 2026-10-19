<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Insertar el estado BORRADOR (id_estado = 10, auto-increment)
        $exists = DB::table('taller.estados_curso')
            ->where('nombre', 'Borrador')
            ->exists();

        if (!$exists) {
            DB::table('taller.estados_curso')->insert([
                'id_estado' => 10,
                'nombre' => 'Borrador',
                'descripcion' => 'Curso en proceso de creación por el facilitador',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('taller.estados_curso')
            ->where('nombre', 'Borrador')
            ->delete();
    }
};
