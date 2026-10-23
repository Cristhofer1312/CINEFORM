<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Escribe en security.auditoria (tabla de solo inserción).
 *
 * Registrar dentro de la misma transacción que el cambio auditado: si el cambio
 * se revierte, su registro de auditoría también, y nunca queda uno sin el otro.
 */
class Auditoria
{
    public function __construct(private Autorizacion $autorizacion) {}

    public function registrar(
        string $accion,
        string $entidad,
        int|string|null $entidadId,
        ?array $antes = null,
        ?array $despues = null,
        ?string $motivo = null
    ): void {
        DB::table('security.auditoria')->insert([
            'user_id' => Auth::id(),
            'perfil_id' => $this->autorizacion->perfilActivo(),
            'accion' => $accion,
            'entidad' => $entidad,
            'entidad_id' => $entidadId === null ? null : (string) $entidadId,
            'antes' => $antes === null ? null : json_encode($antes, JSON_UNESCAPED_UNICODE),
            'despues' => $despues === null ? null : json_encode($despues, JSON_UNESCAPED_UNICODE),
            'motivo' => $motivo,
            'ip' => request()?->ip(),
            'created_at' => now(),
        ]);
    }
}
