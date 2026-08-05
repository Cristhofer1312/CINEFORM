<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comun.personas_especializacion', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('id_persona')->nullable(); // "id_persona" int
            $table->integer('id_especializacion')->nullable(); // "id_especializacion" int
            $table->integer('anos_experiencia')->nullable(); // "anos_experiencia" int

            // Columnas de auditoría
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();

            // Llave foránea (se agregará en la migración de llaves foráneas)
            //  $table->foreign('id_persona')->references('id_persona')->on('comun_personas'); 
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comun.personas_especializacion');
    }
};