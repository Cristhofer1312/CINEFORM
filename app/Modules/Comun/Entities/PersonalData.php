<?php

namespace Modules\Comun\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Security\Entities\User;

class PersonalData extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tipo_dni',
        'dni',
        'pasaporte',
        'rif',
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
        'creado_en'
    ];

    protected $table = "comun.personas";
    protected $primaryKey = "id_persona";
    public $timestamps = false;

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getNombreCompletoAttribute()
    {
        return trim(implode(' ', array_filter([
            $this->primer_nombre,
            $this->primer_apellido,
        ])));
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
     * Facilita el crypt_id para renderAvatar.
     */
    public function getCryptIdAttribute()
    {
        return $this->user?->crypt_id;
    }

    protected static function newFactory()
    {

        return \Modules\Comun\Database\factories\PersonalDataFactory::new();
    }
    public function securityUser()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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
}
