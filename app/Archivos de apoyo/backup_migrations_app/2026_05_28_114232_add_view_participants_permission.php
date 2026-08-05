<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $process = DB::table('security.processes')->where('route', 'taller.cursos.index')->first();
        
        if ($process) {
            DB::table('security.permissions')->insertOrIgnore([
                'process_id' => $process->id,
                'slug' => 'view_participants',
                'name' => 'Ver Participantes Inscritos',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Agregar a actions del proceso
            if (strpos($process->actions, 'view_participants') === false) {
                $newActions = $process->actions ? $process->actions . '|view_participants' : 'view_participants';
                DB::table('security.processes')
                    ->where('id', $process->id)
                    ->update(['actions' => $newActions]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $process = DB::table('security.processes')->where('route', 'taller.cursos.index')->first();
        
        if ($process) {
            DB::table('security.permissions')
                ->where('process_id', $process->id)
                ->where('slug', 'view_participants')
                ->delete();
        }
    }
};
