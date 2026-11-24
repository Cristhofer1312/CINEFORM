<?php

namespace Modules\Taller\Services;

use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Modules\Comun\Entities\PersonalData;
use Modules\Taller\Services\Contracts\QrGeneratorInterface;
use Modules\Taller\Services\BaconQrGenerator;
use Modules\Taller\Services\CertificadoCodigo;
use FPDF;

class CertificadoService
{
    public const CODIGO_VISTA_PREVIA = 'VISTA-PREVIA-SIN-VALIDEZ';

    private QrGeneratorInterface $qr;

    public function __construct(?QrGeneratorInterface $qr = null)
    {
        $this->qr = $qr ?? new BaconQrGenerator();
    }
    /**
     * Coordenadas del diseño propio, usado cuando el curso no tiene plantilla.
     *
     * Las coordenadas calibradas están pensadas para la imagen de fondo del
     * curso; sin esa imagen quedarían flotando sobre el papel. Este diseño
     * coloca cada elemento de forma coherente para que el certificado siga
     * siendo legible y presentable.
     */
    private const COORDS_SIN_PLANTILLA = [
        'nombre' => ['x' => 38.5, 'y' => 95, 'size' => 24, 'w' => 220],
        'dni'    => ['x' => 118.5, 'y' => 113, 'size' => 12, 'w' => 60, 'align' => 'C'],
        'firma'  => ['x' => 203, 'y' => 130, 'w' => 60, 'h' => 24],
        'qr'     => ['x' => 34, 'y' => 130, 'w' => 28, 'h' => 28],
        'code'   => ['x' => 98.5, 'y' => 183, 'size' => 8, 'w' => 100],
    ];

    /**
     * Dibuja el certificado con un diseño propio cuando el curso no tiene
     * plantilla configurada.
     *
     * Antes solo se pintaba un rectángulo con la frase "Plantilla de
     * certificado no encontrada", que se veía como un error. Ahora el documento
     * sale presentable y legible, con una nota discreta al pie que recuerda que
     * falta configurar la plantilla institucional.
     *
     * @param  FPDF  $pdf
     * @param  Curso  $curso
     * @return void
     */
    private function dibujarDisenoSinPlantilla(FPDF $pdf, Curso $curso)
    {
        $azul = [31, 58, 95];
        $gris = [90, 90, 90];
        $grisClaro = [155, 155, 155];

        // Marco doble
        $pdf->SetDrawColor(...$azul);
        $pdf->SetLineWidth(0.8);
        $pdf->Rect(8, 8, 281, 194);
        $pdf->SetLineWidth(0.25);
        $pdf->Rect(12, 12, 273, 186);

        // Titulo
        $pdf->SetTextColor(...$azul);
        $pdf->SetFont('Arial', 'B', 30);
        $pdf->SetXY(0, 40);
        $pdf->Cell(297, 12, 'CERTIFICADO', 0, 0, 'C');

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(0, 54);
        $pdf->Cell(297, 8, $this->latin('DE CAPACITACION'), 0, 0, 'C');

        // Nombre del curso
        $pdf->SetFont('Arial', 'I', 11);
        $pdf->SetTextColor(...$gris);
        $pdf->SetXY(20, 66);
        $pdf->Cell(257, 7, $this->latin(mb_strimwidth($curso->nombre, 0, 110, '...')), 0, 0, 'C');

        // Entradilla sobre el nombre del participante
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->SetXY(0, 80);
        $pdf->Cell(297, 6, $this->latin('Se otorga el presente certificado a:'), 0, 0, 'C');

        // Espacio de la firma
        $pdf->SetDrawColor(...$grisClaro);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(203, 154, 263, 154);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(...$gris);
        $pdf->SetXY(203, 156);
        $pdf->Cell(60, 5, $this->latin('Firma de la autoridad'), 0, 0, 'C');

        // Nota: la plantilla institucional sigue sin configurarse.
        // A 190mm para quedar dentro del marco interior, no encima de la linea.
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->SetTextColor(...$grisClaro);
        $pdf->SetXY(0, 190);
        $pdf->Cell(297, 5, $this->latin('Documento generado sin plantilla institucional configurada.'), 0, 0, 'C');
    }

    /**
     * Reduce el cuerpo de un texto hasta que quepa en el ancho dado.
     *
     * FPDF no recorta el texto que se pasa a Cell(): si no cabe, se sale de la
     * hoja. Con nombres largos eso invadiria la firma o el borde, asi que se
     * baja el tamano en pasos de 1 hasta llegar al minimo indicado.
     *
     * @param  string  $texto
     * @param  float   $anchoMm
     * @param  float   $tamInicial
     * @param  float   $tamMinimo
     * @return float
     */
    private function ajustarTamano(string $texto, float $anchoMm, float $tamInicial, float $tamMinimo): float
    {
        $tam = (float) $tamInicial;

        while ($tam > $tamMinimo && $this->textoExcede($texto, $anchoMm, $tam)) {
            $tam -= 1;
        }

        return $tam;
    }

    /**
     * Indica si el texto, con el cuerpo indicado, se pasaria del ancho dado.
     *
     * FPDF estima el ancho de las fuentes base en 0.5 por caracter, que es lo
     * que usa internamente para calcular la posicion de un Cell() centrado.
     *
     * @param  string  $texto
     * @param  float   $anchoMm
     * @param  float   $tam
     * @return bool
     */
    private function textoExcede(string $texto, float $anchoMm, float $tam): bool
    {
        return (mb_strlen($texto) * $tam * 0.5 * 25.4 / 72) > $anchoMm;
    }

    /**
     * Recorta el texto con puntos suspensivos si no cabe en el ancho dado.
     *
     * Es la ultima red de seguridad del diseño propio: un nombre absurdo (un
     * parrafo entero en el campo) no cabe ni con el cuerpo minimo, y en un
     * certificado es preferible una linea truncada a varias lineas que se
     * solaparian con la cedula, la firma y el codigo. Con nombres reales el
     * recorte no llega a activarse nunca.
     *
     * @param  string  $texto
     * @param  float   $anchoMm
     * @param  float   $tam
     * @return string
     */
    private function recortarTexto(string $texto, float $anchoMm, float $tam): string
    {
        if (!$this->textoExcede($texto, $anchoMm, $tam)) {
            return $texto;
        }

        $maxChars = (int) floor($anchoMm / ($tam * 0.5 * 25.4 / 72));

        if ($maxChars < 4) {
            return mb_substr($texto, 0, 4);
        }

        return rtrim(mb_substr($texto, 0, $maxChars - 3)) . '...';
    }

    /**
     * Convierte UTF-8 a Latin-1 para FPDF (fuentes base sin soporte Unicode).
     *
     * Reemplaza a utf8_decode(), obsoleto desde PHP 8.2.
     */
    private function latin(string $texto): string
    {
        $convertido = mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');

        return is_string($convertido) ? $convertido : $texto;
    }

    /**
     * Detecta el tipo real de imagen leyendo los magic bytes del archivo.
     * Devuelve 'PNG', 'JPG' o null si no es un formato soportado.
     */
    public function detectImageType(string $path): ?string
    {
        $handle = @fopen($path, 'rb');
        if (!$handle) return null;
        $bytes = fread($handle, 4);
        fclose($handle);

        // PNG: 89 50 4E 47
        if (substr($bytes, 0, 4) === "\x89PNG") return 'PNG';
        // JPEG: FF D8 FF
        if (substr($bytes, 0, 3) === "\xFF\xD8\xFF") return 'JPG';

        return null;
    }

    /**
     * Genera el PDF del certificado para un participante inscrito.
     *
     * Resuelve insumos desde disco y delega en generarPdfCon().
     *
     * @param  bool  $esVistaPrevia  Si es true, marca el documento como vista previa y omite el código/QR válidos
     * @return string Contenido binario del PDF
     */
    public function generarPdf(Curso $curso, Inscripcion $inscripcion, bool $esVistaPrevia = false): string
    {
        /** @var PersonalData $persona */
        $persona = $inscripcion->persona;
        $code = $esVistaPrevia
            ? self::CODIGO_VISTA_PREVIA
            : CertificadoCodigo::emitir($curso, $persona);

        // Cargar coordenadas: base → defaults.json → {curso}.json (fuente única: CertificadoConfig).
        $coords = CertificadoConfig::coordsPara($curso->id_curso);

        // Plantilla (propia en cualquier extensión, o global).
        $plantillaPath = CertificadoConfig::rutaPlantilla($curso->id_curso);

        $hayPlantilla = $plantillaPath !== null
            && $this->detectImageType($plantillaPath) !== null;

        // Sin plantilla se ignoran las coordenadas calibradas: están pensadas
        // para la imagen de fondo del curso y quedarían descolocadas.
        if (! $hayPlantilla) {
            $coords = self::COORDS_SIN_PLANTILLA;
        }

        return $this->generarPdfCon(
            $curso,
            $persona,
            $code,
            $coords,
            $plantillaPath,
            CertificadoConfig::rutaFirma($curso->id_curso),
            $hayPlantilla,
            $esVistaPrevia
        );
    }

    /**
     * Núcleo de emisión con insumos explícitos (sin leer disco por curso).
     *
     * Permite la prueba en vivo del wizard, donde aún no hay curso ni
     * .coords.json definitivo: se pasa lo que el editor tiene en pantalla.
     *
     * @param  Curso  $curso  Curso (puede ser no persistido, solo nombre/código)
     * @param  PersonalData  $persona  Datos de muestra para nombre/DNI
     * @param  string  $code  Código ya emitido (firma del QR)
     * @param  array  $coords  Coordenadas en mm FPDF
     * @param  string|null  $plantillaPath  Fondo (o null para diseño propio)
     * @param  string|null  $firmaPath  Firma (o null para omitirla)
     * @param  bool  $hayPlantilla  Si es false se usa el diseño propio
     * @param  bool  $esVistaPrevia  Si es true, marca el PDF como vista previa sin código/QR válidos
     * @return string Contenido binario del PDF
     */
    public function generarPdfCon(
        Curso $curso,
        PersonalData $persona,
        string $code,
        array $coords,
        ?string $plantillaPath,
        ?string $firmaPath,
        bool $hayPlantilla,
        bool $esVistaPrevia = false
    ): string {
        if ($esVistaPrevia) {
            $code = self::CODIGO_VISTA_PREVIA;
            $qrContent = 'VISTA PREVIA - SIN VALIDEZ OFICIAL';
        } else {
            $qrContent = route('taller.certificados.verificar', $code);
        }

        // Generar QR en local (sin servicios externos: el DNI no sale del servidor).
        // En el temporal del sistema (/tmp, siempre escribible), no en
        // storage/app: ese volumen puede quedar con dueño root y PHP-FPM
        // (www-data) se quedaría sin permiso de escritura.
        $tempQrPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . 'cineform_qr_' . uniqid() . '.png';

        $this->qr->generar($qrContent, $tempQrPath);

        // Generar PDF (A4 Horizontal)
        $pdf = new FPDF('L', 'mm', 'A4');
        
        // 1. DESACTIVAR el salto de página automático para permitir dibujo al borde
        $pdf->SetAutoPageBreak(false, 0);
        
        // 2. Añadir la página
        $pdf->AddPage();

        // 3. Dibujar la plantilla de fondo PRIMERO (Ocupa toda la hoja)
        if ($hayPlantilla && $plantillaPath) {
            $pdf->Image($plantillaPath, 0, 0, 297, 210, $this->detectImageType($plantillaPath));
        } else {
            $this->dibujarDisenoSinPlantilla($pdf, $curso);
        }

        // 4. Dibujar imágenes adicionales (Firma, QR)
        // QR Code
        if (file_exists($tempQrPath) && isset($coords['qr'])) {
            $qrW = $coords['qr']['w'] ?? 20;
            $qrH = $coords['qr']['h'] ?? $qrW;
            $pdf->Image($tempQrPath, $coords['qr']['x'], $coords['qr']['y'], $qrW, $qrH);
        }

        // Firma digital de la autoridad.
        if ($firmaPath && isset($coords['firma'])) {
            $firmaW = $coords['firma']['w'] ?? 50;
            $firmaH = $coords['firma']['h'] ?? ($firmaW * 0.4);
            // Detectar tipo real por magic bytes, ignorando la extensión
            $firmaTypeReal = $this->detectImageType($firmaPath);
            if ($firmaTypeReal) {
                $pdf->Image($firmaPath, $coords['firma']['x'], $coords['firma']['y'], $firmaW, $firmaH, $firmaTypeReal);
            }
        }

        // 5. Posicionar y dibujar los textos
        $pdf->SetTextColor(0, 0, 0);

        // Código del certificado
        if (isset($coords['code'])) {
            $pdf->SetFont('Arial', 'B', $coords['code']['size'] ?? 8);
            $pdf->setXY($coords['code']['x'], $coords['code']['y']);
            $codeW = $coords['code']['w'] ?? 50;
            $pdf->Cell($codeW, 10, $this->latin($code), 0, 0, 'C');
        }

        // Nombre completo del participante
        if (isset($coords['nombre'])) {
            $nombreSize = $coords['nombre']['size'] ?? 24;
            $nombreW = $coords['nombre']['w'] ?? 220;
            $nombreTexto = (string) $persona->nombre_completo;

            // En el diseño propio el nombre no puede invadir la firma ni salirse
            if (!$hayPlantilla) {
                $nombreSize = $this->ajustarTamano($nombreTexto, $nombreW, $nombreSize, 13);
                $nombreTexto = $this->recortarTexto($nombreTexto, $nombreW, $nombreSize);
            }

            $pdf->SetFont('Arial', 'B', $nombreSize);
            $pdf->setXY($coords['nombre']['x'], $coords['nombre']['y']);
            $pdf->Cell($nombreW, 10, $this->latin($nombreTexto), 0, 0, 'C');
        }

        // Cédula del participante
        if (isset($coords['dni'])) {
            $pdf->SetFont('Arial', 'B', $coords['dni']['size'] ?? 12);
            $pdf->setXY($coords['dni']['x'], $coords['dni']['y']);
            $dniW = $coords['dni']['w'] ?? 25;
            $pdf->Cell($dniW, 10, $this->latin($persona->dni), 0, 0, $coords['dni']['align'] ?? 'L');
        }

        if ($esVistaPrevia) {
            $this->dibujarMarcaVistaPrevia($pdf);
        }

        $pdfContent = $pdf->Output('S');

        if (file_exists($tempQrPath)) {
            @unlink($tempQrPath);
        }

        return $pdfContent;
    }

    /**
     * Marca visible de vista previa para que un PDF de prueba no pueda
     * hacerse pasar por un certificado oficial emitido.
     */
    private function dibujarMarcaVistaPrevia(FPDF $pdf): void
    {
        // Banda superior enmarcada
        $pdf->SetDrawColor(185, 28, 28);
        $pdf->SetFillColor(254, 242, 242);
        $pdf->SetLineWidth(0.6);
        $pdf->Rect(48.5, 5, 200, 11, 'DF');

        $pdf->SetTextColor(185, 28, 28);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetXY(48.5, 6.5);
        $pdf->Cell(200, 8, $this->latin('VISTA PREVIA - DOCUMENTO SIN VALIDEZ OFICIAL'), 0, 0, 'C');

        // Sello central sin relleno (permite verificar la calibración, pero impide recortar bordes)
        $pdf->SetFont('Arial', 'B', 38);
        $pdf->SetTextColor(220, 38, 38);
        $pdf->SetXY(0, 56);
        $pdf->Cell(297, 16, 'VISTA PREVIA', 0, 0, 'C');

        // Banda inferior de advertencia
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(185, 28, 28);
        $pdf->SetXY(0, 196);
        $pdf->Cell(297, 8, $this->latin('VISTA PREVIA - NO VÁLIDO PARA VERIFICACIÓN'), 0, 0, 'C');
    }
}
