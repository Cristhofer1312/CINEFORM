<?php

namespace Modules\Parametros\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Municipios extends Model
{
    use HasFactory;
    
    protected $table = 'comun.municipios';
    protected $primaryKey = 'id_municipio';

    protected $fillable = [];
    
    protected static function newFactory()
    {
        return \Modules\Parametros\Database\factories\MunicipiosFactory::new();
    }
}
