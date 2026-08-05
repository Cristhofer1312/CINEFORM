<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tablas sin dependencias
        Schema::create('comun.estados', function (Blueprint $table) {
            $table->id();
            $table->string('description', 50);
        });

        Schema::create('comun.carreras', function (Blueprint $table) {
            $table->increments('id_carrera');
            $table->string('nombre_carrera', 255);
            $table->string('descripcion', 255)->nullable();
            $table->string('status', 50)->nullable();
            $table->integer('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->integer('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        Schema::create('comun.niveles_educacion', function (Blueprint $table) {
            $table->increments('id_nivel_educacion');
            $table->string('nivel', 255);
            $table->string('descripcion', 255)->nullable();
            $table->string('status', 50)->nullable();
            $table->integer('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->integer('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        // 2. comun.municipios → comun.estados
        Schema::create('comun.municipios', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('state_id');
            $table->foreign('state_id')->references('id')->on('comun.estados')->onDelete('cascade');
        });

        // 3. comun.parroquias → comun.municipios
        Schema::create('comun.parroquias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('municipality_id');
            $table->foreign('municipality_id')->references('id')->on('comun.municipios')->onDelete('cascade');
        });

        // Sync sequences
        foreach (['comun.estados', 'comun.municipios', 'comun.parroquias'] as $table) {
            DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 0) + 1, false)");
        }
    }

    public function down(): void
    {
        $tables = [
            'comun.parroquias',
            'comun.municipios',
            'comun.niveles_educacion',
            'comun.carreras',
            'comun.estados',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
