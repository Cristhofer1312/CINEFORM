<?php

namespace Modules\Security\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptationId;

class Process extends Model
{
    use HasFactory,
        EncryptationId;
    protected $table = "security.processes";
    protected $appends = ['crypt_id',  'profile_array'];
    protected $hidden = ['id'];
    public $timestamps = false;
    
    
   
    protected $fillable = [];

    /**
     * Relación con los permisos granulares (RBAC).
     * Un proceso tiene múltiples permisos (view, create, edit, etc.)
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'process_id');
    }
   
    
   


    public function getProfileArrayAttribute() {
        return \Illuminate\Support\Facades\DB::table('security.profile_permissions')
            ->join('security.permissions', 'security.profile_permissions.permission_id', '=', 'security.permissions.id')
            ->where('security.permissions.process_id', $this->id)
            ->pluck('profile_id')
            ->unique()
            ->toArray();
    }

    public function getProfile() {
        return $this->belongsToMany(Profile::class, 'security.profile_processes')
                        ->withPivot('profile_id', 'process_id', 'actions')
                ;
    }
}
