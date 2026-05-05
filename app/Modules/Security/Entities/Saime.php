<?php

namespace Modules\Security\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saime extends Model
{
    use HasFactory;
    
    protected $connection = 'saime';
    protected $table = 'datos';
    protected $hidden = [
        'id',
        'first_name',
        'second_name',
        'first_surname',
        'second_surname',
        'birth_date',
        'estado_civil',
        'genero',
    ];
    public $timestamps = false;
    protected $appends = [
        'full_name',
    ];

    public function getFullNameAttribute(){
        return Upper($this->first_name.' '.$this->second_name.' '.$this->first_surname.' '.$this->second_surname);
    }
}
