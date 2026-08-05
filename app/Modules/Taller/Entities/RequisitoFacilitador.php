<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Encryptor;

class RequisitoFacilitador extends Model
{
    protected $table = 'taller.requisitos_facilitador';
    protected $primaryKey = 'id_requisito_facilitador';
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'tipo',
        'titulo',
        'descripcion',
        'obligatorio',
        'orden',
        'activo',
        'creado_por',
    ];

    protected $casts = [
        'obligatorio' => 'boolean',
        'activo' => 'boolean',
    ];

    protected $appends = ['crypt_id'];

    public function getCryptIdAttribute()
    {
        return Encryptor::encrypt($this->id_requisito_facilitador);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function respuestas()
    {
        return $this->hasMany(PostulacionRespuesta::class, 'id_requisito_facilitador');
    }

    public function creador()
    {
        return $this->belongsTo(\Modules\Security\Entities\User::class, 'creado_por');
    }
}