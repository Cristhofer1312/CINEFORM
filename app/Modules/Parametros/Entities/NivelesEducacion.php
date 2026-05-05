<?php

namespace Modules\Parametros\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NivelesEducacion extends Model
{
    use HasFactory;

    protected $table = 'comun.niveles_educacion';
    protected $primaryKey = 'id_nivel_educacion';
    protected $fillable = [];
    
    protected static function newFactory()
    {
        return \Modules\Parametros\Database\factories\NivelesEducacionFactory::new();
    }
}
