<?php

namespace Modules\Taller\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida las imágenes de plantilla y firma del certificado.
 *
 * El editor trabaja sobre un lienzo horizontal de A4 (297 x 210 mm) y el PDF
 * estira la plantilla hasta las coordenadas calibradas, así que una imagen
 * vertical o gigantesca se ve deformada y pesa de más sin aportar nada.
 *
 * Los tres límites se avisan con un mensaje en Swal en lugar de dejar que la
 * validación de Laravel muestre su error genérico.
 */
class ImagenCertificado implements ValidationRule
{
    /**
     * @param  string  $etiqueta  Cómo se llama al campo en los mensajes ("La plantilla")
     * @param  int  $maxKilos  Peso máximo en kilobytes
     * @param  int  $maxPx  Lado máximo en píxeles
     * @param  float  $maxAltoSobreAncho  Proporción máxima alto/ancho. 1.0 descarta verticales.
     * @param  string  $consayoVertical  Explicación de por qué se rechaza una imagen vertical
     */
    public function __construct(
        private string $etiqueta,
        private int $maxKilos,
        private int $maxPx,
        private float $maxAltoSobreAncho,
        private string $consayoVertical
    ) {
    }

    /**
     * Plantilla del certificado: A4 horizontal, 4 MB, 4000 px por lado.
     */
    public static function plantilla(): self
    {
        return new self(
            'La plantilla',
            4096,
            4000,
            1.0,
            'El certificado es horizontal (A4, 297 x 210 mm), así que necesitas una imagen apaisada.'
        );
    }

    /**
     * Firma digital: imagen pequeña con fondo transparente. Se permite cualquier
     * orientación salvo tiras absurdamente verticales, porque una firma sí puede
     * ser más alta que ancha.
     */
    public static function firma(): self
    {
        return new self(
            'La firma',
            2048,
            3000,
            4.0,
            'Una firma no debería ocupar una tira tan alta; recórtala o usa una imagen más horizontal.'
        );
    }

    /**
     * @param  string  $attribute
     * @param  mixed  $value  Instancia de UploadedFile
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        $ruta = method_exists($value, 'getRealPath') ? $value->getRealPath() : null;

        if (!$ruta || !is_readable($ruta)) {
            $fail($this->etiqueta . ' no se pudo leer. Vuelve a seleccionarla e inténtalo de nuevo.');

            return;
        }

        // 1) Peso
        $kilos = filesize($ruta) / 1024;
        if ($kilos > $this->maxKilos) {
            $fail(sprintf(
                '%s pesa %s MB y el máximo es %d MB. Optimiza la imagen (por ejemplo, expórtala como JPG con calidad 80) e inténtalo de nuevo.',
                $this->etiqueta,
                number_format($kilos / 1024, 1, ',', '.'),
                (int) round($this->maxKilos / 1024)
            ));

            return;
        }

        // 2) Dimensiones reales
        $info = @getimagesize($ruta);
        if ($info === false) {
            $fail($this->etiqueta . ' no parece una imagen válida. Usa un archivo PNG o JPG.');

            return;
        }

        [$ancho, $alto] = $info;

        // 3) Resolución excesiva
        if ($ancho > $this->maxPx || $alto > $this->maxPx) {
            $fail(sprintf(
                '%s mide %d x %d px y el máximo es %d px por lado. Reduce la resolución e inténtalo de nuevo.',
                $this->etiqueta,
                $ancho,
                $alto,
                $this->maxPx
            ));

            return;
        }

        // 4) Proporción demasiado vertical
        if ($alto > $ancho * $this->maxAltoSobreAncho) {
            $fail(sprintf(
                '%s es demasiado larga: mide %d x %d px y es más alta que ancha. %s',
                $this->etiqueta,
                $ancho,
                $alto,
                $this->consayoVertical
            ));
        }
    }
}
