<?php

namespace Modules\Security\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\EncryptationId;

class Countries extends Model {

    use HasFactory,
        EncryptationId;

    protected $fillable = [];
    protected $table = "security.countries";
    public $timestamps = false;
    protected $hidden = ['id'];

    protected static function newFactory() {
        return \Modules\Security\Database\factories\CountriesFactory::new();
    }
}
