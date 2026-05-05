<?php

namespace Modules\Security\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la tabla pivote security.profile_permissions.
 * Usado para queries eficientes de filtrado en el sidebar.
 */
class ProfilePermission extends Model
{
    protected $table = 'security.profile_permissions';

    protected $fillable = [
        'profile_id',
        'permission_id',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }
}
