<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;

class Modalidad extends Model
{
    protected $table = 'taller.modalidad';
    protected $primaryKey = 'id_modalidad';

    public $timestamps = true;
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
    
    protected $fillable = [
        'nombre_modalidad',
        'abreviatura',
        'descripcion',
        'status',
        'creado_por',
        'actualizado_por'
    ];
}