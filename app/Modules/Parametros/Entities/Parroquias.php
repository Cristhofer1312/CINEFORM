<?php

namespace Modules\Parametros\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Parroquias extends Model
{
    use HasFactory;
    
    protected $table = 'comun.parroquias';
    protected $primaryKey = 'id_parroquia';

    protected $fillable = [];
    
    protected static function newFactory()
    {
        return \Modules\Parametros\Database\factories\ParroquiasFactory::new();
    }
}
