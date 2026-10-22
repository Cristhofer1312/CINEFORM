<?php

namespace Modules\Taller\Http\Controllers;

use App\Constants\SecurityAction;
use App\Enums\EstadoCertificado;
use App\Enums\EstadoCurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\ContenidoCurso;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\CursoRequisito;
use Modules\Taller\Http\Requests\StoreCursoRequest;
use Modules\Taller\Http\Requests\StorePaso1Request;
use Modules\Taller\Http\Requests\StorePaso2Request;
use Modules\Taller\Http\Requests\StorePaso3Request;
use Modules\Taller\Rules\ImagenCertificado;
use Modules\Taller\Services\CondicionalEstadoCurso;

class CrearCursoController extends BaseController
{
    /**
     * Entrada legacy al wizard (formulario monolítico retirado).
     *
     * El flujo actual es en 5 pasos con borrador por usuario; esta ruta
     * solo redirige al paso 1 para no romper marcadores antiguos.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        if (! hasPermissionRoute('taller.cursos.index', SecurityAction::CREAR_CURSO)) {
            abort(403, 'No tienes permiso para planificar cursos.');
        }

        return redirect()->route('taller.cursos.wizard.paso1.create');
    }

    /**
     * Entrada legacy (POST del formulario monolítico retirado).
     *
     * Redirige al paso 1: los slices se capturan por separado con
     * StorePaso1/2/3Request y borrador por usuario.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreCursoRequest $request)
    {
        if (! hasPermissionRoute('taller.cursos.index', SecurityAction::CREAR_CURSO)) {
            abort(403, 'No tienes permiso para planificar cursos.');
        }

        return redirect()->route('taller.cursos.wizard.paso1.create')
            ->with('info', 'El formulario de creación ahora es por pasos. Tus datos no se perdieron: complétalos aquí.');
    }

    /**
     * Wizard 5 pasos: datos compartidos de catálogos.
     *
     * Tablas casi estáticas: se cachean 5 min para no repetirlas en
     * cada GET del wizard. Clave versionada para invalidar a mano si
     * se editan catálogos (taller:… o bump manual).
     */
    public const CATALOGOS_CACHE_KEY = 'taller.wizard.catalogos.v2';

    private function catalogosPaso1()
    {
        return \Illuminate\Support\Facades\Cache::remember(
            self::CATALOGOS_CACHE_KEY,
            300,
            function () {
                return [
                    'modalidades' => \Modules\Taller\Entities\Modalidad::all(),
                    'actividades' => \Modules\Taller\Entities\ActividadFormativa::where('status', 'Activo')->orderBy('nombre')->get(),
                    'aspectos' => \Modules\Taller\Entities\Aspecto::where('status', 'Activo')->orderBy('nombre')->get(),
                    'modalidadesEspeciales' => \Modules\Taller\Entities\ModalidadEspecial::all(),
                    'regiones' => \Modules\Parametros\Entities\Estados::orderBy('description')->get(),
                ];
            }
        );
    }

    public static function olvidarCacheCatalogos(): void
    {
        \Illuminate\Support\Facades\Cache::forget(self::CATALOGOS_CACHE_KEY);
    }

    private function exigirPermisoCrear(): void
    {
        if (! hasPermissionRoute('taller.cursos.index', SecurityAction::CREAR_CURSO)) {
            abort(403, 'No tienes permiso para planificar cursos.');
        }
    }

    /**
     * Descarta el borrador actual y empieza uno nuevo desde cero.
     */
    public function reiniciar()
    {
        $this->exigirPermisoCrear();
        app(\Modules\Taller\Services\BorradorWizardService::class)->reiniciar(Auth::id());

        return redirect()->route('taller.cursos.wizard.paso1.create')
            ->with('info', 'Se ha iniciado un nuevo borrador en blanco.');
    }

    /**
     * Paso 1: Identidad y clasificación (precarga borrador + old()).
     */
    public function createPaso1()
    {
        $this->exigirPermisoCrear();
        $servicio = app(\Modules\Taller\Services\BorradorWizardService::class);
        $borrador = $servicio->actual(Auth::id());
        $payload = is_array($borrador->payload) ? $borrador->payload : [];
        $paso1 = $payload['paso1'] ?? [];
        $completados = $this->pasosCompletados($borrador);
        $tieneBorradorPrevio = ! empty($payload['paso1']) || ! empty($payload['paso2']) || ! empty($payload['contenidos']);

        $fotoUrl = $this->resolverFotoWizardUrl($borrador);

        return view('taller::a.CursoCrearPaso1', array_merge(
            $this->catalogosPaso1(),
            [
                'paso1' => $paso1,
                'pasoActual' => 1,
                'completados' => $completados,
                'borrador' => $borrador,
                'tieneBorradorPrevio' => $tieneBorradorPrevio,
                'fotoUrl' => $fotoUrl,
            ]
        ));
    }

    /**
     * Guarda el slice del paso 1 (borrador, sin validar completitud).
     */
    public function storePaso1(StorePaso1Request $request)
    {
        $this->exigirPermisoCrear();
        $servicio = app(\Modules\Taller\Services\BorradorWizardService::class);
        $borrador = $servicio->actual(Auth::id());

        $datos = $request->validated();
        unset($datos['foto'], $datos['eliminar_foto'], $datos['accion'], $datos['guardar_y_salir']);

        // La purga de temporales abandonados la hace el diario
        // taller:limpiar-wizards (respeta tokens con borrador vigente).
        $wizardDir = storage_path('app/public/Certificados/wizard/'.$borrador->token);
        if (! file_exists($wizardDir)) {
            @mkdir($wizardDir, 0775, true);
        }
        // Foto de portada en temporal (extensión según MIME real).
        $fotoPath = $borrador->foto_path;

        if ($request->boolean('eliminar_foto')) {
            foreach (['png', 'jpg', 'jpeg'] as $ext) {
                @unlink($wizardDir.'/foto.'.$ext);
            }
            $fotoPath = null;
        } elseif ($request->hasFile('foto')) {
            $extensionFoto = ((string) $request->file('foto')->getMimeType()) === 'image/png' ? 'png' : 'jpg';
            foreach (['png', 'jpg', 'jpeg'] as $ext) {
                @unlink($wizardDir.'/foto.'.$ext);
            }
            $request->file('foto')->move($wizardDir, 'foto.'.$extensionFoto);
            @chmod($wizardDir.'/foto.'.$extensionFoto, 0664);
            $fotoPath = 'wizard/'.$borrador->token.'/foto.'.$extensionFoto;
        }

        if (is_dir($wizardDir) && is_writable($wizardDir)) {
            @file_put_contents($wizardDir.'/datos.json', json_encode($datos, JSON_PRETTY_PRINT));
        }

        $servicio->guardarSlice(Auth::id(), 'paso1', $datos, 2);
        $servicio->marcarFoto(Auth::id(), $fotoPath);

        if ($request->input('accion') === 'salir' || $request->filled('guardar_y_salir')) {
            return redirect()->route('taller.cursos.index')
                ->with('success', 'Borrador guardado exitosamente.');
        }

        return redirect()->route('taller.cursos.wizard.paso2.create')
            ->with('success', 'Identidad guardada. Ahora define fechas, sede y facilitador.');
    }

    /**
     * Paso 2: Logística, fechas y sede. Trimestre/anio derivados.
     */
    public function createPaso2()
    {
        $this->exigirPermisoCrear();
        $servicio = app(\Modules\Taller\Services\BorradorWizardService::class);
        $borrador = $servicio->actual(Auth::id());
        $payload = is_array($borrador->payload) ? $borrador->payload : [];
        if (empty($payload['paso1'])) {
            return redirect()->route('taller.cursos.wizard.paso1.create')
                ->withErrors(['error' => 'Completa primero la identidad del curso (paso 1).']);
        }

        $paso2 = $payload['paso2'] ?? [];
        // El modal de facilitadores carga por AJAX paginado (buscarFacilitadores):
        // no se renderiza la lista completa aquí (N usuarios + stats de avatar).
        // Se resuelve el seleccionado con old() primero (redirect con errores).
        $idSel = old('id_persona', $paso2['id_persona'] ?? null);
        $facilitadorSel = null;
        if (! empty($idSel)) {
            $facilitadorSel = \Modules\Comun\Entities\PersonalData::with('especializaciones')
                ->find($idSel);
        }
        $regiones = $this->catalogosPaso1()['regiones'];
        // Primera página renderizada en servidor (respaldo si el fetch falla);
        // la búsqueda y "cargar más" usan buscarFacilitadores por AJAX.
        $facilitadoresIni = $this->queryFacilitadores(null)->paginate(10);
        $anioActual = date('Y');
        $ultimoCorrelativo = Curso::where('anio', $paso2['anio'] ?? $anioActual)->max('correlativo') ?? 0;
        $proximoCorrelativo = ($paso2['correlativo'] ?? null) ?: $ultimoCorrelativo + 1;
        $codigoPreview = $this->generarCodigoPreview(array_merge($payload['paso1'] ?? [], $paso2, [
            'trimestre' => $paso2['trimestre'] ?? $this->trimestreDe($paso2['fecha_inicio'] ?? null),
            'anio' => $paso2['anio'] ?? $anioActual,
            'correlativo' => $proximoCorrelativo,
        ]));

        $catalogos = $this->catalogosPaso1();
        $mapa = fn ($col, $key) => $col->pluck('abreviatura', $key);
        $codigoPartes = [
            'actividad' => $mapa($catalogos['actividades'], 'id_actividad_formativa')[$payload['paso1']['id_actividad_formativa'] ?? 0] ?? '',
            'aspecto' => $mapa($catalogos['aspectos'], 'id_aspecto')[$payload['paso1']['id_aspecto'] ?? 0] ?? '',
            'modalidad' => $mapa($catalogos['modalidades'], 'id_modalidad')[$payload['paso1']['id_modalidad'] ?? 0] ?? '',
            'modEsp' => $mapa($catalogos['modalidadesEspeciales'], 'id_modalidad_especial')[$payload['paso1']['id_modalidad_especial'] ?? 0] ?? '',
        ];

        return view('taller::a.CursoCrearPaso2', [
            'paso2' => $paso2,
            'pasoActual' => 2,
            'completados' => $this->pasosCompletados($borrador),
            'borrador' => $borrador,
            'facilitadorSel' => $facilitadorSel,
            'facilitadoresIni' => $facilitadoresIni,
            'regiones' => $regiones,
            'proximoCorrelativo' => $proximoCorrelativo,
            'codigoPreview' => $codigoPreview,
            'codigoPartes' => $codigoPartes,
            'trimestreSugerido' => $this->trimestreDe($paso2['fecha_inicio'] ?? null),
        ]);
    }

    /**
     * Base del buscador de facilitadores (usuarios con EDITAR_CURSO y
     * ficha personal). El render inicial y el AJAX comparten el filtro.
     */
    private function queryFacilitadores(?string $q)
    {
        $q = trim((string) $q);

        return User::whereHas('perfiles.permissions', function ($query) {
            $query->where('security.permissions.slug', SecurityAction::dbString(SecurityAction::EDITAR_CURSO));
        })->whereHas('personalData', function ($query) use ($q) {
            if ($q !== '') {
                $query->where(function ($sub) use ($q) {
                    $sub->where('primer_nombre', 'ilike', "%{$q}%")
                        ->orWhere('primer_apellido', 'ilike', "%{$q}%")
                        ->orWhere('dni', 'ilike', "%{$q}%")
                        ->orWhereRaw("CONCAT_WS(' ', primer_nombre, segundo_nombre, primer_apellido, segundo_apellido) ILIKE ?", ["%{$q}%"])
                        ->orWhereRaw("CONCAT_WS(' ', primer_nombre, primer_apellido) ILIKE ?", ["%{$q}%"]);
                });
            }
        })->with(['personalData.especializaciones']);
    }

    /**
     * Búsqueda paginada de facilitadores para el modal del paso 2.
     *
     * Evita renderizar los N usuarios con avatar en el HTML del paso.
     */
    public function buscarFacilitadores(Request $request)
    {
        $this->exigirPermisoCrear();

        $usuarios = $this->queryFacilitadores($request->query('q', ''))
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'data' => $usuarios->getCollection()->map(function ($u) {
                $p = $u->personalData;

                return [
                    'id_persona' => $p->id_persona,
                    'nombre' => trim(($p->primer_nombre ?? '').' '.($p->primer_apellido ?? '')),
                    'dni' => $p->dni,
                    'especialidades' => $p->especializaciones->pluck('nombre')->take(3)->values(),
                ];
            })->values(),
            'next_page_url' => $usuarios->nextPageUrl(),
            'current_page' => $usuarios->currentPage(),
        ]);
    }

    public function storePaso2(StorePaso2Request $request)
    {
        $this->exigirPermisoCrear();
        $datos = $request->validated();
        unset($datos['accion'], $datos['guardar_y_salir']);
        app(\Modules\Taller\Services\BorradorWizardService::class)->guardarSlice(Auth::id(), 'paso2', $datos, 3);

        if ($request->input('accion') === 'salir' || $request->filled('guardar_y_salir')) {
            return redirect()->route('taller.cursos.index')
                ->with('success', 'Borrador guardado exitosamente.');
        }

        return redirect()->route('taller.cursos.wizard.paso3.create')
            ->with('success', 'Logística guardada. Ahora arma el temario.');
    }

    /**
     * Paso 3: Temario simple sin evaluaciones.
     */
    public function createPaso3()
    {
        $this->exigirPermisoCrear();
        $servicio = app(\Modules\Taller\Services\BorradorWizardService::class);
        $borrador = $servicio->actual(Auth::id());
        $payload = is_array($borrador->payload) ? $borrador->payload : [];
        if (empty($payload['paso2'])) {
            return redirect()->route('taller.cursos.wizard.paso2.create')
                ->withErrors(['error' => 'Completa primero la logística del curso (paso 2).']);
        }

        return view('taller::a.CursoCrearPaso3', [
            'contenidos' => $payload['contenidos'] ?? [],
            'pasoActual' => 3,
            'completados' => $this->pasosCompletados($borrador),
            'borrador' => $borrador,
            'rango' => [
                'inicio' => $payload['paso2']['fecha_inicio'] ?? null,
                'fin' => $payload['paso2']['fecha_fin'] ?? null,
            ],
        ]);
    }

    public function storePaso3(StorePaso3Request $request)
    {
        $this->exigirPermisoCrear();
        $datos = $request->validated();
        unset($datos['accion'], $datos['guardar_y_salir']);
        app(\Modules\Taller\Services\BorradorWizardService::class)
            ->guardarSlice(Auth::id(), 'contenidos', $datos['contenidos'] ?? [], 4);

        if ($request->input('accion') === 'salir' || $request->filled('guardar_y_salir')) {
            return redirect()->route('taller.cursos.index')
                ->with('success', 'Borrador guardado exitosamente.');
        }

        return redirect()->route('taller.cursos.wizard.plantilla.create')
            ->with('success', 'Temario guardado. Configura el certificado (opcional).');
    }

    /**
     * Revisión pre-flight antes de finalizar.
     */
    public function revision()
    {
        $this->exigirPermisoCrear();
        $servicio = app(\Modules\Taller\Services\BorradorWizardService::class);
        $borrador = $servicio->actual(Auth::id());
        $payload = is_array($borrador->payload) ? $borrador->payload : [];
        if (empty($payload['paso1']) || empty($payload['paso2'])) {
            return redirect()->route('taller.cursos.wizard.paso1.create')
                ->withErrors(['error' => 'Completa los pasos 1 y 2 antes de revisar.']);
        }

        $wizardDir = storage_path('app/public/Certificados/wizard/'.$borrador->token);
        $config = \Modules\Taller\Services\CertificadoConfig::class;
        $hayPlantilla = $config::rutaWizardPlantilla($borrador->token) !== null
            || $config::buscarPrimero($config::dirBase(), 'plantilla') !== null;
        $hayFirma = $config::rutaWizardFirma($borrador->token) !== null;
        $estadoCertificado = ! $hayPlantilla ? 'SIN_PLANTILLA' : (! $hayFirma ? 'FALTA_FIRMA' : 'CONFIGURADO');

        $paso2 = $payload['paso2'];
        $codigoPreview = $this->generarCodigoPreview(array_merge(
            $payload['paso1'], $paso2, ['correlativo' => $paso2['correlativo'] ?? '?']
        ));
        $facilitador = ! empty($paso2['id_persona'])
            ? \Modules\Comun\Entities\PersonalData::find($paso2['id_persona'])
            : null;

        $modalidadEspecial = ! empty($payload['paso1']['id_modalidad_especial'])
            ? \Modules\Taller\Entities\ModalidadEspecial::find($payload['paso1']['id_modalidad_especial'])
            : null;
        $esParaMenores = (bool) ($modalidadEspecial->es_menor ?? false);

        $requisitos = is_array($payload['requisitos'] ?? null) ? $payload['requisitos'] : [];
        if (! $esParaMenores) {
            $requisitos = array_values(array_filter(
                $requisitos,
                fn ($req) => ! CursoRequisito::esCartaPermisoDocumento($req)
            ));
        } else {
            if (! collect($requisitos)->contains(fn ($req) => CursoRequisito::esCartaPermisoDocumento($req))) {
                $requisitos[] = CursoRequisito::requisitoCartaPermiso();
            }

            $requisitos = array_map(function ($req) {
                if (CursoRequisito::esCartaPermisoDocumento($req)) {
                    $req['obligatorio'] = true;
                }

                return $req;
            }, $requisitos);
        }

        $fotoUrl = $this->resolverFotoWizardUrl($borrador);

        return view('taller::a.CursoRevision', [
            'paso1' => $payload['paso1'],
            'paso2' => $paso2,
            'contenidos' => $payload['contenidos'] ?? [],
            'requisitos' => $requisitos,
            'pasoActual' => 5,
            'completados' => $this->pasosCompletados($borrador),
            'codigoPreview' => $codigoPreview,
            'facilitador' => $facilitador,
            'estadoCertificado' => $estadoCertificado,
            'esParaMenores' => $esParaMenores,
            'publicoObjetivo' => $modalidadEspecial->nombre ?? 'General',
            'hayFoto' => ! empty($fotoUrl),
            'fotoUrl' => $fotoUrl,
            'borrador' => $borrador,
        ]);
    }

    /**
     * Pasos con datos guardados (para el stepper clicable).
     */
    private function pasosCompletados($borrador): array
    {
        $payload = is_array($borrador->payload) ? $borrador->payload : [];
        $completados = [];
        if (! empty($payload['paso1'])) {
            $completados[] = 1;
        }
        if (! empty($payload['paso2'])) {
            $completados[] = 2;
        }
        if (! empty($payload['contenidos'])) {
            $completados[] = 3;
        }
        $config = \Modules\Taller\Services\CertificadoConfig::class;
        if ($config::rutaWizardPlantilla($borrador->token) !== null || $config::rutaWizardFirma($borrador->token) !== null) {
            $completados[] = 4;
        }
        if (! empty($payload['requisitos'])) {
            $completados[] = 5;
        }

        return $completados;
    }

    private function trimestreDe(?string $fecha): ?int
    {
        if (! $fecha) {
            return null;
        }
        try {
            return (int) ceil(\Carbon\Carbon::parse($fecha)->month / 3);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Paso 2: Muestra la calibración de plantilla del wizard.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function wizardPlantillaCreate()
    {
        if (! hasPermissionRoute('taller.cursos.index', SecurityAction::CREAR_CURSO)) {
            abort(403, 'No tienes permiso para planificar cursos.');
        }

        $servicio = app(\Modules\Taller\Services\BorradorWizardService::class);
        $borrador = $servicio->actual(Auth::id());
        $payload = is_array($borrador->payload) ? $borrador->payload : [];

        if (empty($payload['paso1']) || empty($payload['paso2'])) {
            return redirect()->route('taller.cursos.wizard.paso1.create')
                ->withErrors(['error' => 'Completa los pasos previos de identidad y logística antes de configurar el certificado.']);
        }

        $servicio->sincronizarSesion($borrador);
        $token = $borrador->token;
        $config = \Modules\Taller\Services\CertificadoConfig::class;

        // --- MANEJO DE IMAGEN DE PLANTILLA CON PREVENCIÓN DE CACHÉ ---
        // Se resuelve el estado actual del wizard (o la global): nunca de cero.
        $plantillaPath = $config::rutaWizardPlantilla($token);
        $plantillaGlobalPath = $config::buscarPrimero($config::dirBase(), 'plantilla');

        $plantillaUrl = null;
        if ($plantillaPath !== null) {
            $plantillaUrl = $this->plantillaUrl('wizard', $token, $plantillaPath);
        } elseif ($plantillaGlobalPath !== null) {
            $plantillaUrl = $this->plantillaUrl('global', 'global', $plantillaGlobalPath);
        }

        // --- MANEJO DE FIRMA CON PREVENCIÓN DE CACHÉ ---
        $firmaUrl = null;
        $firmaPath = $config::rutaWizardFirma($token);
        if ($firmaPath !== null) {
            $firmaUrl = $this->firmaUrl('wizard', $token, $firmaPath);
        }

        // Coordenadas del wizard (base → defaults → coords.json del token):
        // al volver al paso 4 se retoma la calibración, nunca de cero.
        $coords = \Modules\Taller\Services\CertificadoConfig::coordsBase();

        $defaultsPath = \Modules\Taller\Services\CertificadoConfig::rutaDefaults();
        if (is_file($defaultsPath)) {
            $defaults = json_decode((string) @file_get_contents($defaultsPath), true);
            if (is_array($defaults)) {
                $coords = array_merge($coords, $defaults);
            }
        }

        $coordsPath = \Modules\Taller\Services\CertificadoConfig::dirWizard($token).'/coords.json';
        if (is_file($coordsPath)) {
            $savedCoords = json_decode((string) @file_get_contents($coordsPath), true);
            if (is_array($savedCoords)) {
                $coords = array_merge($coords, $savedCoords);
            }
        }

        $datosFusionados = array_merge($payload['paso1'] ?? [], $payload['paso2'] ?? []);
        $curso = (object) [
            'codigo' => $this->generarCodigoPreview($datosFusionados),
            'crypt_id' => '',
            'nombre' => $payload['paso1']['nombre'] ?? '',
        ];

        return view('taller::a.CursoPlantillaCrear', compact('curso', 'plantillaUrl', 'coords', 'firmaUrl'))
            ->with('esWizard', true)
            ->with('pasoActual', 4)
            ->with('borrador', $borrador)
            ->with('completados', $this->pasosCompletados($borrador));
    }

    /**
     * Paso 4: Guarda la plantilla/firma/coordenadas en el directorio temporal.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function wizardPlantillaStore(Request $request)
    {
        if (! hasPermissionRoute('taller.cursos.index', SecurityAction::CREAR_CURSO)) {
            abort(403, 'No tienes permiso para planificar cursos.');
        }

        $servicio = app(\Modules\Taller\Services\BorradorWizardService::class);
        $borrador = $servicio->actual(Auth::id());
        $payload = is_array($borrador->payload) ? $borrador->payload : [];

        if (empty($payload['paso1']) || empty($payload['paso2'])) {
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'error' => 'Completa primero los pasos previos.'], 422);
            }

            return redirect()->route('taller.cursos.wizard.paso1.create')
                ->withErrors(['error' => 'Completa los pasos previos de identidad y logística.']);
        }

        $servicio->sincronizarSesion($borrador);
        $token = $borrador->token;

        // Autoguardado del editor (fetch JSON): solo coordenadas, sin
        // redirect. Los archivos se suben con el submit clásico.
        if ($request->wantsJson()) {
            $coords = json_decode((string) $request->input('coords'), true);
            $errorCoords = \Modules\Taller\Services\CertificadoConfig::validarCoords($coords);
            if ($errorCoords !== null) {
                return response()->json(['ok' => false, 'error' => $errorCoords], 422);
            }

            $wizardDir = storage_path('app/public/Certificados/wizard/'.$token);
            if (! file_exists($wizardDir)) {
                mkdir($wizardDir, 0755, true);
            }
            file_put_contents($wizardDir.'/coords.json', json_encode($coords));

            return response()->json(['ok' => true]);
        }

        $redireccion = $this->validarImagenesCertificado($request);
        if ($redireccion) {
            return $redireccion;
        }

        $wizardDir = storage_path('app/public/Certificados/wizard/'.$token);
        if (! file_exists($wizardDir)) {
            mkdir($wizardDir, 0755, true);
        }

        // Guardar plantilla con su extensión real en minúsculas (nunca ".png" forzado
        // ni mayúsculas del nombre original: así siempre se vuelve a encontrar).
        if ($request->hasFile('plantilla')) {
            $mime = (string) $request->file('plantilla')->getMimeType();
            $extension = $mime === 'image/png' ? 'png' : 'jpg';
            $this->eliminarVariantes($wizardDir.'/plantilla.', $extension);
            $request->file('plantilla')->move($wizardDir, 'plantilla.'.$extension);
        }

        // Guardar firma con su extensión real en minúsculas (MIME, no la que diga el nombre).
        if ($request->hasFile('firma')) {
            $mime = (string) $request->file('firma')->getMimeType();
            $extension = $mime === 'image/png' ? 'png' : 'jpg';
            // Eliminar firmas previas con otras extensiones para evitar duplicidad de firmas
            $this->eliminarVariantes($wizardDir.'/firma.', $extension);
            $request->file('firma')->move($wizardDir, 'firma.'.$extension);
        }

        // Guardar coordenadas si se enviaron (validadas contra la hoja A4).
        if ($request->filled('coords')) {
            $coords = json_decode((string) $request->coords, true);
            $errorCoords = \Modules\Taller\Services\CertificadoConfig::validarCoords($coords);
            if ($errorCoords !== null) {
                return back()->withInput()->with('error', $errorCoords);
            }

            file_put_contents($wizardDir.'/coords.json', $request->coords);

            // Guardar como predeterminado global: solo gestión (son coordenadas de la institución).
            if ($request->has('guardar_default')) {
                if (! hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO)) {
                    Log::warning('Intento de guardar coordenadas globales sin permiso (wizard)', [
                        'usuario' => Auth::id(),
                    ]);
                } else {
                    $defaultsDir = storage_path('app/public/Certificados');
                    if (! file_exists($defaultsDir)) {
                        mkdir($defaultsDir, 0755, true);
                    }
                    file_put_contents($defaultsDir.'/defaults.json', $request->coords);
                }
            }
        }

        if ($request->input('accion') === 'salir' || $request->filled('guardar_y_salir')) {
            return redirect()->route('taller.cursos.index')
                ->with('success', 'Borrador guardado exitosamente.');
        }

        return redirect()->route('taller.cursos.wizard.requisitos.create')
            ->with('success', 'Configuración de certificado guardada. Ahora define los requisitos de la actividad.');
    }

    /**
     * Finaliza el wizard: crea el curso en BD (POR_ACEPTAR) y mueve los
     * archivos temporales a su ubicación definitiva.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function finalizar(Request $request)
    {
        if (! hasPermissionRoute('taller.cursos.index', SecurityAction::CREAR_CURSO)) {
            abort(403, 'No tienes permiso para planificar cursos.');
        }

        $servicioBorrador = app(\Modules\Taller\Services\BorradorWizardService::class);
        $borrador = $servicioBorrador->porToken(Auth::id(), (string) (session('wizard_curso.token') ?? ''))
            ?? $servicioBorrador->actual(Auth::id());
        $payloadBorrador = is_array($borrador->payload) ? $borrador->payload : [];

        // Pre-flight: re-valida el borrador (puede ser viejo o manipulado).
        // No se confía en empty() ni en la sesión; se reutilizan las rules
        // de StorePaso1/2/3 + requisitos.
        try {
            $validado = app(\Modules\Taller\Services\CursoBorradorValidator::class)
                ->validarParaFinalizar($payloadBorrador);
        } catch (ValidationException $e) {
            return redirect()->route('taller.cursos.wizard.paso1.create')
                ->withErrors($e->errors());
        }
        $validatedData = $validado['datos'];
        $requisitosValidados = $validado['requisitos'];

        // La carta de permiso es obligatoria solo en cursos dirigidos a
        // menores (niños y adolescentes). El paso 5 ya la precarga según el
        // público objetivo, pero se reafirma aquí para cubrir borradores
        // viejos o payloads manipulados.
        $esParaMenores = \Modules\Taller\Entities\ModalidadEspecial::esMenor(
            (int) ($validatedData['id_modalidad_especial'] ?? 0)
        );

        if (! $esParaMenores) {
            $requisitosValidados = array_values(array_filter(
                $requisitosValidados,
                fn ($req) => ! CursoRequisito::esCartaPermisoDocumento($req)
            ));
        } else {
            if (! collect($requisitosValidados)->contains(fn ($req) => CursoRequisito::esCartaPermisoDocumento($req))) {
                $requisitosValidados[] = CursoRequisito::requisitoCartaPermiso();
            }

            $requisitosValidados = array_map(function ($req) {
                if (CursoRequisito::esCartaPermisoDocumento($req)) {
                    $req['obligatorio'] = true;
                }

                return $req;
            }, $requisitosValidados);
        }

        $wizard = [
            'token' => $borrador->token,
            'datos' => $validatedData,
            'requisitos' => $requisitosValidados,
        ];

        // A partir del commit la BD ya no admite reversión: si algo falla
        // después, el catch no debe compensar un curso que sí se llegó a
        // guardar, porque el resultado sería un curso sin sus archivos de
        // certificado y un mensaje de error que mentiría al usuario.
        $committeado = false;
        $curso = null;
        $estadoCertificado = null;
        $intentos = 0;

        // Crear el curso (El código PROCINEC se genera automáticamente vía Observer).
        // El correlativo se calculó como max+1 al abrir el formulario: si dos
        // coordinadores crean a la vez, el UNIQUE de codigo rechaza al segundo.
        // Cada intento usa su propia transacción: en Postgres una violación
        // 23505 aborta la tx y el reintento dentro de la misma nunca funciona.
        $cursoData = [
            'nombre' => $validatedData['nombre'],
            'id_modalidad' => $validatedData['id_modalidad'],
            'id_actividad_formativa' => $validatedData['id_actividad_formativa'],
            'id_aspecto' => $validatedData['id_aspecto'] ?? null,
            'id_modalidad_especial' => $validatedData['id_modalidad_especial'] ?? null,
            'id_persona' => $validatedData['id_persona'],
            'nivel' => $validatedData['nivel'] ?? null,
            'trimestre' => $validatedData['trimestre'],
            'correlativo' => $validatedData['correlativo'],
            'anio' => $validatedData['anio'],
            'descripcion' => $validatedData['descripcion'] ?? null,
            'duracion' => $validatedData['duracion'] ?? null,
            'horas' => $validatedData['horas'] ?? null,
            'cantidad_cupos' => $validatedData['cantidad_cupos'] ?? null,
            'telegram' => $validatedData['telegram'] ?? null,
            'direccion_corta' => $validatedData['direccion_corta'],
            'es_nacional' => ! empty($validatedData['es_nacional']),
            'fecha_inicio' => $validatedData['fecha_inicio'],
            'fecha_fin' => $validatedData['fecha_fin'],
            'creado_por' => Auth::id(),
            'creado_en' => now(),
        ];

        while (true) {
            try {
                DB::beginTransaction();

                $curso = Curso::create($cursoData);

                // Sincronizar múltiples localidades (Estados)
                if (! empty($cursoData['es_nacional'])) {
                    $curso->localidades()->sync([]);
                } elseif (isset($validatedData['localidades'])) {
                    $curso->localidades()->sync($validatedData['localidades']);
                }

                // Asignar estado inicial: Por Aceptar (completó los 3 pasos)
                $curso->agregarEstado(EstadoCurso::POR_ACEPTAR->value);

                Log::info('Curso creado', ['curso_id' => $curso->id_curso, 'creado_por' => Auth::id()]);

                // Guardar contenidos si existen (temario de creación: nunca evaluaciones).
                if (isset($validatedData['contenidos']) && is_array($validatedData['contenidos'])) {
                    foreach ($validatedData['contenidos'] as $contenidoData) {
                        $dataToSave = [
                            'titulo' => $contenidoData['titulo'],
                            'descripcion' => $contenidoData['descripcion_breve'] ?? '',
                            'descripcion_breve' => $contenidoData['descripcion_breve'] ?? '',
                            'url_contenido' => $contenidoData['url_contenido'] ?? null,
                            'fecha_contenido' => $contenidoData['fecha_contenido'] ?? null,
                            'hora_inicio' => $contenidoData['hora_inicio'] ?? null,
                            'hora_fin' => $contenidoData['hora_fin'] ?? null,
                            'es_evaluacion' => false,
                            'id_tipo_evaluacion' => null,
                            'ponderacion' => 0,
                            'id_curso' => $curso->id_curso,
                            'creado_por' => Auth::id(),
                        ];

                        ContenidoCurso::create($dataToSave);
                    }
                }

                // Guardar requisitos ya re-validados en el pre-flight.
                if (! empty($requisitosValidados)) {
                    foreach ($requisitosValidados as $req) {
                        CursoRequisito::create([
                            'id_curso' => $curso->id_curso,
                            'tipo' => $req['tipo'],
                            'titulo' => $req['titulo'],
                            'descripcion' => $req['descripcion'] ?? null,
                            'obligatorio' => isset($req['obligatorio']) ? (bool) $req['obligatorio'] : true,
                        ]);
                    }
                }

                // Guardar foto de presentación en su ubicación definitiva
                $rutaImagen = $this->moverArchivosFoto($wizard['token'], $curso->id_curso);
                if ($rutaImagen) {
                    $curso->update(['imagen' => $rutaImagen]);
                }

                // Copiar los archivos temporales a la carpeta definitiva del curso.
                // OJO: aquí NO se borra el wizard. Si el commit falla, el catch
                // revierte la BD y necesita los originales para compensar.
                $this->copiarArchivosPlantilla($wizard['token'], $curso->id_curso);

                // Se lee el estado del certificado con los archivos ya en su carpeta
                // definitiva. Omitir la calibración es una vía legítima del wizard
                // ("Omitir calibración"), y ahí es donde nace un curso sin
                // certificado: sin este dato el guardado se celebraría en silencio.
                $estadoCertificado = $curso->estadoCertificado();

                DB::commit();
                $committeado = true;

                // El curso ya está confirmado en la BD: ahora sí se descarta el temporal.
                // Todo lo que sigue es limpieza y no admite compensación.
                $this->eliminarDirectorio($this->wizardCertificadosDir($wizard['token']));

                // Limpiar el borrador del usuario y la sesión del wizard
                $servicioBorrador->eliminar($borrador);

                // El curso ya está guardado: lo que sigue solo informa. Si quedó sin
                // certificado, el aviso tiene que decirlo en vez de celebrar un
                // guardado que el usuario va a descubrir incompleto más tarde.
                if ($estadoCertificado !== EstadoCertificado::CONFIGURADO) {
                    return redirect()->route('taller.cursos.index')
                        ->with('warning', $this->avisoCertificadoIncompleto($curso, $estadoCertificado));
                }

                return redirect()->route('taller.cursos.index')
                    ->with('success', 'Curso "'.$curso->nombre.'" y sus requisitos configurados exitosamente.');

            } catch (\Illuminate\Database\QueryException $e) {
                if ($committeado) {
                    // El curso ya está en la BD y solo falló la limpieza de
                    // temporales. No se revierte ni se borran sus archivos: se
                    // informa con un único aviso, que no contradice al éxito.
                    Log::warning('Curso creado; falló solo la limpieza de temporales', [
                        'mensaje' => $e->getMessage(),
                        'clase' => get_class($e),
                        'archivo' => $e->getFile().':'.$e->getLine(),
                        'id_curso' => $curso->id_curso ?? null,
                        'token_wizard' => $wizard['token'] ?? null,
                    ]);

                    return redirect()->route('taller.cursos.index')
                        ->with('warning', 'El curso "'.($curso->nombre ?? '').'" y sus requisitos se guardaron correctamente, pero no se pudo limpiar una carpeta temporal del certificado. No afecta al curso.');
                }

                $this->revertirTransaccion();

                // DB::rollBack() solo revierte filas. Las copias que se hubieran hecho
                // para este id_curso quedarían huérfanas, así que se limpian aquí.
                if (isset($curso) && $curso instanceof Curso && $curso->id_curso) {
                    $this->eliminarArchivosPlantillaCurso($curso->id_curso);
                    $this->eliminarFotoCurso($curso->id_curso);
                }

                // Colisión de código PROCINEC: la tx ya se revirtió, el siguiente
                // intento arranca una tx fresca (en PG reintentar dentro de la
                // misma tx abortada nunca funciona).
                if (self::esCodigoDuplicado($e) && ++$intentos < 3) {
                    $cursoData['correlativo'] = (int) $cursoData['correlativo'] + 1;
                    $curso = null;

                    Log::warning('Colisión de código PROCINEC al crear curso, reintentando', [
                        'intento' => $intentos,
                        'correlativo' => $cursoData['correlativo'],
                    ]);

                    continue;
                }

                Log::error('Error al crear curso', [
                    'mensaje' => $e->getMessage(),
                    'clase' => get_class($e),
                    'archivo' => $e->getFile().':'.$e->getLine(),
                    'id_curso_parcial' => $curso->id_curso ?? null,
                    'token_wizard' => $wizard['token'] ?? null,
                ]);

                // El detalle técnico solo va al log: un getMessage() de Postgres
                // expone nombres de tabla y columna al usuario.
                return back()
                    ->withErrors(['error' => 'Ocurrió un error al crear el curso y no se guardó nada. Revisa los datos e inténtalo de nuevo.']);
            } catch (\Throwable $e) {
                if ($committeado) {
                    Log::warning('Curso creado; falló solo la limpieza de temporales', [
                        'mensaje' => $e->getMessage(),
                        'clase' => get_class($e),
                        'archivo' => $e->getFile().':'.$e->getLine(),
                        'id_curso' => $curso->id_curso ?? null,
                        'token_wizard' => $wizard['token'] ?? null,
                    ]);

                    return redirect()->route('taller.cursos.index')
                        ->with('warning', 'El curso "'.($curso->nombre ?? '').'" y sus requisitos se guardaron correctamente, pero no se pudo limpiar una carpeta temporal del certificado. No afecta al curso.');
                }

                $this->revertirTransaccion();

                if (isset($curso) && $curso instanceof Curso && $curso->id_curso) {
                    $this->eliminarArchivosPlantillaCurso($curso->id_curso);
                    $this->eliminarFotoCurso($curso->id_curso);
                }

                Log::error('Error al crear curso', [
                    'mensaje' => $e->getMessage(),
                    'clase' => get_class($e),
                    'archivo' => $e->getFile().':'.$e->getLine(),
                    'id_curso_parcial' => $curso->id_curso ?? null,
                    'token_wizard' => $wizard['token'] ?? null,
                ]);

                return back()
                    ->withErrors(['error' => 'Ocurrió un error al crear el curso y no se guardó nada. Revisa los datos e inténtalo de nuevo.']);
            }
        } // while: reintento con tx fresca ante colisión uq_cursos_codigo.
    }

    /**
     * Muestra el formulario para cargar la plantilla del certificado (curso ya existente)
     *
     * @param  int|string  $id_curso
     * @return \Illuminate\View\View
     */
    /**
     * Autoriza el acceso al editor de plantilla/firma de un curso existente.
     *
     * Única fuente de verdad: CondicionalEstadoCurso::puedeAjustarCertificado().
     * La plantilla y la firma son documentos institucionales, solo gestión.
     */
    private function autorizarAjusteCertificado(Curso $curso): void
    {
        $esGestor = hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO);
        $estadoId = $curso->estado_actual->id_estado ?? 0;

        if (! app(CondicionalEstadoCurso::class)->puedeAjustarCertificado($estadoId, $esGestor)) {
            abort(403, 'No tienes permiso para ajustar el certificado de este curso.');
        }
    }

    public function plantillaCreate($id_curso)
    {
        $curso = Curso::findOrFail($id_curso);
        $this->autorizarAjusteCertificado($curso);

        // --- MANEJO DE IMAGEN DE PLANTILLA CON PREVENCIÓN DE CACHÉ ---
        $plantillaPath = \Modules\Taller\Services\CertificadoConfig::rutaPlantilla($curso->id_curso);
        $esGlobal = $plantillaPath !== null && ! str_contains($plantillaPath, '/cursos/');

        $plantillaUrl = null;
        if ($plantillaPath !== null && ! $esGlobal) {
            // Agregamos ?v=timestamp para obligar al navegador a refrescar la imagen
            $plantillaUrl = $this->plantillaUrl('curso', $curso->id_curso, $plantillaPath);
        } elseif ($plantillaPath !== null) {
            $plantillaUrl = $this->plantillaUrl('global', 'global', $plantillaPath);
        }

        // Coordenadas guardadas del curso (base → defaults → {id}.json): el
        // editor abre con la calibración actual, nunca de cero.
        $coords = \Modules\Taller\Services\CertificadoConfig::coordsPara($curso->id_curso);

        // Firma guardada actualmente (cualquier extensión, sin distinguir mayúsculas).
        $firmaUrl = null;
        $firmaPath = \Modules\Taller\Services\CertificadoConfig::rutaFirma($curso->id_curso);
        if ($firmaPath !== null) {
            $firmaUrl = $this->firmaUrl('curso', $curso->id_curso, $firmaPath);
        }

        return view('taller::a.CursoPlantillaCrear', compact('curso', 'plantillaUrl', 'coords', 'firmaUrl'))
            ->with('esWizard', false);
    }

    /**
     * Almacena la plantilla del certificado de un curso ya existente (Paso 2)
     *
     * @param  int|string  $id_curso
     * @return \Illuminate\Http\RedirectResponse
     */
    public function plantillaStore(Request $request, $id_curso)
    {
        $curso = Curso::findOrFail($id_curso);
        $this->autorizarAjusteCertificado($curso);

        $redireccion = $this->validarImagenesCertificado($request);
        if ($redireccion) {
            return $redireccion;
        }

        $imagenes = app(\Modules\Taller\Services\ImagenCertificadoService::class);

        try {
            if ($request->hasFile('plantilla')) {
                $imagenes->guardarPlantilla($request->file('plantilla'), $curso->id_curso);
            }

            if ($request->hasFile('firma')) {
                $imagenes->guardarFirma($request->file('firma'), $curso->id_curso);
            }
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $destinationPath = storage_path('app/public/Certificados/cursos');

        // Guardar coordenadas si se enviaron (validadas contra la hoja A4).
        if ($request->filled('coords')) {
            $coords = json_decode((string) $request->coords, true);
            $errorCoords = \Modules\Taller\Services\CertificadoConfig::validarCoords($coords);
            if ($errorCoords !== null) {
                return back()->withInput()->with('error', $errorCoords);
            }

            // Si la plantilla se auto-ajustó a A4, las posiciones calibradas
            // sobre la vista previa se recorren al lienzo con márgenes.
            if ($request->hasFile('plantilla') && $imagenes->fueAjustada()) {
                $coords = $imagenes->coordsAjustadas($coords);
            }

            file_put_contents($destinationPath.'/'.$curso->id_curso.'.json', json_encode($coords));

            // Guardar como predeterminado global: solo gestión (son coordenadas de la institución).
            // Se guarda la versión ya recorrida si hubo auto-ajuste de la plantilla.
            if ($request->has('guardar_default')) {
                if (! hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO)) {
                    Log::warning('Intento de guardar coordenadas globales sin permiso', [
                        'id_curso' => $curso->id_curso,
                        'usuario' => Auth::id(),
                    ]);
                } else {
                    $defaultsDir = storage_path('app/public/Certificados');
                    if (! file_exists($defaultsDir)) {
                        mkdir($defaultsDir, 0755, true);
                    }
                    file_put_contents($defaultsDir.'/defaults.json', json_encode($coords));
                }
            }
        }

        $mensaje = 'Certificado actualizado exitosamente.';
        if ($imagenes->fueAjustada() && $imagenes->detalleAjuste() !== null) {
            $mensaje .= ' '.$imagenes->detalleAjuste();
        }

        // "Probar Emisión" envía el formulario para guardar las imágenes y
        // luego abre el PDF. Antes era un enlace GET que leía solo lo ya
        // guardado: si se habían elegido archivos sin guardar, no aparecían.
        if ($request->boolean('ir_a_prueba')) {
            return redirect()->route('taller.certificados.probar', $curso->crypt_id);
        }

        return redirect()->route('taller.cursos.show', $curso->id_curso)
            ->with('success', $mensaje);
    }

    /**
     * Mensaje de aviso para un curso que se guardó sin certificado completo.
     *
     * Se usa 'warning' en vez de 'success' porque el layout dispara Swal.fire()
     * una vez por cada flash y el último pisa al anterior: mandarlos juntos
     * mostraría solo el de éxito. El texto confirma igualmente que el curso
     * quedó guardado, para que el aviso no se lea como un fallo.
     *
     * El mensaje se arma en cuatro frases y una sola transición: se guardó, qué
     * queda pendiente, cómo saldrá el PDF y dónde se resuelve. Encadenar varios
     * "pero" en la misma oración vuelve el texto incoherente, y el PDF sale
     * distinto según lo que falte, así que cada estado necesita el suyo.
     *
     * @return string
     */
    private function avisoCertificadoIncompleto(Curso $curso, EstadoCertificado $estado)
    {
        [$pendiente, $comoSaldra] = match ($estado) {
            EstadoCertificado::SIN_PLANTILLA => [
                'la imagen de fondo del certificado',
                'No hay plantilla propia ni global, así que se usará el diseño por defecto del sistema',
            ],
            EstadoCertificado::FALTA_FIRMA => [
                'la firma de la autoridad',
                'La plantilla configurada sí se usará, pero el certificado saldrá sin firma',
            ],
        };

        return 'El curso "'.$curso->nombre.'" y sus requisitos se guardaron correctamente. '
            .'Queda pendiente '.$pendiente.'. '.$comoSaldra.'. '
            .'Puedes completarlo desde la ficha del curso, con "Ajustar Certificado".';
    }

    /**
     * Valida la plantilla y la firma del certificado.
     *
     * En lugar de dejar que Laravel lance la ValidationException (que produce la
     * pantalla de error), se captura el primer mensaje y se devuelve un flash
     * 'error', que el layout convierte en un Swal con el texto exacto.
     *
     * @return \Illuminate\Http\RedirectResponse|null null si todo es válido
     */
    private function validarImagenesCertificado(Request $request)
    {
        try {
            $request->validate([
                'plantilla' => ['nullable', 'image', 'mimes:png,jpg,jpeg', ImagenCertificado::plantilla()],
                'firma' => ['nullable', 'image', 'mimes:png,jpg,jpeg', ImagenCertificado::firma()],
                'coords' => 'nullable|json',
                'guardar_default' => 'nullable',
            ]);
        } catch (ValidationException $e) {
            $mensaje = collect($e->errors())
                ->flatten()
                ->filter()
                ->first() ?: 'No pudimos procesar las imágenes del certificado.';

            return back()->withInput()->with('error', $mensaje);
        }

        return null;
    }

    /**
     * URL de la plantilla del certificado, servida por la aplicación.
     *
     * Antes se armaba con asset('storage/...'), lo que exigía que existiera el
     * enlace simbólico public/storage. Con el bind mount de docker-compose ese
     * enlace se crea en el host y no se resuelve dentro del contenedor, así que
     * la imagen no cargaba y el editor calibraba sobre un lienzo en blanco.
     *
     * @param  string  $tipo  'global', 'curso' o 'wizard'
     * @param  string  $ref  id del curso o token del wizard
     * @param  string|null  $cacheBustFrom  Archivo cuyo mtime se usa para invalidar caché
     * @return string
     */
    private function plantillaUrl($tipo, $ref, $cacheBustFrom = null)
    {
        $url = $tipo === 'global'
            ? route('taller.certificados.plantilla.global')
            : route('taller.certificados.plantilla.ver', ['tipo' => $tipo, 'ref' => $ref]);

        if ($cacheBustFrom && file_exists($cacheBustFrom)) {
            $url .= '?v='.filemtime($cacheBustFrom);
        }

        return $url;
    }

    /**
     * URL de la firma del certificado, servida por la aplicación.
     *
     * @param  string  $tipo  'curso' o 'wizard'
     * @param  string  $ref  id del curso o token del wizard
     * @param  string|null  $cacheBustFrom  Archivo cuyo mtime se usa para invalidar caché
     * @return string
     */
    private function firmaUrl($tipo, $ref, $cacheBustFrom = null)
    {
        $url = route('taller.certificados.firma.ver', ['tipo' => $tipo, 'ref' => $ref]);

        if ($cacheBustFrom && file_exists($cacheBustFrom)) {
            $url .= '?v='.filemtime($cacheBustFrom);
        }

        return $url;
    }

    /**
     * Sirve la foto de portada del wizard de forma segura sin depender del symlink public/storage
     * ni caer en la trampa de estáticos de Nginx (location ~* \.(png|jpg)$).
     *
     * @return \Illuminate\Http\Response
     */
    public function verFotoWizard(string $token)
    {
        $this->exigirPermisoCrear();

        $fotoPath = $this->resolverFotoWizardRuta($token);
        if (! $fotoPath || ! is_file($fotoPath)) {
            abort(404);
        }

        $baseDir = realpath(storage_path('app/public/Certificados/wizard'));
        $realPath = realpath($fotoPath);
        if ($baseDir === false || $realPath === false || ! str_starts_with($realPath, $baseDir.DIRECTORY_SEPARATOR)) {
            abort(404);
        }

        $mime = \Illuminate\Support\Facades\File::mimeType($realPath);
        if (! in_array($mime, ['image/png', 'image/jpeg'], true)) {
            abort(404);
        }

        $file = \Illuminate\Support\Facades\File::get($realPath);
        $lastModified = filemtime($realPath);
        $etag = '"'.md5($file).'"';

        if (trim((string) request()->header('If-None-Match')) === $etag) {
            return response(null, 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'public, max-age=86400');
        }

        return response($file, 200)
            ->header('Content-Type', $mime)
            ->header('ETag', $etag)
            ->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified).' GMT')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    /**
     * Resuelve la URL pública (ruta controlada sin extensión) para la foto del wizard con cache-busting.
     */
    private function resolverFotoWizardUrl($borrador): ?string
    {
        $fotoPath = $this->resolverFotoWizardRuta($borrador->token, $borrador->foto_path);
        if (! $fotoPath) {
            return null;
        }

        return route('taller.cursos.wizard.foto', ['token' => $borrador->token])
            .'?v='.filemtime($fotoPath);
    }

    /**
     * Resuelve la ruta física del archivo de foto de un wizard.
     */
    private function resolverFotoWizardRuta(string $token, ?string $fotoPathRelativa = null): ?string
    {
        $wizardDir = storage_path('app/public/Certificados/wizard/'.$token);
        foreach (['png', 'jpg', 'jpeg'] as $ext) {
            $p = $wizardDir.'/foto.'.$ext;
            if (is_file($p)) {
                return $p;
            }
        }

        if (! empty($fotoPathRelativa)) {
            $p = storage_path('app/public/Certificados/'.$fotoPathRelativa);
            if (is_file($p)) {
                return $p;
            }
        }

        return null;
    }

    /**
     * Genera una vista previa del código PROCINEC que se aplicará al crear el curso.
     *
     * Usa los mapas id→abreviatura de los catálogos cacheados: cero queries
     * extra por render (antes eran hasta 4 finds por llamada).
     *
     * @return string
     */
    private function generarCodigoPreview(array $datos)
    {
        $catalogos = $this->catalogosPaso1();
        $mapa = function ($coleccion, $llave) {
            return $coleccion->pluck('abreviatura', $llave);
        };

        $actividad = $mapa($catalogos['actividades'], 'id_actividad_formativa')[$datos['id_actividad_formativa'] ?? 0] ?? '';
        $aspecto = $mapa($catalogos['aspectos'], 'id_aspecto')[$datos['id_aspecto'] ?? 0] ?? '';
        $modEsp = $mapa($catalogos['modalidadesEspeciales'], 'id_modalidad_especial')[$datos['id_modalidad_especial'] ?? 0] ?? '';
        $modalidad = $mapa($catalogos['modalidades'], 'id_modalidad')[$datos['id_modalidad'] ?? 0] ?? '';
        $trimestre = $datos['trimestre'] ?? '';
        $correlativo = $datos['correlativo'] ?? '0';
        $anio = $datos['anio'] ?? date('Y');

        $middle = $actividad.$trimestre.$correlativo.$aspecto.$modalidad.$modEsp;

        return strtoupper('LAB-'.$middle.'-'.$anio);
    }

    /**
     * Directorio temporal de un wizard de certificado.
     *
     * @param  string  $token
     * @return string
     */
    private function wizardCertificadosDir($token)
    {
        return storage_path('app/public/Certificados/wizard/'.$token);
    }

    /**
     * Copia los archivos temporales del wizard a la carpeta definitiva del curso.
     *
     * No borra el temporal: esa limpieza ocurre en finalizar() después de
     * DB::commit(), para que un fallo no destruya lo que el usuario subió.
     *
     * @param  string  $token
     * @param  int  $id_curso
     * @return void
     */
    /**
     * Elimina las variantes de un archivo con otras extensiones, sin
     * distinguir mayúsculas (restos de subidas antiguas como `firma.JPG`).
     */
    private function eliminarVariantes($prefijoSinExtension, $conservarExtension)
    {
        foreach (glob($prefijoSinExtension.'*') ?: [] as $ruta) {
            if (is_file($ruta) && strtolower((string) pathinfo($ruta, PATHINFO_EXTENSION)) !== strtolower($conservarExtension)) {
                @unlink($ruta);
            }
        }
    }

    private function copiarArchivosPlantilla($token, $id_curso)
    {
        $wizardDir = $this->wizardCertificadosDir($token);
        $destPath = storage_path('app/public/Certificados/cursos');
        if (! file_exists($destPath)) {
            mkdir($destPath, 0755, true);
        }

        $config = \Modules\Taller\Services\CertificadoConfig::class;

        // Se copian tal cual estén (incluso con mayúsculas heredadas) pero el
        // destino siempre queda en minúsculas para que todo lo encuentre.
        $plantilla = $config::buscarPrimero($wizardDir, 'plantilla');
        if ($plantilla !== null) {
            $extension = strtolower((string) pathinfo($plantilla, PATHINFO_EXTENSION));
            $this->copiarArchivo($plantilla, $destPath.'/'.$id_curso.'.'.$extension);
        }

        $firma = $config::buscarPrimero($wizardDir, 'firma');
        if ($firma !== null) {
            $extension = strtolower((string) pathinfo($firma, PATHINFO_EXTENSION));
            $this->copiarArchivo($firma, $destPath.'/'.$id_curso.'_firma.'.$extension);
        }

        if (file_exists($wizardDir.'/coords.json')) {
            $this->copiarArchivo($wizardDir.'/coords.json', $destPath.'/'.$id_curso.'.json');
        }
    }

    /**
     * Elimina los archivos de certificado que pertenecen a un curso.
     *
     * Se usa para compensar un rollback: si el curso no llegó a existir, sus
     * archivos tampoco deben quedarse huérfanos en disco.
     *
     * @param  int  $id_curso
     * @return void
     */
    private function eliminarArchivosPlantillaCurso($id_curso)
    {
        $destPath = storage_path('app/public/Certificados/cursos');

        // Glob en vez de lista fija: también atrapa restos con mayúsculas
        // (`7_firma.JPG`) de subidas anteriores a la normalización.
        foreach (["$destPath/$id_curso.*", "$destPath/{$id_curso}_firma.*"] as $patron) {
            foreach (glob($patron) ?: [] as $ruta) {
                if (is_file($ruta)) {
                    @unlink($ruta);
                }
            }
        }
    }

    /**
     * Elimina la foto de presentación de un curso.
     *
     * @param  int  $id_curso
     * @return void
     */
    private function eliminarFotoCurso($id_curso)
    {
        $destDir = public_path('imagenes/cursos');

        foreach (['png', 'jpg', 'jpeg'] as $ext) {
            $ruta = "$destDir/$id_curso.$ext";
            if (is_file($ruta)) {
                @unlink($ruta);
            }
        }
    }

    /**
     * Copia un archivo verificando que la copia exista y pese lo mismo.
     *
     * copy() devuelve false sin lanzar excepción si el destino no es escribible.
     * Sin esta comprobación, el original se borraba igual y el curso quedaba sin
     * plantilla sin ningún aviso.
     *
     * @throws \RuntimeException
     */
    private function copiarArchivo($origen, $destino)
    {
        if (! @copy($origen, $destino)) {
            throw new \RuntimeException('No se pudo guardar el archivo del certificado en '.basename($destino).'. Revisa los permisos de escritura.');
        }

        clearstatcache();
        if (! is_file($destino) || filesize($destino) !== filesize($origen)) {
            @unlink($destino);
            throw new \RuntimeException('La copia de '.basename($destino).' quedó incompleta.');
        }
    }

    /**
     * Detecta violación del UNIQUE uq_cursos_codigo (Postgres 23505).
     *
     * El reintento vive en finalizar() con una transacción fresca por
     * intento: reintentar dentro de la misma tx abortada nunca funciona.
     */
    private static function esCodigoDuplicado(\Illuminate\Database\QueryException $e): bool
    {
        return $e->getCode() === '23505'
            || str_contains($e->getMessage(), 'uq_cursos_codigo');
    }

    /**
     * Revierte la transacción solo si sigue abierta.
     *
     * DB::rollBack() sin transacción activa lanza una PDOException que
     * enmascararía el error original.
     *
     * @return void
     */
    private function revertirTransaccion()
    {
        try {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        } catch (\Throwable $e) {
            Log::error('No se pudo revertir la transacción al crear el curso', [
                'mensaje' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mueve la foto de presentación del wizard a la carpeta pública definitiva.
     *
     * @param  string  $token
     * @param  int  $id_curso
     * @return string|null Ruta pública relativa, o null si no hay foto
     */
    private function moverArchivosFoto($token, $id_curso)
    {
        $wizardDir = $this->wizardCertificadosDir($token);
        $fotoPath = null;
        foreach (['png', 'jpg', 'jpeg'] as $ext) {
            if (file_exists($wizardDir.'/foto.'.$ext)) {
                $fotoPath = $wizardDir.'/foto.'.$ext;
                break;
            }
        }

        if (! $fotoPath) {
            return null;
        }

        $extension = strtolower((string) pathinfo($fotoPath, PATHINFO_EXTENSION));
        $destDir = public_path('imagenes/cursos');
        if (! file_exists($destDir)) {
            @mkdir($destDir, 0775, true);
        }

        foreach (['png', 'jpg', 'jpeg'] as $e) {
            if (strtolower($e) !== $extension) {
                @unlink($destDir.'/'.$id_curso.'.'.$e);
            }
        }

        $filename = $id_curso.'.'.$extension;
        $this->copiarArchivo($fotoPath, $destDir.'/'.$filename);
        @chmod($destDir.'/'.$filename, 0664);

        return 'imagenes/cursos/'.$filename;
    }

    /**
     * Elimina un directorio y todo su contenido recursivamente.
     *
     * Se niega a borrar cualquier cosa que no esté dentro de storage/, para que
     * un token mal formada no pueda acabar eliminando un directorio equivocado.
     *
     * @param  string  $dir
     * @return void
     */
    private function eliminarDirectorio($dir)
    {
        if (! is_dir($dir)) {
            return;
        }

        $real = realpath($dir);
        $base = realpath(storage_path());

        if ($real === false || $base === false || $real === $base || strpos($real, $base.DIRECTORY_SEPARATOR) !== 0) {
            Log::warning('Se evitó eliminar un directorio fuera de storage', ['ruta' => $dir]);

            return;
        }

        $items = glob($real.'/*');
        foreach ($items as $item) {
            if (is_dir($item)) {
                $this->eliminarDirectorio($item);
            } else {
                @unlink($item);
            }
        }
        @rmdir($real);
    }

    /**
     * Elimina los directorios temporales de wizards abandonados (más de 24h).
     *
     * @return void
     */
    private function limpiarTemporalesWizard()
    {
        $base = storage_path('app/public/Certificados/wizard');
        if (! file_exists($base)) {
            return;
        }
        $corte = now()->subHours(24)->timestamp;
        foreach (glob($base.'/*') as $dir) {
            if (is_dir($dir) && @filemtime($dir) < $corte) {
                $this->eliminarDirectorio($dir);
            }
        }
    }
}
