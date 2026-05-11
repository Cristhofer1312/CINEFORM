<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * Agrega el permiso 'cancel_enrollment' al Process 4 (Cursos).
     */
    public function up(): void
    {
        // 1. Insertar el permiso en la tabla de permisos
        DB::table('security.permissions')->updateOrInsert(
            ['process_id' => 4, 'slug' => 'cancel_enrollment'],
            [
                'name' => 'Cancelar inscripciones de participantes',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 2. Agregar 'cancel_enrollment' al campo actions del Process 4 (Cursos)
        //    para que aparezca como checkbox en el panel de permisos.
        $process = DB::table('security.processes')->where('id', 4)->first();
        if ($process && !str_contains($process->actions, 'cancel_enrollment')) {
            DB::table('security.processes')
                ->where('id', 4)
                ->update(['actions' => $process->actions . '|cancel_enrollment']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('security.permissions')
            ->where('process_id', 4)
            ->where('slug', 'cancel_enrollment')
            ->delete();
    }
};
