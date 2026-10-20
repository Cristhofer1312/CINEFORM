<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;

class CursoRequisito extends Model
{
    protected $table = 'taller.curso_requisitos';
    protected $primaryKey = 'id_requisito';

    protected $fillable = [
        'id_curso',
        'tipo', // 'pregunta', 'recurso', 'documento'
        'titulo',
        'descripcion',
        'obligatorio'
    ];

    protected $casts = [
        'obligatorio' => 'boolean',
    ];

    /**
     * Título canónico del requisito legal de la carta de permiso.
     */
    public const CARTA_PERMISO_TITULO = 'Carta de permiso (Autorización de menores)';

    /**
     * Definición del requisito obligatorio de la carta de permiso.
     *
     * Se usa como valor por defecto solo en cursos dirigidos a menores de
     * edad (niños y adolescentes), para que la inscripción solicite la
     * autorización del representante legal.
     */
    public static function requisitoCartaPermiso(): array
    {
        return [
            'tipo' => 'documento',
            'titulo' => self::CARTA_PERMISO_TITULO,
            'descripcion' => 'Solo para cursos dirigidos a niños y adolescentes. Descarga la plantilla en "Documentos auxiliares", complétala con los datos del representante legal y fírmala. Súbela en formato PDF o imagen.',
            'obligatorio' => true,
        ];
    }

    /**
     * Detecta si un requisito (array del wizard o título) corresponde a la
     * carta de permiso, para no duplicarla si el curso ya la tiene.
     */
    public static function esCartaPermiso(mixed $requisito): bool
    {
        $titulo = is_array($requisito)
            ? ($requisito['titulo'] ?? '')
            : (is_string($requisito) ? $requisito : '');

        $titulo = mb_strtolower(trim($titulo));

        return str_contains($titulo, 'carta de permiso')
            || str_contains($titulo, 'autorización de menores')
            || str_contains($titulo, 'autorizacion de menores');
    }

    /**
     * Igual que esCartaPermiso(), pero exige que el requisito sea de tipo
     * documento. Evita confundir un recurso informativo o una pregunta con
     * el requisito legal de subida.
     */
    public static function esCartaPermisoDocumento(mixed $requisito): bool
    {
        if ($requisito instanceof self) {
            return $requisito->tipo === 'documento'
                && self::esCartaPermiso($requisito->titulo);
        }

        if (is_array($requisito)) {
            return ($requisito['tipo'] ?? null) === 'documento'
                && self::esCartaPermiso($requisito);
        }

        return false;
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    public function respuestas()
    {
        return $this->hasMany(InscripcionRespuesta::class, 'id_requisito');
    }
}
