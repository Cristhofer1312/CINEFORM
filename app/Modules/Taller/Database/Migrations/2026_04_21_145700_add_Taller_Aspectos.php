<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Crear tabla catálogo para los Aspectos (Tipos de Cursos)
        if (!Schema::hasTable('taller.aspectos')) {
            Schema::create('taller.aspectos', function (Blueprint $table) {
                $table->id('id_aspecto');
                $table->string('nombre'); // Fotografía, Actuación, Producción, etc.
                $table->string('abreviatura')->nullable();
                $table->string('status')->default('Activo');
                $table->timestamps();
            });

            // Poblar con el diccionario de datos confirmado (26 aspectos)
            DB::table('taller.aspectos')->insert([
                ['nombre' => 'Guion',          'abreviatura' => 'GN', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Dirección',      'abreviatura' => 'DR', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Maquillaje',     'abreviatura' => 'MQ', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Documental',     'abreviatura' => 'DO', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Fotografía',     'abreviatura' => 'FO', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'IA',             'abreviatura' => 'IA', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Apreciación',    'abreviatura' => 'AP', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Animación',      'abreviatura' => 'AN', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Actuación',      'abreviatura' => 'AC', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Vestuario',      'abreviatura' => 'VT', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Cámara',         'abreviatura' => 'CR', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Montaje',        'abreviatura' => 'MT', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Historiografía', 'abreviatura' => 'HG', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Sonido',         'abreviatura' => 'SO', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Realización',    'abreviatura' => 'RZ', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Fiscalización',  'abreviatura' => 'FZ', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Iluminación',    'abreviatura' => 'IL', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Producción',     'abreviatura' => 'PR', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Doblaje',        'abreviatura' => 'DJ', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Utilería',       'abreviatura' => 'UT', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Escenografía',   'abreviatura' => 'EC', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Foquista',       'abreviatura' => 'FQ', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Edición',        'abreviatura' => 'ED', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Script',         'abreviatura' => 'SC', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Casting',        'abreviatura' => 'CT', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Dibujo',         'abreviatura' => 'DB', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 2. Modificar tabla taller_cursos para agregar columnas faltantes
        Schema::table('taller.cursos', function (Blueprint $table) {
            if (!Schema::hasColumn('taller.cursos', 'codigo')) {
                $table->string('codigo')->nullable()->after('id_curso');
            }
            if (!Schema::hasColumn('taller.cursos', 'id_aspecto')) {
                $table->unsignedBigInteger('id_aspecto')->nullable()->after('id_modalidad');
            }
            if (!Schema::hasColumn('taller.cursos', 'id_estado')) {
                $table->unsignedBigInteger('id_estado')->nullable()->comment('Región del curso')->after('id_aspecto');
            }
            if (!Schema::hasColumn('taller.cursos', 'nivel')) {
                $table->string('nivel')->nullable()->comment('Básico, Medio, Avanzado')->after('descripcion');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('taller.cursos', function (Blueprint $table) {
            $table->dropColumn(['codigo', 'id_aspecto', 'id_estado', 'nivel']);
        });

        Schema::dropIfExists('taller.aspectos');
    }
};
