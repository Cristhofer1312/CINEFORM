<?php

namespace Modules\Comun\Entities;

use Illuminate\Database\Eloquent\Model;

class Especializacion extends Model
{
    protected $table = 'comun.especializaciones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'status',
        'creado_por',
        'creado_en',
        'actualizado_por',
        'actualizado_en'
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    public function personas()
    {
        return $this->belongsToMany(PersonalData::class, 'comun.personas_especializacion', 'id_especializacion', 'id_persona')
            ->withPivot('anos_experiencia');
    }
}
