<?php

namespace Modules\Security\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptationId;

class ProfileUser extends Model
{
     use HasFactory,
        EncryptationId;

    protected $fillable = [];
    protected $table = "security.profiles_users";
    protected $hidden = ['id_rol_persona', 'id_users'];
    protected $appends = ['crypt_id'];
    public $timestamps = false;
    
}
