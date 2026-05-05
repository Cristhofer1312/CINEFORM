<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Aspecto extends Model
{
    use HasFactory;

    protected $table = 'taller.aspectos';
    protected $primaryKey = 'id_aspecto';

    protected $fillable = [
        'nombre',
        'abreviatura',
        'status'
    ];

    /**
     * Scope para obtener solo los aspectos activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('status', 'Activo');
    }

    /**
     * Cursos que usan este aspecto.
     */
    public function cursos()
    {
        return $this->hasMany(Curso::class, 'id_aspecto', 'id_aspecto');
    }
}
