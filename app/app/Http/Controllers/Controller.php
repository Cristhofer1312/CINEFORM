<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{

    use AuthorizesRequests,
        ValidatesRequests;
    protected $COUNTRY_DEFAULT = 238;
    public function __construct()
    {
        /*
          $this->middleware(function ($request, $next) {
          if (Auth::check() == true) {
          $GET_MODULE = session()->get('MODULE');
          // dd(!session()->get('MODULE') == null);
          if (!session()->get('MODULE') == null) {
          $module = \App\Helpers\Encryptor::decrypt($GET_MODULE);
          $getMenu = Auth::user()->getMenu($module);
          $MENU = [];
          foreach ($getMenu as $key => $value) {
          $menu = [];
          $menu['id'] = $value['id'];
          $menu['text'] = __($value['name']);
          $menu['icon'] = "fa fa-" . $value['icon'];
          $menu['submenu'] = [];
          foreach ($value['get_process'] as $key2 => $value2) {
          $menu['submenu'][$key2]['text'] = __($value2['name']);
          $menu['submenu'][$key2]['url'] = route($value2['route']);
          $menu['submenu'][$key2]['icon'] = "fa fa-" . $value2['icon'];
          }
          $MENU[] = $menu;
          }
          View::share('MENU', $MENU);
          }
          }
          return $next($request);
          });

         */
        $Countries = \Modules\Security\Entities\Countries::find($this->COUNTRY_DEFAULT);

        View::share('COUNTRY_DEFAULT', $Countries->crypt_id);
    }

    public function setJavascript($js = [])
    {
        if (is_array($js)) {
        }
    }
}
