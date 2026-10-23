<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * RF-83: una sola fuente para el catálogo de acciones.
     *
     * - `permissions.description`: el nombre y la descripción de cada acción viven
     *   en la fila del permiso (antes se repetían en SecurityAction::labels() y
     *   descriptions(), separados de la BD).
     * - `processes.codigo`: identificador estable del proceso. La autorización deja
     *   de depender del nombre de una ruta; `route` queda solo para navegar.
     * - `processes.en_menu`: permite procesos que agrupan permisos sin crear una
     *   entrada en el menú lateral.
     * - Se elimina `processes.actions`, una copia en texto de `permissions` que
     *   solo usaba la pantalla de permisos.
     */
    public function up(): void
    {
        Schema::table('security.permissions', function (Blueprint $table) {
            $table->text('description')->nullable();
        });

        Schema::table('security.processes', function (Blueprint $table) {
            $table->string('codigo', 60)->nullable();
            $table->boolean('en_menu')->default(true);
        });

        // El código inicial es la ruta: nada cambia de significado hasta que se renombre.
        DB::statement('UPDATE security.processes SET codigo = route WHERE codigo IS NULL');
        DB::statement('ALTER TABLE security.processes ALTER COLUMN codigo SET NOT NULL');
        DB::statement('CREATE UNIQUE INDEX processes_codigo_unique ON security.processes (codigo)');

        foreach (require database_path('data/permisos_descripciones.php') as $slug => $descripcion) {
            DB::table('security.permissions')
                ->where('slug', $slug)
                ->whereNull('description')
                ->update(['description' => $descripcion]);
        }

        Schema::table('security.processes', function (Blueprint $table) {
            $table->dropColumn('actions');
        });
    }

    public function down(): void
    {
        Schema::table('security.processes', function (Blueprint $table) {
            $table->string('actions', 200)->default('');
        });

        // Se reconstruye la copia en texto a partir de los permisos reales.
        DB::statement(<<<'SQL'
            UPDATE security.processes pr
            SET actions = COALESCE(
                (SELECT string_agg(p.slug, '|' ORDER BY p.id) FROM security.permissions p WHERE p.process_id = pr.id),
                ''
            )
        SQL);

        DB::statement('DROP INDEX IF EXISTS security.processes_codigo_unique');

        Schema::table('security.processes', function (Blueprint $table) {
            $table->dropColumn(['codigo', 'en_menu']);
        });

        Schema::table('security.permissions', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
