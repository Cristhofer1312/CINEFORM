<?php

namespace Modules\Taller\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Helpers\Encryptor;

class ContenidoCurso extends Model
{
    use HasFactory;

    protected $table = 'taller.contenido_cursos';
    protected $primaryKey = 'id_contenido_curso';

    // Deshabilitar los timestamps de Laravel ya que usamos columnas personalizadas
    public $timestamps = false;

    protected $fillable = [
        'id_curso',
        'titulo',
        'descripcion_breve',
        'descripcion',
        'url_contenido',
        'fecha_contenido',
        'es_evaluacion',
        'id_tipo_evaluacion',
        'ponderacion',
        'creado_por',
        'creado_en',
        'actualizado_por',
        'actualizado_en'
    ];

    protected $casts = [
        'fecha_contenido' => 'date',
    ];

    // Nombres de las columnas de timestamp personalizadas
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    // Campos que no existen en la base de datos
    protected $appends = ['crypt_id'];

    public function getCryptIdAttribute()
    {
        return Encryptor::encrypt($this->id_contenido_curso);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso', 'id_curso');
    }

    public function tipoEvaluacion()
    {
        return $this->belongsTo(TipoEvaluacion::class, 'id_tipo_evaluacion', 'id_tipo_evaluacion');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'id_contenido_curso');
    }

    public function asistenciaTokens()
    {
        return $this->hasMany(AsistenciaToken::class, 'id_contenido_curso');
    }

    public function esDiaDeClases(): bool
    {
        return !$this->es_evaluacion;
    }

    public function tokenActivo()
    {
        return $this->asistenciaTokens()
            ->where('activo', true)
            ->where(function ($q) {
                $q->whereNull('fecha_expiracion')
                  ->orWhere('fecha_expiracion', '>', now());
            })
            ->latest('creado_en')
            ->first();
    }
}
