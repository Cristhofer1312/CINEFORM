<?php

namespace Modules\Security\Entities;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptationId;
class DocumentType extends Model
{
    use HasFactory, EncryptationId;
    protected $table = 'security.document_types';
    protected $hidden = [ 'id'];
    protected $appends = ['crypt_id'];
}