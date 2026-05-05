<?php

namespace Modules\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App;

class CheckIntranet {

    private $ipsIntranet = ['10', '192'];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        /*
         * 10.11 Torre
         * 10.13 Maiquetia
         * 10.15 IUAC
         * 192.168 WIFI
         */

       
//        return $next($request);
        $rang = explode('.', $request->ip());
        if (in_array($rang[0], $this->ipsIntranet)) {
            return $next($request);
        }
         return abort(401);
    }
}
