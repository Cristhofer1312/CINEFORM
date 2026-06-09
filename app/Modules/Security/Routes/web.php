<?php

use Illuminate\Support\Facades\Route;

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

Route::get('language/{lang?}', function ($lang = 'en') {
    session()->put('language', $lang);

    return redirect()->back();
    //return redirect('/home');
    //return to_route('home');;
})->name('language');

Route::prefix('security')->group(function () {

    Route::get('pa', function () {

        // Ruta al archivo PDF
        $pdfFile = '8.pdf';
        // Número de página que deseas convertir (empezando desde 0)
        $pageNumber = 0; // Cambia esto al número de página deseada
        // Crear un objeto Imagick
        $imagick = new Imagick();

        // Leer la página específica del PDF
        $imagick->setResolution(300, 300); // Establecer la resolución (opcional)
        $imagick->readImage("{$pdfFile}[{$pageNumber}]");

        // Opcional: configurar el formato de salida
        $imagick->setImageFormat('png'); // Cambia 'png' a 'jpeg' o el formato que necesites
        // Guardar la imagen resultante
        $outputFile = '88.png';

        /*
          $fp = fopen('ddd.png', 'x');
          fwrite($fp, base64_decode($value));
          fclose($fp);
         */
        //header("Content-type: image/png");
        return '<img src="data:image/png;base64,' . base64_encode($imagick->getImageBlob()) . '">';
        //dd(base64_encode($imagick->getImageBlob()));
        //$imagick->writeImage($outputFile);
        // Limpiar recursos
        $imagick->clear();
        $imagick->destroy();

        return "La página $pageNumber del PDF ha sido convertida a imagen y guardada como $outputFile.";

        $fp_pdf = fopen('aa.pdf', 'rb');

        $img = new imagick(); // [0] can be used to set page number
        $img->setResolution(300, 300);
        //$img->readImageFile($fp_pdf, ["2"]);
        $img->readImageFile($fp_pdf, "2");
        $img->setImageFormat("png");
        $img->setImageCompression(imagick::COMPRESSION_JPEG);
        $img->setImageCompressionQuality(90);

        $img->setImageUnits(imagick::RESOLUTION_PIXELSPERINCH);

        //  $data = $img->getImageBlob();

        $img->writeImage("rttr.png");

        /*
          $parseador = new \Smalot\PdfParser\Parser();

          $nombreDocumento = "aa.pdf";
          $documento = $parseador->parseFile($nombreDocumento);

          $paginas = $documento->getPages();
          foreach ($paginas as $indice => $pagina) {
          printf("<h1>Página #%02d</h1>", $indice + 1);
          $texto = $pagina->getText();
          echo "<pre>";
          echo $texto;
          echo "</pre>";
          }
         */
    })->name('pas');
    
    
    Route::match(['get', 'post'], 'photo', [Modules\Security\Http\Controllers\SecurityController::class, 'photo'])->name('photo');
    
    Route::group(array('middleware' => array(\Modules\Security\Http\Middleware\SetLanguage::class)), function () {
        Route::match(['get', 'post'], 'login', [Modules\Security\Http\Controllers\SecurityController::class, 'login'])->name('login');
        Route::match(['get', 'post', 'put'], 'recovery/{token?}', [Modules\Security\Http\Controllers\SecurityController::class, 'recovery'])->name('recovery');        
    });
    Route::get('captcha/{seed?}', [Modules\Security\Http\Controllers\SecurityController::class, 'captcha'])->name('captcha');

    Route::group(array('middleware' => array('auth', \Modules\Security\Http\Middleware\CheckSecurity::class)), function () {
        //Route::get('/usuario/perfil/seleccionar', [Modules\Security\Http\Controllers\SecurityController::class, 'showProfileSelection'])->name('usuario.mostrarPerfilSeleccion');
        Route::get('/usuario/perfil/seleccionar', [Modules\Registro\Http\Controllers\RegistroController::class, 'home'])->name('registro.home');
        //Route::get('/usuario/perfil/seleccionar/{id_rol}', [Modules\Security\Http\Controllers\SecurityController::class, 'seleccionarPerfil'])->name('usuario.seleccionarPerfil');
        Route::match(['get', 'post'], '/usuario/set_perfil/{id_rol}', [Modules\Security\Http\Controllers\SecurityController::class, 'seleccionarPerfil'])->name('usuario.set_perfil');
        Route::get('/usuario/cambiar-perfil', [Modules\Security\Http\Controllers\SecurityController::class, 'showSwitchProfile'])->name('usuario.cambiar_perfil');

        Route::get('home', [Modules\Security\Http\Controllers\SecurityController::class, 'home'])->name('home');
        Route::get('logout', [Modules\Security\Http\Controllers\SecurityController::class, 'logout'])->name('logout');
        // COMENTADO 2026-04-24: Ruta de selección de módulos eliminada del sidebar
        // Route::get('set-module/{id}', [Modules\Security\Http\Controllers\SecurityController::class, 'set_module'])->name('set_module');

        Route::match(['get', 'post'], 'update-profile', [Modules\Security\Http\Controllers\SecurityController::class, 'update_profile'])->name('update_profile');

        Route::get('show-avatar/{img}', [Modules\Security\Http\Controllers\SecurityController::class, 'show_avatar'])->name('show_avatar');

        Route::get('show-pdf/{pdf}/{page?}', [Modules\Security\Http\Controllers\SecurityController::class, 'show_pdf'])->name('show_pdf');

        Route::get('profiles', [Modules\Security\Http\Controllers\ProfilesController::class, 'index'])->name('profiles');
        Route::get('profiles-list', [Modules\Security\Http\Controllers\ProfilesController::class, 'list'])->name('profiles.list');
        Route::match(['get', 'post'], 'profiles-create', [Modules\Security\Http\Controllers\ProfilesController::class, 'create'])->name('profiles.create');
        Route::match(['get', 'post'], 'profiles-update/{id}', [Modules\Security\Http\Controllers\ProfilesController::class, 'update'])->name('profiles.update');
        Route::match(['get', 'post'], 'profiles-permissions/{id}', [Modules\Security\Http\Controllers\ProfilesController::class, 'permissions'])->name('profiles.permissions');

        //Route::get('users', [Modules\Security\Http\Controllers\UsersController::class, 'index'])->name('users');
        Route::get('users-list', [Modules\Security\Http\Controllers\UsersController::class, 'list'])->name('users.list');
        Route::match(['get', 'post'], 'users-create', [Modules\Security\Http\Controllers\UsersController::class, 'create'])->name('users.create');
        Route::match(['get', 'post'], 'users-update/{id}', [Modules\Security\Http\Controllers\UsersController::class, 'update'])->name('users.update');
        Route::match(['get', 'post'], 'users-password/{id}', [Modules\Security\Http\Controllers\UsersController::class, 'password'])->name('users.password');

        Route::match(['get', 'post', 'put'], 'file-admin/{id?}', [Modules\Security\Http\Controllers\SecurityController::class, 'file_admin'])->name('security.admin');


        Route::get('usuarios', [Modules\Security\Http\Controllers\UsersController::class, 'index'])->name('users');
    });
});
