<?php

namespace Modules\Security\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptationId;

class Menu extends Model
{
    use HasFactory,
        EncryptationId;

    protected $table = "security.menus";
    protected $appends = ['crypt_id'];
    public $timestamps = false;
    protected $hidden = [
        'id'
    ];


    public function getProcess() {
        return $this->hasMany(Process::class, 'menu_id')->orderBy('order');
    }
}
