<?php

namespace Modules\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckIntranet
{
    /**
     * Subredes de intranet permitidas en notación CIDR.
     * Ajustar según la infraestructura de red real:
     *   10.11.x.x  → Torre
     *   10.13.x.x  → Maiquetía
     *   10.15.x.x  → IUAC
     *   192.168.x.x → WiFi corporativo
     */
    private array $subredes = [
        '10.11.0.0/16',
        '10.13.0.0/16',
        '10.15.0.0/16',
        '192.168.0.0/16',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();

        foreach ($this->subredes as $cidr) {
            if ($this->ipEnSubred($ip, $cidr)) {
                return $next($request);
            }
        }

        return abort(401, 'Acceso restringido a la red intranet corporativa.');
    }

    /**
     * Verifica si una IP pertenece a una subred CIDR.
     */
    private function ipEnSubred(string $ip, string $cidr): bool
    {
        [$subred, $bits] = explode('/', $cidr);

        $ipLong     = ip2long($ip);
        $subnetLong = ip2long($subred);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $mascara = $bits == 0 ? 0 : (~0 << (32 - (int)$bits));

        return ($ipLong & $mascara) === ($subnetLong & $mascara);
    }
}
