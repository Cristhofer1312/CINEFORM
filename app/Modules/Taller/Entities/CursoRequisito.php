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

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    public function respuestas()
    {
        return $this->hasMany(InscripcionRespuesta::class, 'id_requisito');
    }
}
