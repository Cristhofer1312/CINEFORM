<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Falta justificada por el facilitador (D3): cuenta como asistencia cumplida
 * para certificar.
 */
class AsistenciaJustificacion extends Model
{
    protected $table = 'taller.asistencia_justificaciones';

    protected $primaryKey = 'id_justificacion';

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_contenido_curso', 'id_inscripcion', 'motivo', 'justificada_por',
    ];

    public function actividad()
    {
        return $this->belongsTo(ContenidoCurso::class, 'id_contenido_curso', 'id_contenido_curso');
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion', 'id_inscripcion');
    }

    public function autor()
    {
        return $this->belongsTo(\Modules\Security\Entities\User::class, 'justificada_por');
    }
}
