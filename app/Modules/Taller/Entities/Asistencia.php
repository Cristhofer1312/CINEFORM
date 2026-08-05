<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Helpers\Encryptor;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'taller.asistencias';
    protected $primaryKey = 'id_asistencia';
    public $timestamps = false;

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_contenido_curso', 'id_persona', 'id_inscripcion',
        'fecha_hora_marcado', 'activa', 'anulada_por',
        'fecha_anulacion', 'motivo_anulacion',
        'ip_marcado', 'metodo_marcado',
    ];

    protected $casts = [
        'fecha_hora_marcado' => 'datetime',
        'fecha_anulacion' => 'datetime',
        'activa' => 'boolean',
    ];

    protected $appends = ['crypt_id'];

    public function getCryptIdAttribute()
    {
        return Encryptor::encrypt($this->id_asistencia);
    }

    public function actividad()
    {
        return $this->belongsTo(ContenidoCurso::class, 'id_contenido_curso', 'id_contenido_curso');
    }

    public function persona()
    {
        return $this->belongsTo(\Modules\Comun\Entities\PersonalData::class, 'id_persona', 'id_persona');
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion', 'id_inscripcion');
    }

    public function anulador()
    {
        return $this->belongsTo(\Modules\Security\Entities\User::class, 'anulada_por');
    }

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopeAnuladas($query)
    {
        return $query->where('activa', false);
    }
}
