<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Security\Entities\User;

class ObservacionCurso extends Model
{
    use HasFactory;

    protected $table = 'taller.observaciones_curso';
    protected $primaryKey = 'id_observacion';

    protected $fillable = [
        'id_curso',
        'observacion',
        'creado_por',
    ];

    /**
     * Relación con el curso al que pertenece esta observación.
     */
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso', 'id_curso');
    }

    /**
     * Relación con el usuario que registró la observación (gerencia/coordinador).
     */
    public function autor()
    {
        return $this->belongsTo(User::class, 'creado_por', 'id');
    }
}
