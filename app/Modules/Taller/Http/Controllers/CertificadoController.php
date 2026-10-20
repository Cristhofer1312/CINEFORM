<?php

namespace Modules\Taller\Http\Controllers;

use App\Enums\EstadoCurso;
use Illuminate\Http\Request;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Modules\Comun\Entities\PersonalData;
use Modules\Taller\Services\CertificadoService;
use App\Constants\SecurityAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Modules\Taller\Rules\ImagenCertificado;

class CertificadoController extends BaseController
{
    protected $certificadoService;

    public function __construct(CertificadoService $certificadoService)
    {
        $this->certificadoService = $certificadoService;
    }

    /**
     * Descarga el certificado para un participante autenticado.
     * 
     * @param int|string $id_curso ID del curso (puede venir encriptado por middleware decrypt_id)
     */
    public function descargar($id_curso)
    {
        $curso = Curso::findOrFail($id_curso);

        // 1. Validar estado del curso (Finalizado o Cerrado)
        if (!in_array($curso->estado_actual->id_estado ?? 0, [EstadoCurso::FINALIZADO->value, EstadoCurso::CERRADO->value])) {
            return redirect()->back()->with('error', 'El certificado aún no está disponible para este curso.');
        }

        // 2. Verificar que el usuario tenga una inscripción aprobada
        $user = Auth::user();
        $persona = $user->personalData;

        if (!$persona) {
            return redirect()->back()->with('error', 'No se encontraron datos personales asociados a su cuenta.');
        }

        $inscripcion = Inscripcion::where('id_curso', $curso->id_curso)
            ->where('id_persona', $persona->id_persona)
            ->where('estado', Inscripcion::ESTADO_APROBADO)
            ->first();

        if (!$inscripcion) {
            return redirect()->back()->with('error', 'No tiene una inscripción aprobada en este curso.');
        }

        // 3. Verificar que la certificación haya sido aprobada por el facilitador
        if (!$inscripcion->certificadoAprobado()) {
            return redirect()->back()->with('error', 'Tu certificación aún no ha sido aprobada por el facilitador.');
        }

        $satisfaccion = app(\Modules\Taller\Services\SatisfaccionCursoService::class);

        if ($satisfaccion->cursoPermiteEncuesta($curso) && !$satisfaccion->yaRespondio($inscripcion)) {
            return redirect()
                ->route('taller.cursos.satisfaccion.create', $curso->crypt_id)
                ->with('info', 'Responde la encuesta de satisfacción para descargar tu certificado.');
        }

        // 4. Generar PDF
        try {
            $pdfContent = $this->certificadoService->generarPdf($curso, $inscripcion);
            
            $nombreArchivo = 'Certificado_' . preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $curso->codigo) . '.pdf';

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar el certificado: ' . $e->getMessage());
        }
    }

    /**
     * Sirve la plantilla global de certificados.
     */
    public function verPlantillaGlobal()
    {
        return $this->servirImagen($this->rutaPlantilla('global', 'global'));
    }

    /**
     * Sirve la plantilla del certificado de un curso o de un wizard en curso.
     *
     * @param string $tipo  'curso' (id del curso) o 'wizard' (token del wizard)
     * @param string $ref   id del curso o token del wizard
     */
    public function verPlantilla($tipo, $ref)
    {
        return $this->servirImagen($this->rutaPlantilla($tipo, $ref));
    }

    /**
     * Sirve la firma del certificado de un curso o de un wizard en curso.
     *
     * @param string $tipo  'curso' (id del curso) o 'wizard' (token del wizard)
     * @param string $ref   id del curso o token del wizard
     */
    public function verFirma($tipo, $ref)
    {
        return $this->servirImagen($this->rutaFirma($tipo, $ref));
    }

    /**
     * Resuelve la ruta en disco de una plantilla a partir de tipo y referencia.
     *
     * La ruta nunca se toma de la petición: se construye con una lista blanca,
     * de modo que no hay forma de recorrer directorios ni pedir las coordenadas.
     *
     * @return string|null
     */
    private function rutaPlantilla($tipo, $ref)
    {
        $config = \Modules\Taller\Services\CertificadoConfig::class;

        return match ($tipo) {
            'global' => $config::buscarPrimero($config::dirBase(), 'plantilla'),
            'curso' => ctype_digit((string) $ref) ? $config::buscarPrimero($config::dirCursos(), (string) $ref) : null,
            'wizard' => $config::rutaWizardPlantilla((string) $ref),
            default => null,
        };
    }

    /**
     * Resuelve la ruta en disco de una firma probando las extensiones admitidas.
     *
     * @return string|null
     */
    private function rutaFirma($tipo, $ref)
    {
        $config = \Modules\Taller\Services\CertificadoConfig::class;

        return match ($tipo) {
            'curso' => ctype_digit((string) $ref) ? $config::rutaFirma($ref) : null,
            'wizard' => $config::rutaWizardFirma((string) $ref),
            default => null,
        };
    }

    /**
     * Devuelve una imagen del directorio de certificados con ETag y 304.
     *
     * Sustituye a asset('storage/...'), que dependía del enlace simbólico
     * public/storage. El Content-Type se deduce del contenido real y no de la
     * extensión, y la ruta se valida con realpath para que no se pueda salir
     * del directorio.
     */
    private function servirImagen($ruta)
    {
        if (!$ruta) {
            abort(404);
        }

        $baseDir = realpath(storage_path('app/public/Certificados'));
        $path = $ruta ? realpath($ruta) : false;

        if ($baseDir === false || $path === false || !is_file($path)
            || strpos($path, $baseDir . DIRECTORY_SEPARATOR) !== 0) {
            abort(404);
        }

        $mime = File::mimeType($path);
        if (!in_array($mime, ['image/png', 'image/jpeg'], true)) {
            abort(404);
        }

        $file = File::get($path);
        $etag = '"' . md5($file) . '"';
        $lastModified = File::lastModified($path);
        $cacheControl = 'public, max-age=86400';

        if (trim((string) request()->header('If-None-Match')) === $etag) {
            return response(null, 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', $cacheControl);
        }

        $since = request()->header('If-Modified-Since');
        if ($since && $lastModified <= Carbon::parse($since)->getTimestamp()) {
            return response(null, 304)
                ->header('ETag', $etag)
                ->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT')
                ->header('Cache-Control', $cacheControl);
        }

        return response($file, 200)
            ->header('Content-Type', $mime)
            ->header('ETag', $etag)
            ->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT')
            ->header('Cache-Control', $cacheControl);
    }

    /**
     * Permite probar la emisión del certificado (Vista Previa).
     * 
     * @param int|string $id_curso
     */
    public function probar($id_curso)
    {
        $curso = Curso::findOrFail($id_curso);
        $this->autorizarVistaPrevia($curso);

        $user = Auth::user();
        $persona = $user->personalData;

        if (!$persona) {
            return redirect()->back()->with('error', 'Necesitas datos personales asociados a tu cuenta para previsualizar el certificado.');
        }

        // Creamos una inscripción dummy para la prueba
        $inscripcion = new Inscripcion([
            'id_curso' => $curso->id_curso,
            'estado' => Inscripcion::ESTADO_APROBADO
        ]);
        $inscripcion->setRelation('persona', $persona);

        try {
            $pdfContent = $this->certificadoService->generarPdf($curso, $inscripcion);

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="Prueba_Certificado.pdf"');
        } catch (\Exception $e) {
            Log::error('Error al generar vista previa de certificado', [
                'id_curso' => $curso->id_curso,
                'mensaje' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'No se pudo generar la vista previa del certificado.');
        }
    }

    /**
     * Prueba en vivo del editor: genera el PDF con lo que hay en pantalla.
     *
     * Para el wizard (sin curso aún): usa el nombre del paso 1, la persona
     * propia como muestra, las coords del body y los archivos del temporal.
     * El QR es de muestra (el curso no existe en BD). Throttle en la ruta.
     */
    public function pruebaWizard(Request $request)
    {
        if (! hasPermissionRoute('taller.cursos.index', SecurityAction::CREAR_CURSO)) {
            abort(403, 'No tienes permiso para planificar cursos.');
        }

        $persona = Auth::user()->personalData;
        if (! $persona) {
            return response()->json(['ok' => false, 'error' => 'Necesitas datos personales asociados a tu cuenta.'], 422);
        }

        // La vista previa puede incluir las imágenes que el usuario acaba de
        // elegir en el editor, antes de guardar el paso 4. Se validan con las
        // mismas reglas que el submit clásico y se usan desde su archivo
        // temporal: no se persisten, solo alimentan este PDF.
        $validador = Validator::make($request->all(), [
            'plantilla' => ['nullable', 'image', 'mimes:png,jpg,jpeg', ImagenCertificado::plantilla()],
            'firma' => ['nullable', 'image', 'mimes:png,jpg,jpeg', ImagenCertificado::firma()],
        ], [
            'plantilla.image' => 'La plantilla debe ser una imagen PNG o JPG.',
            'plantilla.mimes' => 'La plantilla debe ser una imagen PNG o JPG.',
            'firma.image' => 'La firma debe ser una imagen PNG o JPG.',
            'firma.mimes' => 'La firma debe ser una imagen PNG o JPG.',
        ]);

        if ($validador->fails()) {
            return response()->json([
                'ok' => false,
                'error' => $validador->errors()->first(),
            ], 422);
        }

        $coords = json_decode((string) $request->input('coords'), true);
        $errorCoords = \Modules\Taller\Services\CertificadoConfig::validarCoords($coords);
        if ($errorCoords !== null) {
            return response()->json(['ok' => false, 'error' => $errorCoords], 422);
        }

        $token = (string) (session('wizard_curso.token') ?? '');
        $config = \Modules\Taller\Services\CertificadoConfig::class;

        // Sin token no hay carpeta temporal que leer. Antes se caía en la
        // plantilla global o en el diseño por defecto, y parecía que el editor
        // había ignorado las imágenes. Si vienen archivos en la propia
        // petición sí se puede previsualizar aunque la sesión haya caducado.
        if ($token === '' && ! $request->hasFile('plantilla') && ! $request->hasFile('firma')) {
            return response()->json([
                'ok' => false,
                'error' => 'El proceso de creación expiró. Vuelve a abrir el paso 4 e inténtalo de nuevo.',
            ], 419);
        }

        // Prioridad: archivo recién elegido en el editor > archivo guardado del
        // wizard > plantilla global. Así la prueba refleja lo que se ve en el
        // lienzo incluso antes de pulsar "Continuar a Requisitos".
        $plantillaPath = $request->hasFile('plantilla')
            ? (string) $request->file('plantilla')->getRealPath()
            : ($token !== '' ? $config::rutaWizardPlantilla($token) : null);

        if (! $request->hasFile('plantilla') && $plantillaPath === null) {
            $plantillaPath = $config::buscarPrimero($config::dirBase(), 'plantilla');
        }

        $firmaPath = $request->hasFile('firma')
            ? (string) $request->file('firma')->getRealPath()
            : ($token !== '' ? $config::rutaWizardFirma($token) : null);

        $hayPlantilla = $plantillaPath !== null
            && $this->certificadoService->detectImageType($plantillaPath) !== null;
        if (! $hayPlantilla) {
            // Sin fondo las coords calibradas quedarían flotando: el núcleo
            // usa el diseño propio igual que en la emisión real.
            $coords = null; // se resuelve abajo
        }

        $nombreCurso = (string) (session('wizard_curso.datos.nombre') ?? 'Curso de muestra');
        $curso = new Curso(['nombre' => $nombreCurso, 'codigo' => 'LAB-MUESTRA']);
        $code = \Modules\Taller\Services\CertificadoCodigo::emitir($curso, $persona);

        try {
            $pdf = $this->certificadoService->generarPdfCon(
                $curso,
                $persona,
                $code,
                $coords ?? $config::coordsBase(),
                $plantillaPath,
                $firmaPath,
                $hayPlantilla
            );

            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="Prueba_Certificado.pdf"');
        } catch (\Throwable $e) {
            Log::error('Error al generar prueba en vivo del certificado', [
                'mensaje' => $e->getMessage(),
            ]);

            return response()->json(['ok' => false, 'error' => 'No se pudo generar la vista previa.'], 500);
        }
    }

    /**
     * Solo el facilitador del curso, gestión o un inscrito aprobado
     * pueden previsualizar el certificado (evita abuso/DoS anónimo).
     */
    private function autorizarVistaPrevia(Curso $curso): void
    {
        $user = Auth::user();
        $esGestor = hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO);
        $personaId = $user->personalData->id_persona ?? null;
        $esFacilitador = $personaId !== null && (int) $curso->id_persona === (int) $personaId;
        $esInscrito = $personaId !== null && Inscripcion::where('id_curso', $curso->id_curso)
            ->where('id_persona', $personaId)
            ->where('estado', Inscripcion::ESTADO_APROBADO)
            ->exists();

        if (!$esGestor && !$esFacilitador && !$esInscrito) {
            abort(403, 'No tienes permiso para previsualizar el certificado de este curso.');
        }
    }

    /**
     * Verifica la autenticidad de un certificado (Ruta Pública).
     * 
     * @param string $codigo Formato: CODIGO_CURSO-DNI
     */
    public function verificar($codigo)
    {
        $parsed = \Modules\Taller\Services\CertificadoCodigo::parse((string) $codigo);
        if ($parsed === null) {
            return view('taller::certificados.verificar', ['error' => 'Código de certificado inválido.']);
        }

        // El formato con firma evita certificados forjados; el legado se
        // sigue aceptando para los QR ya impresos, pero queda registrado.
        if ($parsed['hmac'] !== null
            && !\Modules\Taller\Services\CertificadoCodigo::firmaValida($parsed['codigo'], $parsed['dni'], $parsed['hmac'])) {
            return view('taller::certificados.verificar', ['valido' => false]);
        }

        Log::info('certificado.verificado', ['formato' => $parsed['formato']]);

        $cursoCodigo = $parsed['codigo'];
        $dni = $parsed['dni'];

        $curso = Curso::where('codigo', $cursoCodigo)->first();
        $persona = PersonalData::where('dni', $dni)->first();

        if (!$curso || !$persona) {
            return view('taller::certificados.verificar', ['valido' => false]);
        }

        $inscripcion = Inscripcion::where('id_curso', $curso->id_curso)
            ->where('id_persona', $persona->id_persona)
            ->where('estado', Inscripcion::ESTADO_APROBADO)
            ->first();

        $valido = $inscripcion
            && $inscripcion->certificadoAprobado()
            && in_array($curso->estado_actual->id_estado ?? 0, [EstadoCurso::FINALIZADO->value, EstadoCurso::CERRADO->value]);

        return view('taller::certificados.verificar', [
            'valido' => $valido,
            'curso' => $curso,
            'persona' => $persona,
            'fecha' => $curso->fecha_fin ? $curso->fecha_fin->format('d/m/Y') : 'N/A'
        ]);
    }
}
