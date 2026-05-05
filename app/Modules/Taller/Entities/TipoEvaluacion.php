<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoEvaluacion extends Model
{
    use HasFactory;

    protected $table = 'taller.tipo_evaluaciones';
    protected $primaryKey = 'id_tipo_evaluacion';

    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'creado_por',
        'actualizado_por'
    ];

    // Nombres de columnas timestamp personalizadas según migración
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';
}
