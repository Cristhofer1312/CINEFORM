<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModalidadEspecial extends Model
{
    use HasFactory;

    protected $table = 'taller.modalidades_especiales';
    protected $primaryKey = 'id_modalidad_especial';

    protected $fillable = [
        'nombre',
        'abreviatura',
        'status'
    ];
}
