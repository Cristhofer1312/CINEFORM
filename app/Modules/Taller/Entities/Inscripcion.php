<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Helpers\Encryptor;

class Inscripcion extends Model
{
    use HasFactory;

    protected $table = 'taller.inscripciones';
    protected $primaryKey = 'id_inscripcion';
    
    protected $fillable = [
        'id_curso',
        'id_persona',
        'fecha_inscripcion',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'datetime',
    ];

    protected $appends = ['crypt_id'];

    public function getCryptIdAttribute()
    {
        return Encryptor::encrypt($this->id_inscripcion);
    }

    /**
     * Relación con el modelo Curso
     */
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    /**
     * Relación con el modelo Persona
     */
    public function persona()
    {
        return $this->belongsTo(\Modules\Comun\Entities\PersonalData::class, 'id_persona');
    }
}
