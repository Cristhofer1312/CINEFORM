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
        $country = $this->getCountry;
        $phone = $this->phone;
        return ($country && $country->dial_code) ? ($country->dial_code . ' ' . $phone) : ($phone ?? null);
    }
    public function getFullDocumentAttribute()
    {
        $docType = $this->getDocumentType;
        $doc = $this->document;
        return ($docType && $docType->code) ? ($docType->code . '-' . $doc) : ($doc ?? null);
    }

    function getProfile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    function getCountry()
    {
        return $this->belongsTo(Countries::class, 'country_id');
    }

    /*   
      function getOffices() {
          return $this->belongsToMany(\Modules\Library\Entities\Offices::class, 'library_office_users', 'user_id', 'office_id');
      }
       */
    public function getDocumentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function getProfileIdAttribute()
    {
        return $this->attributes['profile_id'] ?? session()->get('profile_id') ?? $this->getPerfiles()->first()?->id ?? null;
    }

    public function getPerfiles()
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
        $perfiles = $this->getPerfiles; // obtiene los perfiles relacionados
        $resultado = [];

        foreach ($perfiles as $perfil) {
            $resultado[] = $perfil->id;
        }

        return $resultado;
    }

    public function getProfiles()
    {
        return $this->hasMany(ProfileUser::class, 'id_users');
    }

    public function getPersona()
    {
        return $this->hasOne(\Modules\Registro\Entities\Personas::class, 'user_id', 'id'); // Ajusta foreign key si es necesario
    }

    public function personalData()
    {
        return $this->hasOne(\Modules\Comun\Entities\PersonalData::class, 'user_id', 'id');
    }

    public function getIdPersonaAttribute()
    {
        return $this->getPersona?->id_persona;
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
            ->with(['getProcess' => function($q) {
                $q->where('active', true)->orderBy('order');
            }])
            ->get()
            ->toArray();

        foreach ($Menu as $key => $value) {
            foreach ($value['get_process'] as $key2 => $value2) {
                $allowedProfiles = $value2['profile_array'] ?? [];
                if (!in_array($profileId, $allowedProfiles)) {
                    unset($Menu[$key]['get_process'][$key2]);
                }
            }
            if (count($Menu[$key]['get_process']) == 0) {
                unset($Menu[$key]);
            }
        }
        return $Menu;
    }

    // ========================================================================
    // INICIO CÓDIGO COMENTADO — Lógica de Módulos (rollback si es necesario)
    // Fecha: 2026-04-24 | Motivo: Simplificación del sidebar
    // ========================================================================
    /*
    public function getModules()
    {
        $Modules = Modulo::join('security.menus', 'security.menus.module_id', '=', 'security.modules.id')
            ->join('security.processes', 'security.processes.menu_id', '=', 'security.menus.id')
            ->join('security.permissions', 'security.permissions.process_id', '=', 'security.processes.id')
            ->join('security.profile_permissions', 'security.profile_permissions.permission_id', '=', 'security.permissions.id')
            ->where('security.profile_permissions.profile_id', session()->get('profile_id'))
            ->groupBy("security.modules.id", "security.modules.name", "security.modules.description", "security.modules.icon", "security.modules.order")
            ->select('security.modules.*')
            ->get();
        return $Modules;
    }

    public function getProcesses()
    {
        $Processes = Process::join('security.permissions', 'security.permissions.process_id', '=', 'security.processes.id')
            ->join('security.profile_permissions', 'security.profile_permissions.permission_id', '=', 'security.permissions.id')
            ->where('security.profile_permissions.profile_id', session()->get('profile_id'))
            ->groupBy("security.processes.id", "security.processes.name", "security.processes.description", "security.processes.icon", "security.processes.route", "security.processes.order")
            ->select('security.processes.*')
            ->get();
        return $Processes;
    }

    public function captureMenu()
    {

        if (!session()->get('MODULE') == null) {
            return $this->getMenu(session()->get('MODULE'));
        } else {
            return [];
        }
    }

    public function capturePerfil()
    {
        if (session()->get('MODULE') == null) {
            return $this->getPerfiles->toArray();
        } else {
            return [];
        }
    }

    public function getMenu($Module)
    {
        $Menu = Menu::orderBy('order')->where("module_id", $Module)->with('getProcess')->get()->toArray();
        foreach ($Menu as $key => $value) {
            foreach ($value['get_process'] as $key2 => $value2) {
                if (!in_array(session()->get('profile_id'), $value2['profile_array'])) {
                    unset($Menu[$key]['get_process'][$key2]);
                    unset($Menu[$key]['process'][$key2]);
                }
            }
            if (count($Menu[$key]['get_process']) == 0) {
                unset($Menu[$key]);
            }
        }
        return $Menu;
    }

    public function getShortNameAttribute()
    {
        if ($this->document_type->is_natural === false) {
            $name = ucwords(Lower($this->full_name));
        } else {
            $nameParts = explode(' ', $this->full_name);
            $name = ucwords(Lower(implode(' ', array_slice($nameParts, 0, 2))));
        }
        return $name;
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
    */
}
