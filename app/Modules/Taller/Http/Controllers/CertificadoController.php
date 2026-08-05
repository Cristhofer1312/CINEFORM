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

        // 3. Verificar que la certificación haya sido aprobada por el facilitador
        if (!$inscripcion->certificadoAprobado()) {
            return redirect()->back()->with('error', 'Tu certificación aún no ha sido aprobada por el facilitador.');
        }

        // 4. Generar PDF
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
     * Permite probar la emisión del certificado (Vista Previa).
     * 
     * @param int|string $id_curso
     */
    public function probar($id_curso)
    {
        $curso = Curso::findOrFail($id_curso);
        $user = Auth::user();
        $persona = $user->personalData;

        // Si el usuario no tiene datos personales (ej. admin puro), creamos un objeto dummy
        if (!$persona) {
            $persona = new \Modules\Comun\Entities\PersonalData([
                'primer_nombre' => 'USUARIO',
                'primer_apellido' => 'DE PRUEBA',
                'dni' => '00000000'
            ]);
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
            return "Error al generar prueba: " . $e->getMessage();
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

        $valido = $inscripcion
            && $inscripcion->certificadoAprobado()
            && in_array($curso->id_estado, [EstadoCurso::FINALIZADO->value, EstadoCurso::CERRADO->value]);

        return view('taller::certificados.verificar', [
            'valido' => $valido,
            'curso' => $curso,
            'persona' => $persona,
            'fecha' => $curso->fecha_fin ? $curso->fecha_fin->format('d/m/Y') : 'N/A'
        ]);
    }
}
