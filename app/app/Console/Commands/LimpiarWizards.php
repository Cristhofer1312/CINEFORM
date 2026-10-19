<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Limpia temporales de certificados abandonados.
 *
 * El wizard guarda archivos en storage/app/public/Certificados/wizard/{token}
 * y QR efímeros en el temporal del sistema (sys_get_temp_dir, prefijo
 * cineform_qr_). Si la sesión expira antes de finalizar, esos archivos
 * quedan huérfanos: este comando los elimina a diario.
 */
class LimpiarWizards extends Command
{
    protected $signature = 'taller:limpiar-wizards {--horas=24 : Antigüedad mínima en horas}';

    protected $description = 'Elimina directorios temporales de wizards de curso abandonados y QR efímeros.';

    public function handle(): int
    {
        $horas = max(1, (int) $this->option('horas'));
        $corte = now()->subHours($horas)->timestamp;
        $eliminados = 0;

        $base = storage_path('app/public/Certificados/wizard');
        if (is_dir($base)) {
            // Solo se purgan temporales sin borrador vigente en BD: si el
            // usuario retoma su borrador, sus archivos deben seguir ahí.
            $tokensVigentes = \Modules\Taller\Entities\CursoBorrador::pluck('token')->all();
            foreach (glob($base.'/*') ?: [] as $dir) {
                if (!is_dir($dir) || (@filemtime($dir) ?: 0) >= $corte) {
                    continue;
                }
                if (in_array(basename($dir), $tokensVigentes, true)) {
                    continue;
                }
                $this->eliminarDirectorio($dir);
                $eliminados++;
            }
        }

        // QR efímeros en el temporal del sistema (mismo prefijo que usa
        // CertificadoService). Solo los nuestros y con más de una hora.
        $temp = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
        foreach (glob($temp.'/cineform_qr_*.png') ?: [] as $archivo) {
            if (is_file($archivo) && (@filemtime($archivo) ?: 0) < now()->subHour()->timestamp) {
                @unlink($archivo);
                $eliminados++;
            }
        }

        Log::info('Limpieza de temporales de certificados', ['eliminados' => $eliminados, 'horas' => $horas]);
        $borradores = app(\Modules\Taller\Services\BorradorWizardService::class)->purgaExpirados(7);
        Log::info('Limpieza de borradores de curso', ['eliminados' => $borradores]);
        $this->info("Temporales eliminados: {$eliminados}. Borradores purgados: {$borradores}.");

        return self::SUCCESS;
    }

    private function eliminarDirectorio(string $dir): void
    {
        $real = realpath($dir);
        $base = realpath(storage_path());

        if ($real === false || $base === false || $real === $base || ! str_starts_with($real, $base.DIRECTORY_SEPARATOR)) {
            Log::warning('Se evitó eliminar un directorio fuera de storage', ['ruta' => $dir]);

            return;
        }

        foreach (glob($real.'/*') ?: [] as $item) {
            if (is_dir($item)) {
                $this->eliminarDirectorio($item);
            } else {
                @unlink($item);
            }
        }
        @rmdir($real);
    }
}
