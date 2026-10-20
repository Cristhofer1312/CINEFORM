<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModalidadEspecial extends Model
{
    use HasFactory;

    protected $table = 'taller.modalidades_especiales';
    protected $primaryKey = 'id_modalidad_especial';

    protected $fillable = [
        'nombre',
        'abreviatura',
        'status',
        'es_menor',
    ];

    protected $casts = [
        'es_menor' => 'boolean',
    ];

    /**
     * Indica si un público objetivo corresponde a menores de edad.
     *
     * Se usa en el wizard, donde el curso aún no existe y solo se tiene el
     * id seleccionado en el paso 1.
     */
    public static function esMenor(?int $id): bool
    {
        if (empty($id)) {
            return false;
        }

        return (bool) static::query()->whereKey($id)->value('es_menor');
    }
}
