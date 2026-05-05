<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taller.estados_curso', function (Blueprint $table) {
            $table->id('id_estado');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        // Insertar estados iniciales
        DB::table('taller.estados_curso')->insert([
            ['nombre' => 'Por_aceptar', 'descripcion' => 'Facilitador aun no acepta el curso'],
            ['nombre' => 'Rechazado', 'descripcion' => 'Facilitador rechaza el curso'],
            ['nombre' => 'Declinado', 'descripcion' => 'Curso declinado por el organizador'],
            ['nombre' => 'Edicion', 'descripcion' => 'Facilitador evalua la plantilla entregada para edicion de la misma a sus necesidades'],
            ['nombre' => 'Aprobacion', 'descripcion' => 'Fase donde el analista o el coordinador aprueba el curso'],
            ['nombre' => 'Inscripcion', 'descripcion' => 'Fase donde el curso esta listo para inscribirse'],
            ['nombre' => 'En_curso', 'descripcion' => 'Fase donde se imparte el curso'],
            ['nombre' => 'Finalizado', 'descripcion' => 'Fase de emision de certificados y aclaraciones con el facilitador'],
            ['nombre' => 'Cerrado', 'descripcion' => 'Fase final del curso donde solamente se puede solicitar la emision de certificados'],
        ]);
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taller.estados_curso');
    }
};
