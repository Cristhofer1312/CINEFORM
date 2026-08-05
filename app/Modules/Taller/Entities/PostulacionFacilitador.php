<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Helpers\Encryptor;

class PostulacionFacilitador extends Model
{
    use HasFactory;

    protected $table = 'taller.postulaciones_facilitador';
    protected $primaryKey = 'id_postulacion';
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_persona',
        'estado',
        'motivo_rechazo',
        'revisada_por',
        'fecha_revision',
    ];

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_APROBADA = 'aprobada';
    const ESTADO_RECHAZADA = 'rechazada';

    protected $casts = [
        'fecha_revision' => 'datetime',
    ];

    protected $appends = ['crypt_id'];

    public function getCryptIdAttribute()
    {
        return Encryptor::encrypt($this->id_postulacion);
    }

    // Helpers
    public function esPendiente() { return $this->estado === self::ESTADO_PENDIENTE; }
    public function esAprobada() { return $this->estado === self::ESTADO_APROBADA; }
    public function esRechazada() { return $this->estado === self::ESTADO_RECHAZADA; }

    // Relaciones
    public function persona()
    {
        return $this->belongsTo(\Modules\Comun\Entities\PersonalData::class, 'id_persona', 'id_persona');
    }

    public function revisor()
    {
        return $this->belongsTo(\Modules\Security\Entities\User::class, 'revisada_por');
    }

    public function respuestas()
    {
        return $this->hasMany(PostulacionRespuesta::class, 'id_postulacion');
    }
}
