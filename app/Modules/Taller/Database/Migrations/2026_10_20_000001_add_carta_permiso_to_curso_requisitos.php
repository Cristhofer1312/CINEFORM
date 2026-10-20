<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TITULO = 'Carta de permiso (Autorización de menores)';

    private const DESCRIPCION = 'Solo para cursos dirigidos a niños y adolescentes. Descarga la plantilla en "Documentos auxiliares", complétala con los datos del representante legal y fírmala. Súbela en formato PDF o imagen.';

    /**
     * Agrega la carta de permiso como requisito obligatorio a los cursos
     * existentes dirigidos a menores de edad (niños y adolescentes) que aún
     * no la tengan. Los cursos nuevos la reciben desde el wizard (paso 5) y
     * desde finalizar() con la misma condición.
     */
    public function up(): void
    {
        $ahora = now();

        $cursos = DB::table('taller.cursos')
            ->join(
                'taller.modalidades_especiales',
                'taller.cursos.id_modalidad_especial',
                '=',
                'taller.modalidades_especiales.id_modalidad_especial'
            )
            ->where('taller.modalidades_especiales.es_menor', true)
            ->select('taller.cursos.id_curso')
            ->get();

        foreach ($cursos as $curso) {
            $yaExiste = DB::table('taller.curso_requisitos')
                ->where('id_curso', $curso->id_curso)
                ->where('tipo', 'documento')
                ->where(function ($query) {
                    $query->whereRaw('LOWER(titulo) LIKE ?', ['%carta de permiso%'])
                        ->orWhereRaw('LOWER(titulo) LIKE ?', ['%autorización de menores%'])
                        ->orWhereRaw('LOWER(titulo) LIKE ?', ['%autorizacion de menores%']);
                })
                ->exists();

            if ($yaExiste) {
                continue;
            }

            DB::table('taller.curso_requisitos')->insert([
                'id_curso' => $curso->id_curso,
                'tipo' => 'documento',
                'titulo' => self::TITULO,
                'descripcion' => self::DESCRIPCION,
                'obligatorio' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('taller.curso_requisitos')
            ->where('titulo', self::TITULO)
            ->delete();
    }
};
