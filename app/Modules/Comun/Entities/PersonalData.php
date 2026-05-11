<?php

namespace Modules\Comun\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Security\Entities\User;
use App\Helpers\Encryptor;

class PersonalData extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tipo_dni',
        'dni',
        'pasaporte',
        'rif',
        'reg_nac_cine',
        'genero',
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'telefono',
        'telefono_opcional',
        'id_pais',
        'id_estado',
        'id_municipio',
        'id_parroquia',
        'direccion',
        'creado_por',
        'creado_en',
        'actualizado_por',
        'actualizado_en'
    ];

    protected $table = "comun.personas";
    protected $primaryKey = "id_persona";
    public $timestamps = false;
    protected $appends = ['crypt_id'];

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getNombreCompletoAttribute()
    {
        return trim("{$this->primer_nombre} {$this->segundo_nombre} {$this->primer_apellido} {$this->segundo_apellido}");
    }

    /**
     * Obtiene las iniciales del nombre completo.
     */
    public function getInitialsAttribute()
    {
        $partes = explode(' ', trim($this->nombre_completo));
        if (count($partes) >= 2) {
            return strtoupper($partes[0][0] . $partes[count($partes) - 1][0]);
        }
        return strtoupper(substr($this->nombre_completo, 0, 2));
    }

    /**
     * Determina si tiene foto de perfil (usando la relación usuario).
     */
    public function hasPhoto()
    {
        if (!$this->user_id) return false;
        $path = storage_path('app/public/img/avatars/' . $this->user_id . '.png');
        return \Illuminate\Support\Facades\File::exists($path);
    }

    /**
     * Facilita el crypt_id basado en id_persona.
     */
    public function getCryptIdAttribute()
    {
        return Encryptor::encrypt($this->id_persona);
    }

    /**
     * Retorna el crypt_id del usuario para compatibilidad de avatar.
     */
    public function getUserCryptIdAttribute()
    {
        return $this->user?->crypt_id;
    }

    protected static function newFactory()
    {
        return \Modules\Comun\Database\factories\PersonalDataFactory::new();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function securityUser()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getCellPhoneAttribute()
    {
        return ($this->pais?->dial_code ?? '') . ' ' . $this->telefono;
    }

    public function getFullDocumentAttribute()
    {
        return ($this->tipoDni?->code ?? '') . '-' . $this->dni;
    }

    public function tipoDni()
    {
        return $this->belongsTo(\Modules\Security\Entities\DocumentType::class, 'tipo_dni');
    }

    public function generoRef()
    {
        return $this->belongsTo(\Modules\Security\Entities\Genders::class, 'genero');
    }

    public function pais()
    {
        return $this->belongsTo(\Modules\Security\Entities\Countries::class, 'id_pais');
    }

    public function estado()
    {
        return $this->belongsTo(\Modules\Parametros\Entities\Estados::class, 'id_estado');
    }

    public function municipio()
    {
        return $this->belongsTo(\Modules\Parametros\Entities\Municipios::class, 'id_municipio');
    }

    public function parroquia()
    {
        return $this->belongsTo(\Modules\Parametros\Entities\Parroquias::class, 'id_parroquia');
    }

    public function cursos()
    {
        return $this->hasMany(\Modules\Taller\Entities\Curso::class, 'id_persona', 'id_persona');
    }

    public function especializaciones()
    {
        return $this->belongsToMany(Especializacion::class, 'comun.personas_especializacion', 'id_persona', 'id_especializacion')
            ->withPivot('anos_experiencia');
    }

    // Búsquedas y consultas rápidas
    public static function buscarPorDocumento($tipo_dni, $dni)
    {
        return self::where('tipo_dni', $tipo_dni)->where('dni', $dni)->first();
    }

    public function scopeBuscarPorNombres($query, $primer_nombre = null, $segundo_nombre = null, $primer_apellido = null, $segundo_apellido = null)
    {
        return $query
            ->when($primer_nombre, function ($q) use ($primer_nombre) {
                $q->where('primer_nombre', 'like', "%{$primer_nombre}%");
            })
            ->when($segundo_nombre, function ($q) use ($segundo_nombre) {
                $q->where('segundo_nombre', 'like', "%{$segundo_nombre}%");
            })
            ->when($primer_apellido, function ($q) use ($primer_apellido) {
                $q->where('primer_apellido', 'like', "%{$primer_apellido}%");
            })
            ->when($segundo_apellido, function ($q) use ($segundo_apellido) {
                $q->where('segundo_apellido', 'like', "%{$segundo_apellido}%");
            });
    }
}
