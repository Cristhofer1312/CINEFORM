<?php

namespace Modules\Parametros\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Estados extends Model
{
    use HasFactory;
    
    protected $table = 'comun.estados';
    protected $primaryKey = 'id';

    protected $fillable = [];
    
    protected static function newFactory()
    {
        return \Modules\Parametros\Database\factories\EstadosFactory::new();
    }
}
