<?php

namespace Modules\Security\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory;

    protected $table = 'security.permissions';

    protected $fillable = [
        'name',
        'slug',
        'process_id'
    ];

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id');
    }

    public function profiles()
    {
        return $this->belongsToMany(Profile::class, 'security.profile_permissions', 'permission_id', 'profile_id');
    }

    /**
     * Relación directa con la tabla pivote profile_permissions.
     * Usada para filtrar eficientemente qué perfiles tienen este permiso.
     */
    public function profilePermissions()
    {
        return $this->hasMany(ProfilePermission::class, 'permission_id');
    }
}
