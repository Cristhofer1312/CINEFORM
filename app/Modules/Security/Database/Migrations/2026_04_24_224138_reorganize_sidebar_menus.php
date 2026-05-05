<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $menuSeguridad = DB::table('security.menus')->where('name', 'Seguridad')->first();
        $menuAdmin = DB::table('security.menus')->where('name', 'Administración')->first();

        if ($menuSeguridad && $menuAdmin) {
            // Asegurar que Seguridad esté activo
            DB::table('security.menus')
                ->where('id', $menuSeguridad->id)
                ->update(['active' => true, 'order' => 3]); // Seguridad al final

            // Mover 'Usuarios' a Seguridad
            DB::table('security.processes')
                ->where('name', 'Usuarios')
                ->update(['menu_id' => $menuSeguridad->id]);

            // Mover 'Perfiles' a Administración
            DB::table('security.processes')
                ->where('name', 'Perfiles')
                ->update(['menu_id' => $menuAdmin->id]);

            // 'Asignar Perfil' ya suele estar en Administración (id 3), aseguramos
            DB::table('security.processes')
                ->where('name', 'Asignar Perfil')
                ->update(['menu_id' => $menuAdmin->id]);
        }

        if ($menuAdmin) {
            DB::table('security.menus')
                ->where('id', $menuAdmin->id)
                ->update(['order' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $menuSeguridad = DB::table('security.menus')->where('name', 'Seguridad')->first();
        $menuAdmin = DB::table('security.menus')->where('name', 'Administración')->first();

        if ($menuSeguridad && $menuAdmin) {
            // Revertir: Todo a Seguridad
            DB::table('security.processes')
                ->whereIn('name', ['Perfiles', 'Usuarios'])
                ->update(['menu_id' => $menuSeguridad->id]);
            
            DB::table('security.menus')
                ->where('id', $menuSeguridad->id)
                ->update(['active' => true, 'order' => 1]);
            
            DB::table('security.menus')
                ->where('id', $menuAdmin->id)
                ->update(['order' => 3]);
        }
    }
};
