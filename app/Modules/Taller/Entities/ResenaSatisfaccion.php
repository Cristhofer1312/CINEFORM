<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Helpers\Encryptor;

class ResenaSatisfaccion extends Model
{
    use HasFactory;

    protected $table = 'taller.resenas_satisfaccion';
    protected $primaryKey = 'id_resena';

    protected $fillable = [
        'id_curso',
        'id_inscripcion',
        'id_persona',
        'contenido',
        'pedagogico',
        'material',
        'organizacion',
    ];

    protected $appends = ['crypt_id'];

    public function getCryptIdAttribute()
    {
        return Encryptor::encrypt($this->id_resena);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion');
    }
}
