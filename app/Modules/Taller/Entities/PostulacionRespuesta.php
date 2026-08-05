<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;

class PostulacionRespuesta extends Model
{
    protected $table = 'taller.postulacion_respuestas';
    protected $primaryKey = 'id_respuesta';
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_postulacion',
        'id_requisito_facilitador',
        'respuesta_texto',
        'ruta_archivo',
    ];

    public function postulacion()
    {
        return $this->belongsTo(PostulacionFacilitador::class, 'id_postulacion');
    }

    public function requisito()
    {
        return $this->belongsTo(RequisitoFacilitador::class, 'id_requisito_facilitador');
    }
}