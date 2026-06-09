<?php

namespace Modules\Taller\Services;

use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Modules\Comun\Entities\PersonalData;
use FPDF;

class CertificadoService
{
    /**
     * Genera el PDF del certificado para un participante inscrito.
     *
     * @param Curso $curso
     * @param Inscripcion $inscripcion
     * @return string Contenido binario del PDF
     */
    public function generarPdf(Curso $curso, Inscripcion $inscripcion): string
    {
        /** @var PersonalData $persona */
        $persona = $inscripcion->persona;
        $code = $curso->codigo . '-' . $persona->dni;

        // Cargar coordenadas: hardcoded → defaults.json → {curso}.json
        $hardcoded = [
            'nombre' => ['x' => 40.9, 'y' => 78.2, 'size' => 24, 'w' => 220],
            'dni'    => ['x' => 159.4, 'y' => 92.6, 'size' => 12, 'w' => 25],
            'qr'     => ['x' => 9.8,  'y' => 154.6, 'w' => 20, 'h' => 20],
            'code'   => ['x' => 0.0,  'y' => 177.4, 'size' => 8, 'w' => 50],
            'firma'  => ['x' => 178.1, 'y' => 130.2, 'w' => 50, 'h' => 20],
        ];

        $coords = $hardcoded;

        // 1. Cargar defaults globales si existen
        $defaultsPath = storage_path('app/public/Certificados/defaults.json');
        if (file_exists($defaultsPath)) {
            $defaults = json_decode(file_get_contents($defaultsPath), true);
            if ($defaults) {
                $coords = array_merge($coords, $defaults);
            }
        }

        // 2. Cargar coordenadas específicas del curso si existen
        $coordsPath = storage_path('app/public/Certificados/cursos/' . $curso->id_curso . '.json');
        if (file_exists($coordsPath)) {
            $savedCoords = json_decode(file_get_contents($coordsPath), true);
            if ($savedCoords) {
                $coords = array_merge($coords, $savedCoords);
            }
        }

        // Generar QR
        $qrContent = route('taller.certificados.verificar', $code);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($qrContent);
        $tempQrPath = storage_path('app/temp/qr_' . uniqid() . '.png');

        if (!file_exists(dirname($tempQrPath))) {
            @mkdir(dirname($tempQrPath), 0755, true);
        }

        $qrImageData = @file_get_contents($qrUrl);
        if ($qrImageData !== false) {
            file_put_contents($tempQrPath, $qrImageData);
        }

        // Plantilla
        $plantillaPath = storage_path('app/public/Certificados/cursos/' . $curso->id_curso . '.png');
        if (!file_exists($plantillaPath)) {
            $plantillaPath = storage_path('app/public/Certificados/plantilla.png');
        }

        // Generar PDF
        $pdf = new FPDF();
        $pdf->AddPage('L'); // Landscape

        // Fondo del certificado
        if (file_exists($plantillaPath)) {
            $pdf->Image($plantillaPath, 0, 0, 297, 210);
        } else {
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->Rect(5, 5, 287, 200);
            $pdf->SetXY(10, 10);
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 10, 'Plantilla de certificado no encontrada.', 0, 1, 'C');
        }

        // QR Code
        if (file_exists($tempQrPath) && isset($coords['qr'])) {
            $qrW = $coords['qr']['w'] ?? 20;
            $qrH = $coords['qr']['h'] ?? $qrW;
            $pdf->Image($tempQrPath, $coords['qr']['x'], $coords['qr']['y'], $qrW, $qrH);
        }

        // Firma digital del docente
        $firmaPath = storage_path('app/public/Certificados/cursos/' . $curso->id_curso . '_firma.png');
        if (file_exists($firmaPath) && isset($coords['firma'])) {
            $firmaW = $coords['firma']['w'] ?? 50;
            $firmaH = $coords['firma']['h'] ?? ($firmaW * 0.4); // fallback a proporción si no hay h
            $pdf->Image($firmaPath, $coords['firma']['x'], $coords['firma']['y'], $firmaW, $firmaH, 'PNG');
        }

        // Código del certificado
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', $coords['code']['size'] ?? 8);
        $pdf->setXY($coords['code']['x'], $coords['code']['y']);
        $codeW = $coords['code']['w'] ?? 50;
        $pdf->Cell($codeW, 6, utf8_decode($code), 0, 1, 'C');

        // Nombre completo del participante
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', $coords['nombre']['size'] ?? 24);
        $pdf->setXY($coords['nombre']['x'], $coords['nombre']['y']);
        $nombreCompleto = $persona->nombre_completo;
        $nombreW = $coords['nombre']['w'] ?? 220;
        $pdf->Cell($nombreW, 10, utf8_decode($nombreCompleto), 0, 1, 'C');

        // Cédula del participante
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', $coords['dni']['size'] ?? 12);
        $pdf->setXY($coords['dni']['x'], $coords['dni']['y']);
        $dniW = $coords['dni']['w'] ?? 25;
        $pdf->Cell($dniW, 10, utf8_decode($persona->dni), 0, 1, 'L');

        $pdfContent = $pdf->Output('S');

        if (file_exists($tempQrPath)) {
            @unlink($tempQrPath);
        }

        return $pdfContent;
    }
}
