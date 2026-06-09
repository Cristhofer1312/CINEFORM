<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Helpers\Encryptor;

class Inscripcion extends Model
{
    use HasFactory;

    protected $table = 'taller.inscripciones';
    protected $primaryKey = 'id_inscripcion';
    
    protected $fillable = [
        'id_curso',
        'id_persona',
        'fecha_inscripcion',
        'estado',
        'rechazada_por',
        'fecha_rechazo',
        'motivo_estado',
    ];

    // Constantes de Estado
    const ESTADO_POSTULADO = 'postulado';
    const ESTADO_APROBADO  = 'aprobado';
    const ESTADO_RECHAZADO = 'rechazado';
    const ESTADO_DENEGADO  = 'denegado';

    protected $casts = [
        'fecha_inscripcion' => 'datetime',
        'fecha_rechazo' => 'datetime',
    ];

    protected $appends = ['crypt_id'];

    public function getCryptIdAttribute()
    {
        return Encryptor::encrypt($this->id_inscripcion);
    }

    /**
     * Scope para obtener solo inscripciones formalmente aprobadas (consumen cupo)
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', self::ESTADO_APROBADO);
    }

    /**
     * Helpers de Estado
     */
    public function esPostulado() { return $this->estado === self::ESTADO_POSTULADO; }
    public function esAprobado()  { return $this->estado === self::ESTADO_APROBADO; }
    public function esRechazado() { return $this->estado === self::ESTADO_RECHAZADO; }
    public function esDenegado()  { return $this->estado === self::ESTADO_DENEGADO; }

    /**
     * Verifica si la inscripción está en un estado que no permite cursar (antiguo compatible)
     */
    public function esRechazada()
    {
        return in_array($this->estado, [self::ESTADO_RECHAZADO, self::ESTADO_DENEGADO, 'rechazada']);
    }

    /**
     * Relación con el modelo Curso
     */
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    /**
     * Relación con el modelo Persona
     */
    public function persona()
    {
        return $this->belongsTo(\Modules\Comun\Entities\PersonalData::class, 'id_persona');
    }

    /**
     * Relación con el usuario que rechazó (opcional)
     */
    public function rechazadoPor()
    {
        return $this->belongsTo(\Modules\Security\Entities\User::class, 'rechazada_por');
    }

    /**
     * Respuestas a los requisitos
     */
    public function respuestas()
    {
        return $this->hasMany(InscripcionRespuesta::class, 'id_inscripcion');
    }
}
