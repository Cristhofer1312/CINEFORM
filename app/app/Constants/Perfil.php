<?php

namespace App\Constants;

/**
 * IDs de los perfiles base sembrados en `security.profiles` (ProfilesSeeder).
 * Evita números mágicos en código y seeders.
 */
class Perfil
{
    const ADMINISTRADOR = 1;
    const FACILITADOR = 2;
    const PARTICIPANTE = 3;
    const COORDINADOR = 4;
}
