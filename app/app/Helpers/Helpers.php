<?php



if (!function_exists('Upper')) {

    function Upper($text)
    {
        return mb_strtoupper($text, 'UTF8');
    }

}
if (!function_exists('showActions')) {

    function showActions($text, $name, $actions)
    {
        $text = trim($text);
        if ($text == "" || $text == "*") {
            return "";
        }
        $options = array_unique(array_filter(explode("|", $text)));
        $html = '<ul class="list-group p-0" style="margin-top:0.5rem; gap: 0.5rem;">';
        foreach ($options as $value) {
            $checked = in_array($value, $actions) ? 'checked="checked"' : '';
            
            // Buscar etiquetas amigables y descripciones usando el nuevo mapeo RBAC
            $idAction = \App\Constants\SecurityAction::stringToId($value);
            $friendlyName = $value;
            $descriptionLabel = '';
            
            if ($idAction !== 0) {
                $labels = \App\Constants\SecurityAction::labels();
                $descriptions = \App\Constants\SecurityAction::descriptions();
                
                $friendlyName = $labels[$idAction] ?? $value;
                $desc = $descriptions[$idAction] ?? '';
                if ($desc) {
                    $descriptionLabel = '<span class="d-block text-muted mt-1" style="font-size:0.8rem; line-height: 1.2;">'.$desc.'</span>';
                }
            } else {
                $friendlyName = ucwords(str_replace("_", " ", $value));
            }

            // Determinar clases extra para opciones marcadas vs desmarcadas para que sea más vistoso
            $bordeExtra = in_array($value, $actions) ? 'border-primary border-2 shadow-sm' : 'border-primary border-opacity-50';

            $html .= '<li class="list-group-item d-flex align-items-start border rounded px-3 py-2 mb-2 bg-white ' . $bordeExtra . '"> 
                        <div class="form-check p-0 m-0 w-100 d-flex">
                            <input ' . $checked . ' name="permissions[' . $name . '][' . $value . ']" class="form-check-input mt-1 me-2 pointer border-primary" type="checkbox" id="chk_'.$name.'_'.$value.'" /> 
                            <div class="flex-grow-1">
                                <label class="form-check-label fw-bold m-0 text-dark" style="cursor:pointer;" for="chk_'.$name.'_'.$value.'">' . $friendlyName . "</label>
                                " . $descriptionLabel . "
                            </div>
                        </div>
                      </li>";
        }
        $html .= "</ul>";
        return $html;

    }

}
if (!function_exists('Abc')) {

    // Fix #6: Eliminado dd() de depuración que bloqueaba la app en producción
    function Abc($text)
    {
        return ucfirst($text);
    }

}

if (!function_exists('Lower')) {

    function Lower($text)
    {
        return mb_strtolower($text, 'UTF8');
    }

}

if (!function_exists('CamelCase')) {
    function CamelCase($text)
    {
        return ucfirst(Lower($text));
    }
}

if (!function_exists('setLabel')) {

    function setLabel($text)
    {
        return str_replace(" ", "_", mb_strtolower($text, 'UTF8'));
    }

}

if (!function_exists('removeLabel')) {

    function removeLabel($text)
    {
        return str_replace("_", " ", mb_strtolower($text, 'UTF8'));
    }

}


if (!function_exists('showFloat')) {

    function showFloat($monto, $dec = 2)
    {//se7ho
        return number_format($monto, $dec, ',', '.');
    }

}

if (!function_exists('saveFloat')) {

    function saveFloat($monto)
    {//se7ho
        return str_replace(',', '.', str_replace('.', '', $monto));
    }

}

if (!function_exists('saveDate')) {

    function saveDate($date, $separador = "/")
    {
        $date = explode($separador, $date);
        return $date[2] . '-' . $date[1] . '-' . $date[0];
    }

}



if (!function_exists('showDate')) {

    function showDate($date)
    {
        $date = explode("-", $date);
        return $date[2] . '/' . $date[1] . "/" . $date[0];
    }

}
if (!function_exists('showDateFull')) {

    function showDateFull($date_full)
    {
        $date = explode(" ", $date_full);
        $time = $date[1];
        $date = explode("-", $date[0]);


        return $date[2] . '/' . $date[1] . "/" . $date[0] . ' ' . $time;
    }

}
if (!function_exists('showNamePng')) {

    function showNamePng($name)
    {
        $name = explode(".", $name);
        return Upper($name[0]);
    }

}
if (!function_exists('showPhone')) {

    function showPhone($num)
    {
        $num1 = Str::replace('-', '', Str::replace(' ', '', Str::replace('.', '', trim($num))));
        if (strlen($num1) == 11) {
            $num1 = substr($num1, 0, 4) . '-' . substr($num1, 4, 7);
        }
        return $num1;
    }

}




if (!function_exists('checkCta')) {

    function checkCta($banco, $oficina, $digitos, $num_cuenta)
    {
        $pesos1 = array(3, 2, 7, 6, 5, 4, 3, 2);
        $pesos2 = array(3, 2, 7, 6, 5, 4, 3, 2, 7, 6, 5, 4, 3, 2);
        $cuenta = $banco . $oficina . $digitos . $num_cuenta;
        if (strlen($cuenta) != 20) {
            return false;
        }
        $campos1 = $banco . $oficina;
        $campos2 = $oficina . $num_cuenta;
        $digitos1 = (int) $campos1;
        $digitos2 = (int) $campos2;
        $suma1 = 0;
        $suma2 = 0;
        for ($i = 0; $i < 8; $i++) {
            $digito = (int) (($digitos1 / pow(10.0, (7 - $i) * 1.0))) % 10;
            $suma1 += $pesos1[$i] * $digito;
        }
        for ($i = 0; $i < 14; $i++) {
            $digito = (int) (($digitos2 / pow(10.0, (13 - $i) * 1.0))) % 10;
            $suma2 += $pesos2[$i] * $digito;
        }
        $digito1 = (11 - ($suma1 % 11));
        $digito2 = (11 - ($suma2 % 11));
        if ($digito1 >= 10 || $digito1 < 1) {
            $digito1 = $digito1 % 10;
        }
        if ($digito2 >= 10 || $digito2 < 1) {
            $digito2 = $digito2 % 10;
        }
        //echo $digito1 . '-' . $digito2;
        $cuentaValidada = $banco . $oficina . $digito1 . $digito2 . $num_cuenta;
        return $cuenta == $cuentaValidada;
    }

}

// Fix #5: Declaración duplicada de showActions eliminada.
// La versión activa (con 3 parámetros) está definida en la línea 13 de este archivo.

if (!function_exists('hasPermission')) {
    function hasPermission($processId, int $actionId): bool
    {
        $profileId = session()->get('profile_id');
        if (!$profileId) return false;

        $slugToCheck = \App\Constants\SecurityAction::dbString($actionId);
        if (!$slugToCheck) return false;

        return \Illuminate\Support\Facades\DB::table('security.permissions')
            ->join('security.profile_permissions', 'security.profile_permissions.permission_id', '=', 'security.permissions.id')
            ->where('security.permissions.process_id', $processId)
            ->where('security.profile_permissions.profile_id', $profileId)
            ->where('security.permissions.slug', $slugToCheck)
            ->exists();
    }
}

if (!function_exists('hasPermissionRoute')) {
    function hasPermissionRoute(string $routeSlug, int $actionId): bool
    {
        $profileId = session()->get('profile_id');
        if (!$profileId) return false;

        $slugToCheck = \App\Constants\SecurityAction::dbString($actionId);
        if (!$slugToCheck) return false;

        return \Illuminate\Support\Facades\DB::table('security.processes')
            ->join('security.permissions', 'security.permissions.process_id', '=', 'security.processes.id')
            ->join('security.profile_permissions', 'security.profile_permissions.permission_id', '=', 'security.permissions.id')
            ->where('security.processes.route', $routeSlug)
            ->where('security.profile_permissions.profile_id', $profileId)
            ->where('security.permissions.slug', $slugToCheck)
            ->exists();
    }
}


if (!function_exists('renderAvatar')) {
    /**
     * Renderiza el avatar del usuario (Imagen o Iniciales)
     * 
     * @param mixed $user Instancia de Usuario o Persona
     * @param string $size Clase de tamaño (avatar-xs, avatar-sm, avatar-md, avatar-lg, avatar-xl)
     * @param string $extraClasses Clases adicionales para el contenedor o imagen
     */
    function renderAvatar($user, $size = 'avatar-sm', $extraClasses = '') {
        if (!$user) return '';

        // Si es una instancia de Persona, intentamos obtener el usuario asociado si es necesario
        // pero asumimos que el objeto pasado tiene hasPhoto() e initials (como el modelo User que actualizamos)
        
        $fullName = $user->full_name ?? 'Usuario';
        $initials = $user->initials ?? 'U';
        $hasPhoto = method_exists($user, 'hasPhoto') ? $user->hasPhoto() : false;

        if ($hasPhoto) {
            $cryptId = $user->crypt_id ?? null;
            $url = $cryptId ? route('show_avatar', $cryptId) : '#';
            
            // Calculamos un tamaño base aproximado si las clases fallan
            $width = match($size) {
                'avatar-xs' => '24px',
                'avatar-sm' => '36px',
                'avatar-md' => '48px',
                'avatar-lg' => '60px',
                'avatar-xl' => '80px',
                'avatar-xxl' => '120px',
                default => '40px'
            };

            return '<div class="avatar ' . $size . ' ' . $extraClasses . ' rounded-circle" style="width:' . $width . '; height:' . $width . '; border-radius: 50%; background: transparent;">
                        <img src="' . $url . '" alt="' . $fullName . '" class="avatar-img rounded-circle" style="width:100%; height:100%; object-fit:cover; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 6px 15px rgba(0,0,0,0.12);">
                    </div>';
        } else {
            // Estilo de iniciales estandarizado
            $width = match($size) {
                'avatar-xs' => '24px',
                'avatar-sm' => '36px',
                'avatar-md' => '48px',
                'avatar-lg' => '60px',
                'avatar-xl' => '80px',
                'avatar-xxl' => '120px',
                default => '40px'
            };
            $fontSize = match($size) {
                'avatar-xs' => '0.6rem',
                'avatar-sm' => '0.8rem',
                'avatar-md' => '1rem',
                'avatar-lg' => '1.2rem',
                'avatar-xl' => '1.5rem',
                'avatar-xxl' => '2rem',
                default => '1rem'
            };

            return '<div class="avatar ' . $size . ' ' . $extraClasses . ' rounded-circle" style="width:' . $width . '; height:' . $width . '; border-radius: 50%; background: transparent;">
                        <div class="avatar-title rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width:100%; height:100%; border-radius: 50%; font-size:' . $fontSize . '; background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); border: 3px solid #fff; box-shadow: 0 6px 15px rgba(0,0,0,0.12); letter-spacing: 1px;">' . $initials . '</div>
                    </div>';
        }
    }
}
