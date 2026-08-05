<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Helpers\Encryptor;

class AsistenciaToken extends Model
{
    use HasFactory;

    protected $table = 'taller.asistencia_tokens';
    protected $primaryKey = 'id_token';
    public $timestamps = false;

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_contenido_curso', 'token', 'activo',
        'fecha_expiracion', 'creado_por',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_expiracion' => 'datetime',
    ];

    protected $appends = ['crypt_id'];

    public function getCryptIdAttribute()
    {
        return Encryptor::encrypt($this->id_token);
    }

    public static function generarToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function actividad()
    {
        return $this->belongsTo(ContenidoCurso::class, 'id_contenido_curso', 'id_contenido_curso');
    }
}
