<?php

namespace Modules\Taller\Entities;

use App\Enums\EstadoCertificado;
use App\Enums\EstadoCurso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

use App\Helpers\Encryptor;
use Modules\Taller\Entities\ObservacionCurso;

class Curso extends Model
{
    use HasFactory;

    /**
     * Estado del certificado ya calculado, para no releer el disco en cada uso.
     *
     * @var EstadoCertificado|null
     */
    private $estadoCertificadoCache = null;

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
        'direccion_corta',
        'es_nacional',
        'imagen',
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
     * Obtener los valores posibles para el campo status (filtro de listados).
     *
     * Nota: BORRADOR (10) no se incluye porque el wizard actual no crea filas
     * en BD hasta finalizar(); vive solo en sesión + temporales en disco.
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
            ->withPivot(['created_at', 'motivo'])
            ->orderByDesc('taller.curso_estado.created_at')
            ->orderByDesc('taller.curso_estado.id');
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

    public function resenasSatisfaccion()
    {
        return $this->hasMany(ResenaSatisfaccion::class, 'id_curso', 'id_curso');
    }

    /**
     * Get all inscripciones for the curso.
     */
    public function inscripciones()
    {
        return $this->hasMany(\Modules\Taller\Entities\Inscripcion::class, 'id_curso', 'id_curso');
    }

    /**
     * Get only active inscripciones for the curso.
     */
    public function inscripcionesActivas()
    {
        return $this->hasMany(\Modules\Taller\Entities\Inscripcion::class, 'id_curso', 'id_curso')
            ->where('estado', \Modules\Taller\Entities\Inscripcion::ESTADO_APROBADO);
    }

    /**
     * Get the requirements for the curso.
     */
    public function requisitos()
    {
        return $this->hasMany(CursoRequisito::class, 'id_curso', 'id_curso')->orderBy('id_requisito', 'asc');
    }

    /**
     * Estado de la configuración del certificado de este curso.
     *
     * Es un valor derivado de los archivos en disco, no un estado del ciclo de
     * vida del curso: no se guarda en la base de datos ni aparece en el historial
     * de taller.curso_estado. Es la única fuente para saber si el certificado
     * está completo, tanto la ficha del curso como el aviso de creación lo
     * consultan aquí en lugar de recorrer el directorio por su cuenta.
     *
     * Se aplica el mismo criterio que usa CertificadoService al emitir el PDF:
     * la plantilla global cuenta como fondo válido y la firma se busca probando
     * las extensiones admitidas.
     *
     * @return EstadoCertificado
     */
    public function estadoCertificado(): EstadoCertificado
    {
        if ($this->estadoCertificadoCache !== null) {
            return $this->estadoCertificadoCache;
        }

        $base = storage_path('app/public/Certificados');

        $hayPlantilla = \Modules\Taller\Services\CertificadoConfig::rutaPlantilla($this->id_curso) !== null;

        if (!$hayPlantilla) {
            return $this->estadoCertificadoCache = EstadoCertificado::SIN_PLANTILLA;
        }

        foreach (['png', 'jpg', 'jpeg'] as $ext) {
            if (is_file($base . '/cursos/' . $this->id_curso . '_firma.' . $ext)) {
                return $this->estadoCertificadoCache = EstadoCertificado::CONFIGURADO;
            }
        }

        return $this->estadoCertificadoCache = EstadoCertificado::FALTA_FIRMA;
    }

    /**
     * Acceso al estado del certificado como atributo: $curso->estado_certificado
     *
     * @return EstadoCertificado
     */
    public function getEstadoCertificadoAttribute(): EstadoCertificado
    {
        return $this->estadoCertificado();
    }

    /**
     * Actualiza el estado del curso
     *
     * @param int $idEstado
     * @param string|null $motivo
     * @return $this
     */
    public function agregarEstado($idEstado, ?string $motivo = null)
    {
        // Verificar si el estado existe
        $estado = \Modules\Taller\Entities\Estado::findOrFail($idEstado);

        // Validar la transición contra el estado actual (defensa en profundidad:
        // los controladores ya la validan, pero nadie debe poder saltar estados).
        $origen = EstadoCurso::tryFrom((int) ($this->estado_actual->id_estado ?? 0));
        $destino = EstadoCurso::tryFrom((int) $idEstado);

        if ($origen !== null && $destino !== null && !$origen->puedeTransicionarA($destino)) {
            throw new \InvalidArgumentException(
                'Transición no permitida de "' . $origen->label() . '" a "' . $destino->label() . '".'
            );
        }

        // 1. Registrar en el historial (taller.curso_estado)
        DB::table('taller.curso_estado')->insert([
            'id_curso'   => $this->id_curso,
            'id_estado'  => $idEstado,
            'motivo'     => $motivo,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Refrescar la relación para que estado_actual devuelva el nuevo estado
        $this->unsetRelation('estados');

        return $this;
    }

}
