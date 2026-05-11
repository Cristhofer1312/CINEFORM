<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;

use App\Helpers\Encryptor;

class Estado extends Model
{
    protected $table = 'taller.estados_curso';
    protected $primaryKey = 'id_estado';

    // ...
    protected $appends = ['crypt_id'];

    public function getCryptIdAttribute()
    {
        return Encryptor::encrypt($this->id_estado);
    }

    /**
     * Get all cursos that have this estado.
     */
    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'curso_estado', 'id_estado', 'id_curso')
            ->withPivot(['created_at', 'motivo'])
            ->orderBy('curso_estado.created_at', 'desc');
    }
}
