<?php

namespace Modules\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App;


class SetLanguage {

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




        return $next($request);
    }
}
