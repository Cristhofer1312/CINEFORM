<?php

namespace Modules\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App;


class CheckSecurity {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {

        if (session()->get('language') != null) {
            /*
             * Establece el locale de Laravel
             */
            App::setLocale(session()->get('language'));
            //setlocale(LC_TIME, config('locale.languages')[session()->get('language')][1]);
            //Carbon::setLocale(config('locale.languages')[session()->get('language')][0]);
        }

        $ruta = explode('.', \Request::route()->getName()) ;
        if ($ruta[0]=='users' && Auth::user()->id != 1){
            abort(404);
        }


        return $next($request);
    }
}
