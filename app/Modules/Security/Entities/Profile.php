<?php

namespace Modules\Security\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptationId;

class Profile extends Model {

    use HasFactory,
        EncryptationId;

    protected $table = "security.profiles";
    protected $hidden = ['id', 'user_id'];
    protected $appends = ['crypt_id'];
    public $timestamps = false;
    
    
    /**
     * Relación con los nuevos permisos granulares (RBAC)
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'security.profile_permissions', 'profile_id', 'permission_id')
                    ->withTimestamps();
    }
}
