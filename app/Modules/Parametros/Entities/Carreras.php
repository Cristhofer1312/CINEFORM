<?php

namespace Modules\Parametros\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Carreras extends Model
{
    use HasFactory;
    
    protected $table = 'comun.carreras';
    protected $primaryKey = 'id_carrera';

    protected $fillable = [];
    
    protected static function newFactory()
    {
        return \Modules\Parametros\Database\factories\CarrerasFactory::new();
    }
}
