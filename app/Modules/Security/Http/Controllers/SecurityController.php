<?php

namespace Modules\Security\Http\Controllers;

use App;
use App\Helpers\Encryptor;
use App\Helpers\LockDB;
use App\Http\Controllers\Controller;
// use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Config;
use File;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Modules\Security\Entities\User;
use Response;

class SecurityController extends Controller
{
    private $NAC_VENEZOLANA = 1;

    private $PROFILE_TRAMITES = 3;

    private function storeAvatar($imagenCodificada)
    {
        if (! is_string($imagenCodificada) || empty($imagenCodificada)) {
            return false;
        }

        if (! preg_match('#^data:image/[a-z0-9.+-]+;base64,#i', $imagenCodificada)) {
            return false;
        }

        [, $imagenCodificada] = explode(',', $imagenCodificada, 2);
        $imagenCodificada = base64_decode($imagenCodificada, true);

        if ($imagenCodificada === false) {
            return false;
        }

        // Validar que el contenido decodificado sea realmente una imagen
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($imagenCodificada) ?: '';
        if (! preg_match('#^image/#i', $mime)) {
            return false;
        }

        $fileName = Auth::user()->id.'.png';
        $file_fin = 'app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'avatars'.DIRECTORY_SEPARATOR.$fileName;

        $directorio = dirname(storage_path($file_fin));
        if (! File::isDirectory($directorio)) {
            File::makeDirectory($directorio, 0755, true, true);
        }

        if (File::exists(storage_path($file_fin))) {
            File::delete(storage_path($file_fin));
        }

        return File::put(storage_path($file_fin), $imagenCodificada) !== false;
    }

    public function file_admin($id = null)
    {

        abort_unless(preg_match('/^[A-Za-z0-9_-]+$/', (string) $id) === 1, 404);
        $pdfFile = storage_path($id.'.pdf');
        abort_unless(is_file($pdfFile), 404);
        $FILE_PDF = new \Smalot\PdfParser\Parser;

        $documento = $FILE_PDF->parseFile($pdfFile);

        $paginas = $documento->getPages();
        // $paginas = count($paginas);
        $pages = [];
        // dd($paginas);
        foreach ($paginas as $key => $values) {
            $pages[$key] = $key + 1;
        }

        return view('security::files.see', compact('id', 'pages'));
    }

    /*
     * sudo aptitude install php8.#-imagick
     * /etc/ImageMagick-6/
     * Edit the file: Open the policy.xml
     * <policy domain="coder" rights="read|write" pattern="PDF" />
     * sudo systemctl restart apache2
     */

    public function show_pdf($pdf, $page = 0)
    {
        // Ruta al archivo PDF
        // $pdfFile = 'a.pdf';

        $draw = new \ImagickDraw;
        $draw->setFont(public_path('fonts/arial.ttf')); // Asegúrate de que la fuente esté disponible
        $draw->setFontSize(150);
        $draw->setFillColor(new \ImagickPixel('rgba(128, 128, 128, 0.7)')); // Color blanco con opacidad
        // Posicionar el texto
        $text = Auth::user()->full_name;
        // $draw->annotation(100, 100, $text); // Ajusta la posición según sea necesario
        $draw->setGravity(\Imagick::GRAVITY_CENTER);

        // La ruta restringe {pdf} a [A-Za-z0-9_-]+; se repite aquí porque el valor
        // llega a una ruta de disco y al nombre de archivo de Imagick.
        abort_unless(preg_match('/^[A-Za-z0-9_-]+$/', (string) $pdf) === 1, 404);
        $page = (int) $page;
        $pdfFile = storage_path($pdf.'.pdf');
        abort_unless(is_file($pdfFile), 404);

        $FILE_PDF = new \Smalot\PdfParser\Parser;

        $documento = $FILE_PDF->parseFile($pdfFile);

        $paginas = $documento->getPages();

        if ($page >= count($paginas)) {
            return response()->json(['error' => 'page not found'], 400);
        }

        $imagick = new \Imagick;

        // Leer la página específica del PDF
        $imagick->setResolution(200, 200); // Establecer la resolución (opcional)
        $imagick->readImage("{$pdfFile}[{$page}]");

        // Opcional: configurar el formato de salida
        $imagick->setImageFormat('png'); // Cambia 'png' a 'jpeg' o el formato que necesites
        // Guardar la imagen resultante
        $outputFile = 'pdf_img.png';

        $angle = -45; // Ángulo en grados
        $imagick->annotateImage($draw, 0, 0, $angle, $text);

        // header("Content-type: image/png");
        $IMAGE = '<img id="img-book" style="width:100%" oncontextmenu="return false;"  src="data:image/png;base64,'.base64_encode($imagick->getImageBlob()).'">';

        // Limpiar recursos
        $imagick->clear();
        $imagick->destroy();

        // return $IMAGE;
        return view('security::files.img', compact('IMAGE'));
    }

    public function captcha($seed = null)
    {
        $seed = substr(bcrypt($seed == null ? rand(1, 99999) : $seed), 10, 5);
        // dd($seed);
        // create image and set background color
        $captcha = imagecreatetruecolor(200, 50);
        $color1 = rand(0, 255);
        $color2 = rand(0, 255);
        $color3 = rand(0, 255);
        $background_color = imagecolorallocate($captcha, $color1, $color2, $color3);
        imagefill($captcha, 0, 0, $background_color);

        // draw some lines
        for ($i = 0; $i < 10; $i++) {
            $color = rand(48, 96);
            imageline($captcha, rand(0, 255), rand(0, 255), rand(0, 255), rand(0, 255), imagecolorallocate($captcha, $color3, $color1, $color2));
        }
        /*
          #generate a random string of 5 characters
          $string = substr(md5(rand() * time()), 0, 5);

          #make string uppercase and replace "O" and "0" to avoid mistakes
          $string = strtoupper($string);
          $string = str_replace("O", "B", $string);
          $string = str_replace("0", "C", $string);
         */
        $string = Upper($seed);

        // save string in session "captcha" key

        /*
          session_start();
          $_SESSION["captcha"]=$string;

          //dd($string);
         */

        session()->put('SET_CAPTCHA', $string);

        // place each character in a random position
        // putenv('GDFONTPATH=' . realpath('.'));
        // Ruta absoluta: desde PHP 8.5 imagettftext() no resuelve rutas relativas
        // al directorio actual (antes funcionaba porque FPM corre en public/).
        $font = public_path('fonts/arial.ttf');
        for ($i = 0; $i < 5; $i++) {
            $color = rand(0, 32);
            if (file_exists($font)) {
                $x = 4 + $i * 43 + rand(0, 6);

                $y = rand(20, 28);
                imagettftext($captcha, 15, rand(-25, 25), $x, $y, imagecolorallocate($captcha, $color, $color, $color), $font, $string[$i]);
            } else {
                $x = 5 + $i * 24 + rand(0, 6);
                $y = rand(1, 18);

                imagestring($captcha, 15, $x, $y, $string[$i], imagecolorallocate($captcha, $color, $color, $color));
            }
        }

        // applies distorsion to image
        $matrix = [[1, 1, 1], [1.0, 7, 1.0], [1, 1, 1]];
        imageconvolution($captcha, $matrix, 16, 32);

        // imagejpeg() escribe directo a la salida y Laravel enviaba text/html;
        // se captura para que la respuesta lleve el cuerpo y el tipo correctos.
        ob_start();
        imagejpeg($captcha);
        $jpeg = ob_get_clean();

        return Response::make($jpeg, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);

        // avoids catching
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);

        // return the image
        header('Content-type: image/gif');

        return imagejpeg($captcha);
    }

    public function show_avatar($img)
    {
        // El middleware DecryptIds ya descifra el parámetro {img} por lo que
        // puede llegar como id numérico; si aún viene cifrado, lo desciframos aquí.
        $num = is_numeric($img) ? $img : Encryptor::decrypt($img);

        if (! is_numeric($num) || $num < 0) {
            abort(404);
        }

        $path = storage_path('app/public/img/avatars/'.$num.'.png');
        if (! File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);
        $lastModified = File::lastModified($path);
        $etag = md5($file);

        $cacheHeaders = [
            'Cache-Control' => 'public, max-age=86400',
        ];

        // ETag: si el navegador ya tiene esta versión exacta de la imagen, 304
        $requestIfNoneMatch = request()->header('If-None-Match');
        if ($requestIfNoneMatch !== null && trim($requestIfNoneMatch) === '"'.$etag.'"') {
            return response(null, 304)
                ->header('Cache-Control', $cacheHeaders['Cache-Control'])
                ->header('ETag', '"'.$etag.'"');
        }

        // Last-Modified: 304 si la imagen no cambió desde la última petición
        $requestIfModified = request()->header('If-Modified-Since');
        if ($requestIfModified !== null && $lastModified <= \Carbon\Carbon::parse($requestIfModified)->getTimestamp()) {
            return response(null, 304)
                ->header('Cache-Control', $cacheHeaders['Cache-Control'])
                ->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified).' GMT');
        }

        $response = Response::make($file, 200);
        $response->header('Content-Type', $type);
        $response->header('Cache-Control', $cacheHeaders['Cache-Control']);
        $response->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified).' GMT');
        $response->header('ETag', '"'.$etag.'"');

        return $response;
    }

    private function generateCode()
    {
        return str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function register(Request $request)
    {
        /*
          if (\Request::ajax()) {
          exit;
          }
         */
        if (\Request::isMethod('get')) {
            return view('security::users.register');
        } else {

            if (\Request::isMethod('put')) {

                $generateCode = $this->generateCode();
                $user = User::where('email', Lower($request->email))->first();
                if ($user == null) {
                    $code = \Modules\Security\Entities\Codes::where('email', strtolower($request->email))->first();
                    if ($code != null) {

                        $date = Carbon::parse($code->date);
                        $minutesSinceLastRequest = $date->diffInMinutes(Carbon::now());
                        if ($minutesSinceLastRequest > 15) {
                            $code->date = now();
                            $code->code = $generateCode;
                            $code->save();
                        } else {
                            return response()->json(['type' => 'danger', 'message' => __('You have an active code, you must wait 15 minutes'), 'status' => '0']);
                        }
                    } else {
                        $code = new \Modules\Security\Entities\Codes;
                        $code->code = $generateCode;
                        $code->email = Lower($request->email);
                        $code->date = now();
                        $code->save();
                    }
                    $subject = __('Registration Code');
                    $for = $code->email;

                    Mail::send('security::emails.send_code', ['code' => $generateCode], function ($email) use ($subject, $for) {
                        $email->subject($subject);
                        $email->to($for);
                        // $msj->attachData($proforma->output(), "PROFORMA_" . showCode($Proforma->id) . ".pdf");
                    });

                    return response()->json(['type' => 'success', 'message' => __('A code has been sent to your email'), 'status' => '1']);
                } else {
                    return response()->json(['type' => 'danger', 'message' => __('A User Already Exists with That Email'), 'status' => '0']);
                }
            } else {
                // dd($request->all());
                $request->validate([
                    'email' => 'required|email',
                    'code' => 'required',
                    'password' => ['required', 'same:re_password', Password::defaults()],
                    're_password' => 'required',
                    'captcha' => 'required',
                ]);

                if (Lower(session()->get('SET_CAPTCHA')) !== Lower($request->captcha)) {
                    return back()->withErrors(['error-message' => __('Captcha does not match')])->withInput();
                }

                $user = User::where('email', strtolower($request->email))->first();

                if ($user == null) {
                    $findCodeExists = \Modules\Security\Entities\Codes::where('email', strtolower($request->email))->first();
                    if ($findCodeExists != null) {
                        if ($findCodeExists->code == $request->code) {
                            $findCodeExists->processed = true;
                            $findCodeExists->save();

                            $Country = \Modules\Security\Entities\Countries::where('iso2', 've')->first();

                            $User = new User;
                            $User->document_type_id = $this->NAC_VENEZOLANA;
                            $User->document = '';
                            $User->full_name = '';
                            $User->email = $request->email;
                            $User->username = '';
                            $User->password = Hash::make($request->password);
                            $User->phone = '';
                            $User->country_id = $Country ? $Country->id : null;
                            $User->profile_id = $this->PROFILE_TRAMITES;

                            $User->active = 0;
                            $User->user_id = 1;
                            $User->register_date = now();
                            $User->ip = $request->ip();
                            $User->save();

                            return to_route('login')->withSuccess(__('Successfully Created User'));
                        } else {
                            return back()->withErrors(['error-message' => __('Your verification code is wrong')]);
                        }
                    } else {
                        return back()->withErrors(['error-message' => __('You do not have a verification code')]);
                    }
                } else {
                    return back()->withErrors(['error-message' => __('A User Already Exists with That Email')]);
                }
            }
        }
    }

    public function recovery(Request $request, $token = null)
    {
        /*
          if (\Request::ajax()) {
          exit;
          }
         */
        if (\Request::isMethod('get')) {
            if ($token == null) {
                return view('security::users.recovery');
            } else {

                // Buscar usuario cuyo hash de token coincide con el token recibido en la URL
                $hashedToken = hash('sha256', $token);
                $User = User::where('token', $hashedToken)->first();
                if ($User != null) {
                    if ($User->change_password == true) {
                        $date = Carbon::parse($User->date_change_password);
                        $minutesSinceLastRequest = $date->diffInMinutes(Carbon::now());
                        if ($minutesSinceLastRequest < 30) {
                            // Guardar ID cifrado en sesión para el paso siguiente
                            session()->put('SET_ID_ALTER_PASSWORD', $User->crypt_id);
                            // Consumir el token (invalidarlo para evitar re-uso)
                            User::whereId($User->id)->update([
                                'change_password' => true,
                                'token' => '', // Token consumido tras validación
                                'date_change_password' => $User->date_change_password, // Mantener timestamp
                            ]);

                            return view('security::users.recovery_password', compact('User'));
                        } else {
                            User::whereId($User->id)->update([
                                'change_password' => false,
                                'token' => '',
                                'date_change_password' => null,
                            ]);

                            return to_route('login')->withErrors(['error-message' => __('Security Token Expired')]);
                        }
                    } else {
                        User::whereId($User->id)->update([
                            'change_password' => false,
                            'token' => '',
                            'date_change_password' => null,
                        ]);
                    }
                }

                return to_route('login');
                //
            }
        } else {
            if (\Request::isMethod('post')) {
                // dd($request->all());
                $request->validate([
                    'email' => 'required|email',
                    'captcha' => 'required',
                ]);
                if (Lower(session()->get('SET_CAPTCHA')) !== Lower($request->captcha)) {
                    return back()->withErrors(['error-message' => __('Captcha does not match')])->withInput();
                }

                $user = User::where('email', strtolower($request->email))->first();

                if ($user != null) {
                    // Generar token criptográfico aleatorio seguro (64 chars)
                    $plainToken = Str::random(64);
                    // Almacenar SOLO el hash en DB — nunca el token en claro
                    $hashedToken = hash('sha256', $plainToken);

                    $subject = __('Recover Password');
                    $for = $request->email;

                    try {
                        // Enviar el token PLANO por email (el receptor tendrá el link con token real)
                        Mail::send('security::emails.recovery_password', ['code' => $plainToken, 'user' => $user->toArray()], function ($email) use ($subject, $for) {
                            $email->subject($subject);
                            $email->to($for);
                        });
                    } catch (\Exception $ex) {
                        return back()->withErrors(['error-message' => __('There is a problem with your email, please check.')])->withInput();
                    }

                    // Guardar HASH del token en DB — nunca en texto plano
                    User::whereId($user->id)->update([
                        'change_password' => true,
                        'token' => $hashedToken,
                        'date_change_password' => now(),
                    ]);

                    return to_route('login')->withSuccess(__('An email has been sent to you with specifications for updating your password.'));
                } else {
                    return to_route('login');
                }
            } else {
                $request->validate([
                    'password' => ['required', 'same:re_password', Password::defaults()],
                    're_password' => 'required',
                    'captcha' => 'required',
                ]);

                if (Lower(session()->get('SET_CAPTCHA')) !== Lower($request->captcha)) {
                    return back()->withErrors(['error-message' => __('Captcha does not match')])->withInput();
                }
                if (session()->get('SET_ID_ALTER_PASSWORD') != null) {

                    $id = Encryptor::decrypt(session()->get('SET_ID_ALTER_PASSWORD'));
                    $User = User::find($id);

                    if (Hash::check($request->password, $User->password)) {
                        return back()->withErrors(['error-message' => __('New Password cannot be same as your current password.')])->withInput();
                    }

                    User::whereId($User->id)->update([
                        'change_password' => false,
                        'token' => '',
                        'date_change_password' => null,
                        'password' => Hash::make($request->password),
                    ]);

                    return to_route('login')->withSuccess(__('User Updated Successfully'));
                } else {
                    return to_route('login')->withSuccess(__('Security Token Expired'));
                }
            }
        }
    }

    public function login(Request $request)
    { // Define el método login, que recibe como parámetro un objeto $request, que contiene los datos enviados por el usuario.

        if (Auth::check() == true) {
            session()->flush();
            Auth::logout();
        } // Si el usuario está autenticado (ya inició sesión), se limpia toda la sesión y se ejecuta el cierre de sesión para asegurar un estado limpio antes de continuar.

        if (\Request::ajax()) {
            exit;
        } // Si la petición es AJAX, se termina la ejecución inmediatamente, probablemente para prevenir accesos no autorizados por AJAX.

        if (\Request::isMethod('get')) {
            // Config::set('app.javascripts', ['5', '6']);
            // dd(config('app.javascripts'))
            // return view('login');
            // return view('welcome');
            return view('security::users.login'); // Si la petición es GET, muestra la vista del formulario de login desde el módulo security, y no realiza nada más.
        } else { // Si es otro tipo de petición (usualmente POST), continúa con la lógica de autenticación.

            $request->validate([
                'username' => 'required',
                'password' => 'required',
                'captcha' => 'required',
            ]); // Valida que los campos username, password y captcha estén presentes en el request, caso contrario lanza error.
            if (Lower(session()->get('SET_CAPTCHA')) !== Lower($request->captcha)) {
                return back()->withErrors(['error-message' => __('Captcha does not match')])->withInput();
            } // Compara el captcha almacenado en sesión (convertido a minúsculas) con el captcha enviado. Si no coinciden, retorna con error y llena el formulario con los datos previamente ingresados.

            $usernameNormalized = mb_strtolower($request->username);
            $user = User::whereRaw('LOWER(username) = ?', [$usernameNormalized])
                ->orWhereRaw('LOWER(email) = ?', [$usernameNormalized])
                ->first(); // Busca en la base de datos el usuario cuyo campo (email o username) coincide con el valor recibido, de forma case-insensitive.

            if ($user == null) {// Si no encuentra usuario, retorna con mensaje de error. Si sí, continúa.

                return back()->withErrors(['error-message' => __('Wrong Data')]);
            } else {
                if ($user->active == 1) {// Verifica que el usuario esté activo. Si no lo está, retorna error.
                    if (Hash::check($request->password, $user->password)) {// Verifica la contraseña contra el hash almacenado, sin depender del formato exacto del username en la DB.
                        Auth::login($user); // Autentica directamente con el modelo recuperado (case-insensitive).

                        session()->regenerate(); // Previene session fixation: genera nuevo ID de sesión tras autenticación exitosa

                        // Token de sesión: se guarda en sesión para permitir invalidación remota
                        // cuando el status de un perfil asignado cambia (driver de sesión es File).
                        $fresh = Auth::user();
                        if (empty($fresh->session_token)) {
                            $fresh->session_token = \Illuminate\Support\Str::random(40);
                            $fresh->save();
                        }
                        session()->put('session_token', $fresh->session_token);

                        User::whereId(Auth::user()->id)->update([
                            'change_password' => false,
                            'token' => '',
                            'date_change_password' => null,
                        ]); // Si el login es exitoso, actualiza el usuario logueado para quitar flags temporales relacionados con el cambio de contraseña y limpiar tokens.

                        // Sin selección de perfil: los permisos suman todos los perfiles
                        // activos. El principal se guarda solo como dato informativo.
                        if ($principal = app(\App\Services\Security\Autorizacion::class)->perfilPrincipalPara($fresh)) {
                            session()->put('profile_id', $principal);
                        }

                        // COMENTADO 2026-04-24: Lógica de módulos eliminada del sidebar
                        // if (Auth::user()->getModules()->count() == 1) {
                        //     session()->put('MODULE', Auth::user()->getModules()[0]->id);
                        // }

                        return redirect()->intended(route('home')); // Redirige a la ruta nombrada home (dashboard o página principal).
                    } else {
                        return back()->withErrors(['error-message' => __('Wrong Data')])->withInput(); // Si el login falla, muestra error y rellena el formulario con datos ingresados.
                    }
                } else {
                    return back()->withErrors(['error-message' => __('User blocked')])->withInput(); // Si el usuario no está activo (bloqueado), muestra error específico.
                }
            }
        }
    }

    /**
     * Desde el 26/10 no se elige perfil: los permisos suman todos los perfiles
     * activos. Las rutas antiguas de selección/cambio quedan como redirección
     * al inicio para no romper enlaces guardados.
     */
    public function seleccionarPerfil($id_rol)
    {
        return redirect()->intended(route('home'));
    }

    public function showSwitchProfile()
    {
        return to_route('home');
    }

    public function home()
    {
        // dd(Auth::user()->getMenu());
        return view('security::users.home');
    }

    /** /home y /security/usuario/perfil/seleccionar: ya no hay selección de perfil. */
    public function registroHome()
    {
        return to_route('home');
    }

    // COMENTADO 2026-04-24: Lógica de módulos eliminada del sidebar
    // public function set_module($id)
    // {
    //     session()->put('MODULE', Encryptor::decrypt($id));
    //     return to_route('home');
    // }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }

    public function update_profile(Request $request)
    {

        if (\Request::isMethod('get')) {

            $typeDoc = \Modules\Security\Entities\DocumentType::get()->pluck('code', 'crypt_id');

            return view('security::users.update_profile', compact('typeDoc'));
        } else {
            $option = LockDB::crazy($request->option);
            // dd($option);
            switch ($option) {
                case 'photo':
                    $request->validate([
                        'set_new_photo' => 'required',
                    ]);

                    if (! $this->storeAvatar($request->set_new_photo)) {
                        return to_route('update_profile')->withErrors(['error-message' => __('Photo upload failed. Make sure to take a new photo and try again.')]);
                    }

                    return to_route('update_profile')->withSuccess(__('Photo Updated Correctly'));

                    break;
                case 'profile':

                    $request->validate([
                        'address' => 'nullable|string|max:255',
                        'phone' => 'required',
                        'email' => 'required|email|max:100',
                        'code' => 'required',
                    ]);

                    $email = strtolower(trim($request->email));

                    /*
                     * Verificar que el correo no este repetido
                     */
                    $emailExists = User::where('email', $email)
                        ->where('id', '!=', Auth::user()->id)
                        ->exists();
                    if ($emailExists == true) {
                        return to_route('update_profile')->withErrors(['error-message' => __('The email has already been taken.')]);
                    }

                    /*
                     * Validar el código de verificación enviado por correo
                     * (mismo mecanismo que el registro)
                     */
                    $findCodeExists = \Modules\Security\Entities\Codes::where('email', $email)
                        ->where('code', $request->code)
                        ->where('processed', false)
                        // El código caduca a los 15 minutos (mismo plazo que su reenvío).
                        ->where('date', '>=', now()->subMinutes(15))
                        ->first();
                    if (! $findCodeExists) {
                        return to_route('update_profile')->withErrors(['error-message' => __('Your verification code is wrong')]);
                    }

                    // Cambiar el correo cambia la identidad de acceso y de recuperación:
                    // se exige reautenticación con la contraseña actual.
                    $cambiaCorreo = $email !== strtolower((string) Auth::user()->email);
                    if ($cambiaCorreo && ! Hash::check((string) $request->current_password, Auth::user()->password)) {
                        return to_route('update_profile')->withErrors(['current_password' => __("Old Password Doesn't match!")]);
                    }

                    $Country = \Modules\Security\Entities\Countries::where('iso2', $request->country_iso2)->first();

                    $Usuario = User::find(Auth::user()->id);

                    $phone = preg_replace('/[^0-9]/', '', $request->phone);
                    if ($Country) {
                        $dial = (string) $Country->dial_code;
                        if ($dial !== '' && substr($phone, 0, strlen($dial)) === $dial) {
                            $phone = substr($phone, strlen($dial));
                        }
                    }

                    $Usuario->address = $request->address;
                    $Usuario->phone = $phone;
                    $Usuario->email = $email;
                    if ($Country) {
                        $Usuario->country_id = $Country->id;
                    }

                    $Usuario->user_id = Auth::user()->id;
                    $Usuario->ip = $request->ip();

                    $Usuario->save();

                    if ($Usuario->personalData) {
                        $Usuario->personalData->direccion = $request->address;
                        if ($Country) {
                            $Usuario->personalData->id_pais = $Country->id;
                        }
                        $Usuario->personalData->save();
                    }

                    $findCodeExists->processed = true;
                    $findCodeExists->save();

                    if ($cambiaCorreo) {
                        $this->rotarSesiones($request, $Usuario);
                    }

                    return to_route('update_profile')->withSuccess(__('Correctly Updated Data'));

                    break;
                case 'credentials':
                    $request->validate([
                        'current_password' => 'required',
                        'new_password' => ['required', 'same:re_password', Password::defaults()],
                        're_password' => 'required',
                    ]);

                    if (! Hash::check($request->current_password, Auth::user()->password)) {
                        return to_route('update_profile')->withErrors(['error-message' => __("Old Password Doesn't match!")]);
                    }
                    if (Hash::check($request->new_password, Auth::user()->password)) {
                        return to_route('update_profile')->withErrors(['error-message' => __('New Password cannot be same as your current password.')]);
                    }

                    User::whereId(auth()->user()->id)->update([
                        'password' => Hash::make($request->new_password),
                    ]);

                    $this->rotarSesiones($request, User::find(Auth::id()));

                    return to_route('update_profile')->withSuccess(__('Password changed successfully!'));
                    // dd($request->all());
                    break;
                default:
                    return to_route('update_profile');
                    break;
            }

            return view('security::users.update_profile');
        }
    }

    /**
     * Envía un código de verificación al correo indicado en "Editar Datos".
     * Misma mecánica que el registro: código de 6 dígitos en security.codes
     * con reutilización cada 15 minutos.
     */
    public function update_profile_send_code(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:100',
        ]);

        if (User::where('email', strtolower($request->email))->where('id', '!=', Auth::user()->id)->exists()) {
            return response()->json(['type' => 'danger', 'message' => __('The email has already been taken.'), 'status' => '0']);
        }

        $generateCode = $this->generateCode();
        $code = \Modules\Security\Entities\Codes::where('email', strtolower($request->email))->first();

        if ($code != null) {
            $date = Carbon::parse($code->date);
            $minutesSinceLastRequest = $date->diffInMinutes(Carbon::now());
            if ($minutesSinceLastRequest > 15) {
                $code->date = now();
                $code->code = $generateCode;
                $code->processed = false;
                $code->save();
            } else {
                return response()->json(['type' => 'danger', 'message' => __('You have an active code, you must wait 15 minutes'), 'status' => '0']);
            }
        } else {
            $code = new \Modules\Security\Entities\Codes;
            $code->code = $generateCode;
            $code->email = strtolower($request->email);
            $code->date = now();
            $code->save();
        }

        $subject = __('Security Code');
        $for = $code->email;

        try {
            Mail::send('security::emails.send_code', ['code' => $generateCode], function ($email) use ($subject, $for) {
                $email->subject($subject);
                $email->to($for);
            });
        } catch (\Exception $e) {
            // El detalle (servidor SMTP, credenciales) va al log, no al cliente.
            Log::error('Error al enviar código de verificación', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);

            return response()->json(['type' => 'danger', 'message' => __('Error sending email: ').__('please try again later'), 'status' => '0']);
        }

        return response()->json(['type' => 'success', 'message' => __('A code has been sent to your email'), 'status' => '1']);
    }

    /**
     * Tras cambiar contraseña o correo: nuevo ID de sesión y nuevo session_token.
     * Authenticate cierra las demás sesiones del usuario (su token ya no coincide);
     * la sesión actual conserva el acceso con el token nuevo.
     */
    private function rotarSesiones(Request $request, User $usuario): void
    {
        $token = \Illuminate\Support\Str::random(40);
        \Illuminate\Support\Facades\DB::table('security.users')->where('id', $usuario->id)->update(['session_token' => $token]);

        $request->session()->regenerate();
        $request->session()->put('session_token', $token);
        Auth::user()->session_token = $token;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index()
    {
        return view('security::index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        return view('security::create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('security::show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('security::edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
