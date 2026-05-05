<?php

namespace Modules\Security\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\EncryptationId;

class Genders extends Model {

    use HasFactory,
        EncryptationId;

    protected $table = "security.genders";
    protected $fillable = [];
    public $timestamps = false;
    protected $appends = ['crypt_id'];
    protected $hidden = [
        'id'
    ];

    protected static function newFactory() {
        return \Modules\Security\Database\factories\GendersFactory::new();
    }
}
