<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;

class InscripcionRespuesta extends Model
{
    protected $table = 'taller.inscripcion_respuestas';
    protected $primaryKey = 'id_respuesta';

    protected $fillable = [
        'id_inscripcion',
        'id_requisito',
        'respuesta_texto',
        'ruta_archivo',
    ];

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion');
    }

    public function requisito()
    {
        return $this->belongsTo(CursoRequisito::class, 'id_requisito');
    }
}
