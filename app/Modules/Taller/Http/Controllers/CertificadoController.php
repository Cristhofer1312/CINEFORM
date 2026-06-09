<?php

namespace Modules\Taller\Http\Controllers;

use App\Enums\EstadoCurso;
use Illuminate\Http\Request;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Modules\Comun\Entities\PersonalData;
use Modules\Taller\Services\CertificadoService;
use Illuminate\Support\Facades\Auth;

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
        if (!in_array($curso->id_estado, [EstadoCurso::FINALIZADO->value, EstadoCurso::CERRADO->value])) {
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

        // 3. Generar PDF
        try {
            $pdfContent = $this->certificadoService->generarPdf($curso, $inscripcion);
            
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="Certificado_' . $curso->codigo . '.pdf"');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar el certificado: ' . $e->getMessage());
        }
    }

    /**
     * Verifica la autenticidad de un certificado (Ruta Pública).
     * 
     * @param string $codigo Formato: CODIGO_CURSO-DNI
     */
    public function verificar($codigo)
    {
        // El código viene como CURSO_CODIGO-DNI
        $parts = explode('-', $codigo);
        if (count($parts) < 2) {
            return view('taller::certificados.verificar', ['error' => 'Código de certificado inválido.']);
        }

        $dni = array_pop($parts);
        $cursoCodigo = implode('-', $parts);

        $curso = Curso::where('codigo', $cursoCodigo)->first();
        $persona = PersonalData::where('dni', $dni)->first();

        if (!$curso || !$persona) {
            return view('taller::certificados.verificar', ['valido' => false]);
        }

        $inscripcion = Inscripcion::where('id_curso', $curso->id_curso)
            ->where('id_persona', $persona->id_persona)
            ->where('estado', Inscripcion::ESTADO_APROBADO)
            ->first();

        $valido = $inscripcion && in_array($curso->id_estado, [EstadoCurso::FINALIZADO->value, EstadoCurso::CERRADO->value]);

        return view('taller::certificados.verificar', [
            'valido' => $valido,
            'curso' => $curso,
            'persona' => $persona,
            'fecha' => $curso->fecha_fin ? $curso->fecha_fin->format('d/m/Y') : 'N/A'
        ]);
    }
}
