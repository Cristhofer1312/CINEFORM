<?php

namespace Modules\Security\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptationId;

class ProfileProcesse extends Model
{
    use HasFactory,
        EncryptationId;
    
    
    protected $table = "security.profile_processes";
    protected $hidden = ['id'];
    protected $appends = ['crypt_id'];
    public $timestamps = false;
}
