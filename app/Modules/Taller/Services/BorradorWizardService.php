<?php

namespace Modules\Taller\Services;

use Illuminate\Support\Str;
use Modules\Taller\Entities\CursoBorrador;

/**
 * Persistencia de borradores del wizard por usuario.
 *
 * La sesión sigue como caché rápida, pero la fuente persistente es BD:
 * el borrador sobrevive a la expiración de la sesión y permite varios
 * borradores por usuario con limpieza por antigüedad.
 */
class BorradorWizardService
{
    /**
     * Recupera o crea el borrador activo de la sesión para el usuario.
     * Si la sesión expiró pero el usuario tiene un borrador en BD,
     * lo recupera y rehidrata la sesión para no perder el trabajo.
     */
    public function actual(int $userId): CursoBorrador
    {
        $token = session('wizard_curso.token');

        if (is_string($token) && $token !== '') {
            $borrador = CursoBorrador::where('token', $token)
                ->where('user_id', $userId)
                ->first();
            if ($borrador) {
                return $borrador;
            }
        }

        // Fallback persistente: buscar el borrador más reciente del usuario
        $borradorReciente = CursoBorrador::where('user_id', $userId)
            ->latest('updated_at')
            ->first();

        if ($borradorReciente) {
            $this->sincronizarSesion($borradorReciente);

            return $borradorReciente;
        }

        return $this->crear($userId);
    }

    public function reiniciar(int $userId): CursoBorrador
    {
        CursoBorrador::where('user_id', $userId)->delete();
        session()->forget('wizard_curso');

        return $this->crear($userId);
    }

    public function crear(int $userId): CursoBorrador
    {
        $borrador = CursoBorrador::create([
            'user_id' => $userId,
            'token' => Str::random(24),
            'paso_actual' => 1,
            'payload' => [],
        ]);

        $this->sincronizarSesion($borrador);

        return $borrador;
    }

    public function porToken(int $userId, string $token): ?CursoBorrador
    {
        return CursoBorrador::where('token', $token)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Guarda un slice (paso1, paso2, contenidos, requisitos) sin validar
     * completitud: [Guardar borrador] no exige wizard completo.
     *
     * Sin fresh(): el llamante ya tiene los datos; evita un SELECT por guardado.
     */
    public function guardarSlice(int $userId, string $slice, array $datos, ?int $pasoActual = null): CursoBorrador
    {
        $borrador = $this->actual($userId);
        $payload = is_array($borrador->payload) ? $borrador->payload : [];
        $payload[$slice] = $datos;

        $borrador->payload = $payload;
        if ($pasoActual !== null) {
            $borrador->paso_actual = max((int) $borrador->paso_actual, $pasoActual);
        }
        $borrador->save();

        $this->sincronizarSesion($borrador);

        return $borrador;
    }

    public function marcarFoto(int $userId, ?string $fotoPath): CursoBorrador
    {
        $borrador = $this->actual($userId);
        $borrador->foto_path = $fotoPath;
        $borrador->save();

        return $borrador;
    }

    /**
     * Datos fusionados listos para finalizar(): paso1 + paso2 + contenidos.
     */
    public function datosFinalizar(CursoBorrador $borrador): array
    {
        $payload = is_array($borrador->payload) ? $borrador->payload : [];

        return array_merge(
            $payload['paso1'] ?? [],
            $payload['paso2'] ?? [],
            ['contenidos' => $payload['contenidos'] ?? []]
        );
    }

    public function requisitos(CursoBorrador $borrador): array
    {
        $payload = is_array($borrador->payload) ? $borrador->payload : [];

        return $payload['requisitos'] ?? [];
    }

    public function eliminar(CursoBorrador $borrador): void
    {
        if (session('wizard_curso.token') === $borrador->token) {
            session()->forget('wizard_curso');
        }
        $borrador->delete();
    }

    /**
     * Refresca la sesión desde el borrador para precargar formularios
     * con old() -> borrador -> defaults al retroceder.
     */
    public function sincronizarSesion(CursoBorrador $borrador): void
    {
        $payload = is_array($borrador->payload) ? $borrador->payload : [];

        session()->put('wizard_curso', [
            'token' => $borrador->token,
            'datos' => array_merge($payload['paso1'] ?? [], $payload['paso2'] ?? []),
            'contenidos' => $payload['contenidos'] ?? [],
            'requisitos' => $payload['requisitos'] ?? [],
            'paso_actual' => (int) $borrador->paso_actual,
        ]);
    }

    public function purgaExpirados(int $dias = 7): int
    {
        return CursoBorrador::where('updated_at', '<', now()->subDays(max(1, $dias)))->delete();
    }
}
