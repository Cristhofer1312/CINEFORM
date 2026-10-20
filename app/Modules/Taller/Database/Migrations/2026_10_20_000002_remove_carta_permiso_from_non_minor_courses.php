<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Corrige instalaciones donde la carta de permiso se agregó a todos los
     * cursos antes de conocer la regla definitiva: solo debe exigirse en
     * cursos dirigidos a niños y adolescentes.
     *
     * Si un curso no es para menores pero ya tiene respuestas cargadas para
     * la carta, no se borra la evidencia: el requisito queda como no
     * obligatorio y la pantalla de inscripción lo oculta.
     */
    public function up(): void
    {
        $cartas = DB::table('taller.curso_requisitos as cr')
            ->join('taller.cursos as c', 'c.id_curso', '=', 'cr.id_curso')
            ->leftJoin(
                'taller.modalidades_especiales as me',
                'me.id_modalidad_especial',
                '=',
                'c.id_modalidad_especial'
            )
            ->where('cr.tipo', 'documento')
            ->where(function ($query) {
                $query->whereRaw('LOWER(cr.titulo) LIKE ?', ['%carta de permiso%'])
                    ->orWhereRaw('LOWER(cr.titulo) LIKE ?', ['%autorización de menores%'])
                    ->orWhereRaw('LOWER(cr.titulo) LIKE ?', ['%autorizacion de menores%']);
            })
            ->where(function ($query) {
                $query->whereNull('me.es_menor')->orWhere('me.es_menor', false);
            })
            ->select('cr.id_requisito')
            ->get();

        foreach ($cartas as $carta) {
            $tieneRespuestas = DB::table('taller.inscripcion_respuestas')
                ->where('id_requisito', $carta->id_requisito)
                ->exists();

            if ($tieneRespuestas) {
                DB::table('taller.curso_requisitos')
                    ->where('id_requisito', $carta->id_requisito)
                    ->update([
                        'obligatorio' => false,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('taller.curso_requisitos')
                    ->where('id_requisito', $carta->id_requisito)
                    ->delete();
            }
        }
    }

    /**
     * No se restaura: devolver la carta a cursos que no son para menores
     * reintroduciría el error que esta migración corrige.
     */
    public function down(): void
    {
        //
    }
};
