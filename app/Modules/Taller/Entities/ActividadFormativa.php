<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Helpers\Encryptor;

class ActividadFormativa extends Model
{
    use HasFactory;

    protected $table = 'taller.actividades_formativas';
    protected $primaryKey = 'id_actividad_formativa';

    protected $fillable = [
        'nombre',
        'abreviatura',
        'status'
    ];

    protected $appends = ['crypt_id'];

    public function getCryptIdAttribute()
    {
        return Encryptor::encrypt($this->id_actividad_formativa);
    }

    /**
     * Scope para obtener solo las actividades activas.
     */
    public function scopeActivos($query)
    {
        return $query->where('status', 'Activo');
    }

    /**
     * Cursos que usan esta actividad formativa.
     */
    public function cursos()
    {
        return $this->hasMany(Curso::class, 'id_actividad_formativa', 'id_actividad_formativa');
    }
}
