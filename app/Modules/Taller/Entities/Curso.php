<?php

namespace Modules\Taller\Entities;

use App\Enums\EstadoCurso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

use App\Helpers\Encryptor;
use Modules\Taller\Entities\ObservacionCurso;

class Curso extends Model
{
    use HasFactory;
    public function getCryptIdAttribute()
    {

        return Encryptor::encrypt($this->id_curso);
    }

    protected $appends = ['crypt_id'];

    protected $table = 'taller.cursos';
    protected $primaryKey = 'id_curso';

    public $timestamps = true;
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_curso',
        'codigo',
        'nombre',
        'id_modalidad',
        'id_actividad_formativa',
        'id_aspecto',
        'id_modalidad_especial',
        'id_estado',
        'id_persona',
        'descripcion',
        'nivel',
        'trimestre',
        'correlativo',
        'anio',
        'duracion',
        'horas',
        'cantidad_cupos',
        'telegram',
        'es_nacional',
        'creado_por',
        'actualizado_por',
        'fecha_inicio',
        'fecha_fin'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'es_nacional' => 'boolean',
        'status' => EstadoCurso::class
    ];

    /**
     * Obtener los valores posibles para el campo status
     *
     * @return array
     */
    public static function getStatuses()
    {
        return [
            EstadoCurso::POR_ACEPTAR->value => EstadoCurso::POR_ACEPTAR->label(),
            EstadoCurso::INSCRIPCION->value => EstadoCurso::INSCRIPCION->label(),
            EstadoCurso::EN_CURSO->value => EstadoCurso::EN_CURSO->label(),
            EstadoCurso::FINALIZADO->value => EstadoCurso::FINALIZADO->label(),
            EstadoCurso::CERRADO->value => EstadoCurso::CERRADO->label(),
        ];
    }

    public function persona()
    {
        return $this->belongsTo(\Modules\Comun\Entities\PersonalData::class, 'id_persona', 'id_persona');
    }

    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class, 'id_modalidad', 'id_modalidad');
    }

    public function actividadFormativa()
    {
        return $this->belongsTo(ActividadFormativa::class, 'id_actividad_formativa', 'id_actividad_formativa');
    }

    public function aspecto()
    {
        return $this->belongsTo(Aspecto::class, 'id_aspecto', 'id_aspecto');
    }

    /**
     * Relación con las múltiples localidades (Estados) a las que pertenece el curso
     */
    public function localidades()
    {
        return $this->belongsToMany(\Modules\Parametros\Entities\Estados::class, 'taller.curso_localidades', 'id_curso', 'id_estado');
    }

    public function modalidadEspecial()
    {
        return $this->belongsTo(ModalidadEspecial::class, 'id_modalidad_especial', 'id_modalidad_especial');
    }

    public function region()
    {
        return $this->belongsTo(\Modules\Parametros\Entities\Estados::class, 'id_estado', 'id_estado');
    }

    /**
     * Get all estados for the curso (Historial de estados).
     */
    public function estados()
    {
        return $this->belongsToMany(Estado::class, 'taller.curso_estado', 'id_curso', 'id_estado')
            ->withPivot('created_at')
            ->orderBy('taller.curso_estado.created_at', 'desc');
    }

    /**
     * Get the current estado of the curso.
     */
    public function estadoActual()
    {
        return $this->estados()->take(1);
    }

    /**
     * Get the current estado attribute.
     */
    public function getEstadoActualAttribute()
    {
        if ($this->relationLoaded('estados')) {
            return $this->estados->first();
        }
        return $this->estadoActual()->first();
    }

    /**
     * Get the current status of the curso.
     */
    public function getStatusAttribute()
    {
        return $this->estado_actual;
    }

    /**
     * Historial de observaciones/rechazos registradas por la coordinación.
     */
    public function observaciones()
    {
        return $this->hasMany(ObservacionCurso::class, 'id_curso', 'id_curso')
            ->orderBy('created_at', 'desc');
    }

    public function contenidos()
    {
        return $this->hasMany(ContenidoCurso::class, 'id_curso');
    }

    /**
     * Get all inscripciones for the curso.
     */
    public function inscripciones()
    {
        return $this->hasMany(\Modules\Taller\Entities\Inscripcion::class, 'id_curso', 'id_curso');
    }

    /**
     * Actualiza el estado del curso
     *
     * @param int $idEstado
     * @param string|null $motivo
     * @return $this
     */
    public function agregarEstado($idEstado)
    {
        // Verificar si el estado existe
        $estado = \Modules\Taller\Entities\Estado::findOrFail($idEstado);

        // 1. Registrar en el historial (taller.curso_estado)
        \Illuminate\Support\Facades\DB::table('taller.curso_estado')->insert([
            'id_curso'   => $this->id_curso,
            'id_estado'  => $idEstado,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 2. Sincronizar el estado actual en la tabla principal de cursos
        $this->id_estado = $idEstado;
        $this->save();

        return $this;
    }

}
