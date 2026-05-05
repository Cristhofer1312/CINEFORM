<?php

namespace Modules\Security\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Codes extends Model
{
    use HasFactory;

    protected $fillable = [];
    
     protected $table = "security.codes";
    public $timestamps = false;
    
    /* protected static function newFactory()
    {
        return \Modules\Security\Database\factories\CodesFactory::new();
    } */
}
