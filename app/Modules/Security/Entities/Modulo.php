<?php

namespace Modules\Security\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\EncryptationId;


class Modulo extends Model
{

    use HasFactory,
        EncryptationId;

    protected $table = "security.modules";
    protected $appends = ['crypt_id'];
    protected $fillable = [];
    public $timestamps = false;
    protected $hidden = [
        'id'
    ];

    public function menus()
    {
        return $this->getMenus();
    }

    public function getMenus()
    {
        return $this->hasMany(Menu::class, 'module_id')->orderBy('order');
    }

    protected static function newFactory()
    {
        return \Modules\Users\Database\factories\ModuloFactory::new();
    }
}
