<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $table = 'taller.estados_curso';
    protected $primaryKey = 'id_estado';
    
    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    /**
     * Get all cursos that have this estado.
     */
    public function cursos()
    {
        return $this->belongsToMany(\Modules\Taller\Entities\Curso::class, 'taller.curso_estado', 'id_estado', 'id_curso')
            ->withPivot('created_at')
            ->orderBy('taller.curso_estado.created_at', 'desc');
    }
}
