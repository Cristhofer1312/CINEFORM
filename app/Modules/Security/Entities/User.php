<?php

namespace Modules\Security\Entities;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\EncryptationId;
use Modules\Comun\Entities\PersonalData;

class User extends Authenticatable
{

    use HasApiTokens,
        HasFactory,
        Notifiable,
        EncryptationId;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = "security.users";
    protected $fillable = [
        'username',
        'email',
        'password',
    ];
    public $timestamps = false;
    /* protected $appends = ['cell_phone', 'full_document']; */

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        /* 'password',
        'username', */
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // 'password' => 'hashed',
    ];

    public function getFullNameAttribute()
    {
        return $this->attributes['full_name'] ?? $this->personalData?->nombre_completo ?? '';
    }

    /**
     * Obtiene las iniciales del nombre completo del usuario.
     */
    public function getInitialsAttribute()
    {
        $name = $this->full_name;
        if (empty($name)) return 'U';
        
        $partes = explode(' ', trim($name));
        if (count($partes) >= 2) {
            return strtoupper($partes[0][0] . $partes[count($partes) - 1][0]);
        }
        return strtoupper(substr($name, 0, 2));
    }

    /**
     * Determina si el usuario tiene una foto de perfil personalizada.
     */
    public function hasPhoto()
    {
        $path = storage_path('app/public/img/avatars/' . $this->id . '.png');
        return \Illuminate\Support\Facades\File::exists($path);
    }

    public function getDocumentAttribute()
    {
        return $this->attributes['document'] ?? $this->personalData?->dni ?? '';
    }

    public function getPhoneAttribute()
    {
        return $this->attributes['phone'] ?? $this->personalData?->telefono ?? '';
    }

    public function getDocumentTypeIdAttribute()
    {
        return $this->attributes['document_type_id'] ?? $this->personalData?->tipo_dni ?? null;
    }

    public function getCountryIdAttribute()
    {
        return $this->attributes['country_id'] ?? $this->personalData?->id_pais ?? null;
    }

    public function getCellPhoneAttribute()
    {
        $country = $this->country;
        $phone = $this->phone;
        return ($country && $country->dial_code) ? ($country->dial_code . ' ' . $phone) : ($phone ?? null);
    }
    public function getFullDocumentAttribute()
    {
        $docType = $this->documentType;
        $doc = $this->document;
        return ($docType && $docType->code) ? ($docType->code . '-' . $doc) : ($doc ?? null);
    }

    public function getProfile()
    {
        return $this->profile();
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function country()
    {
        return $this->belongsTo(Countries::class, 'country_id');
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function getProfileIdAttribute()
    {
        return $this->attributes['profile_id'] ?? session()->get('profile_id') ?? $this->perfiles()->first()?->id ?? null;
    }

    public function perfiles()
    {
        return $this->belongsToMany(
            \Modules\Security\Entities\Profile::class,
            'security.profiles_users',  // Nombre tabla pivote
            'id_users',                 // FK usuario en pivote
            'id_rol'                    // FK perfil en pivote
        )->withPivot('status', 'creado_por', 'creado_en', 'actualizado_por', 'actualizado_en');
    }

    public function getPerfilesArray()
    {
        return $this->perfiles->pluck('id')->toArray();
    }

    public function profiles()
    {
        return $this->hasMany(ProfileUser::class, 'id_users');
    }

    public function personalData()
    {
        return $this->hasOne(\Modules\Comun\Entities\PersonalData::class, 'user_id', 'id');
    }

    public function getIdPersonaAttribute()
    {
        return $this->personalData?->id_persona;
    }

    // ========================================================================
    // Lógica para Sidebar Simplificado (Sin selección previa de módulo)
    // ========================================================================
    public function captureMenu()
    {
        $profileId = session()->get('profile_id');
        if (!$profileId) return [];

        $Menu = \Modules\Security\Entities\Menu::orderBy('order')
            ->where('active', true)
            ->with(['processes' => function($q) {
                $q->where('active', true)->orderBy('order');
            }])
            ->get()
            ->toArray();

        foreach ($Menu as $key => $value) {
            foreach ($value['processes'] as $key2 => $value2) {
                $allowedProfiles = $value2['profile_array'] ?? [];
                if (!in_array($profileId, $allowedProfiles)) {
                    unset($Menu[$key]['processes'][$key2]);
                }
            }
            if (count($Menu[$key]['processes']) == 0) {
                unset($Menu[$key]);
            }
        }
        return $Menu;
    }

    public function capturePerfil()
    {
        if (session()->get('profile_id')) {
            return $this->perfiles->toArray();
        } else {
            return [];
        }
    }

    public function getShortNameAttribute()
    {
        $nameParts = explode(' ', $this->full_name);
        return ucwords(strtolower(implode(' ', array_slice($nameParts, 0, 2))));
    }

    public function verifyPermission($route = null)
    {
        $profileId = session()->get('profile_id');
        if (!$profileId) return false;

        $position_point = strpos($route, '.');
        if ($position_point === false) {
            $routeFather = $route;
            $routeSon = "";
        } else {
            $nameRoute = explode(".", $route);
            $routeFather = $nameRoute[1];
            $routeSon = $nameRoute[0];
        }

        $query = Process::join('security.permissions', 'security.permissions.process_id', '=', 'security.processes.id')
            ->join('security.profile_permissions', 'security.profile_permissions.permission_id', '=', 'security.permissions.id')
            ->where('security.processes.route', $routeFather)
            ->where('security.profile_permissions.profile_id', $profileId);

        if (!empty($routeSon)) {
            $query->where('security.permissions.slug', $routeSon);
        }

        return $query->exists();
    }
}
