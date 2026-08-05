<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CertificacionTestDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // ── Verificar si ya existe data de prueba (idempotencia) ──
            $cursoExistente = DB::table('taller.cursos')
                ->where('codigo', 'TGC-2026-001')
                ->first();

            if ($cursoExistente) {
                $this->command->warn("⚠️  La data de prueba ya existe (Curso ID: {$cursoExistente->id_curso}). Omitiendo.");
                DB::rollBack();
                return;
            }

            // ── 1. Crear 4 usuarios + personas participantes ──────────────
            $participantes = [
                ['username' => 'maria_garcia',   'email' => 'maria@cineform.com',  'dni' => '20123456', 'nombre' => 'María',  'apellido' => 'García',  'genero' => 2],
                ['username' => 'carlos_mendoza', 'email' => 'carlos@cineform.com', 'dni' => '20234567', 'nombre' => 'Carlos', 'apellido' => 'Mendoza', 'genero' => 1],
                ['username' => 'ana_torres',     'email' => 'ana@cineform.com',    'dni' => '20345678', 'nombre' => 'Ana',    'apellido' => 'Torres',  'genero' => 2],
                ['username' => 'luis_ramirez',   'email' => 'luis@cineform.com',   'dni' => '20456789', 'nombre' => 'Luis',   'apellido' => 'Ramirez', 'genero' => 1],
            ];

            $userIds = [];
            $personaIds = [];

            foreach ($participantes as $p) {
                $userId = DB::selectOne(
                    'INSERT INTO security.users (username, email, password, register_date, active, ip) VALUES (?, ?, ?, ?, ?, ?) RETURNING id',
                    [$p['username'], $p['email'], Hash::make('12345678'), now(), 1, '127.0.0.1']
                )->id;
                $userIds[] = $userId;

                DB::table('security.profiles_users')->insert([
                    'id_rol'     => 3,
                    'id_users'   => $userId,
                    'status'     => 1,
                    'creado_por' => 1,
                    'creado_en'  => now(),
                ]);

                $personaId = DB::selectOne(
                    'INSERT INTO comun.personas (user_id, tipo_dni, dni, pasaporte, rif, reg_nac_cine, genero, primer_nombre, primer_apellido, telefono, id_pais, id_estado, id_municipio, id_parroquia, direccion, creado_por, creado_en, actualizado_por, actualizado_en) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id_persona',
                    [$userId, 1, $p['dni'], $p['dni'], $p['dni'], $p['dni'], $p['genero'], $p['nombre'], $p['apellido'], '0412' . substr($p['dni'], -4), 1, 1, 1, 1, 'Av. Test #' . substr($p['dni'], -3), 1, now(), 1, now()]
                )->id_persona;
                $personaIds[] = $personaId;
            }

            $this->command->info("4 participantes creados: " . implode(', ', array_column($participantes, 'username')));

            // ── 2. Crear curso en estado 8 (Finalizado) ──────────────────
            $idCurso = DB::selectOne(
                'INSERT INTO taller.cursos (nombre, codigo, descripcion, id_modalidad, id_actividad_formativa, id_aspecto, id_persona, nivel, trimestre, correlativo, anio, duracion, horas, cantidad_cupos, fecha_inicio, fecha_fin, es_nacional, creado_por, creado_en, actualizado_por, actualizado_en) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id_curso',
                ['Taller de Guion Cinematográfico', 'TGC-2026-001', 'Curso intensivo sobre estructura de guion, personajes y diálogos para cortometrajes.', 1, 1, 1, 1, 'Intermedio', 2, 1, 2026, 4, 32, 20, '2026-03-01', '2026-03-28', true, 1, now(), 1, now()]
            )->id_curso;

            DB::table('taller.curso_estado')->insert([
                'id_curso'   => $idCurso,
                'id_estado'  => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (Schema::hasColumn('taller.cursos', 'id_estado')) {
                DB::table('taller.cursos')->where('id_curso', $idCurso)->update(['id_estado' => 8]);
            }

            $this->command->info("Curso creado (ID: {$idCurso}) en estado Finalizado (8)");

            // ── 3. Crear contenidos: 6 clases + 2 evaluaciones ──────────
            $contenidos = [
                ['titulo' => 'Introducción al Guion',            'eval' => false, 'pond' => null, 'orden' => 1, 'fecha' => '2026-03-03'],
                ['titulo' => 'Estructura Dramática',             'eval' => false, 'pond' => null, 'orden' => 2, 'fecha' => '2026-03-05'],
                ['titulo' => 'Creación de Personajes',           'eval' => false, 'pond' => null, 'orden' => 3, 'fecha' => '2026-03-10'],
                ['titulo' => 'Diálogos y Subtexto',              'eval' => false, 'pond' => null, 'orden' => 4, 'fecha' => '2026-03-12'],
                ['titulo' => 'Formato y Normas Técnicas',        'eval' => false, 'pond' => null, 'orden' => 5, 'fecha' => '2026-03-17'],
                ['titulo' => 'Revisión y Feedback',              'eval' => false, 'pond' => null, 'orden' => 6, 'fecha' => '2026-03-19'],
                ['titulo' => 'Examen Teórico',                   'eval' => true,  'pond' => 40,   'orden' => 7, 'fecha' => '2026-03-24'],
                ['titulo' => 'Evaluación Práctica: Guion Corto', 'eval' => true,  'pond' => 60,   'orden' => 8, 'fecha' => '2026-03-26'],
            ];

            $contenidoIds = [];
            foreach ($contenidos as $c) {
                $idContenido = DB::selectOne(
                    'INSERT INTO taller.contenido_cursos (id_curso, titulo, descripcion, descripcion_breve, url_contenido, orden, es_evaluacion, ponderacion, fecha_contenido, creado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id_contenido_curso',
                    [$idCurso, $c['titulo'], 'Descripción de ' . $c['titulo'], $c['titulo'], 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', $c['orden'], $c['eval'], $c['pond'], $c['fecha'], 1]
                )->id_contenido_curso;
                $contenidoIds[] = $idContenido;
            }

            $clases = array_slice($contenidoIds, 0, 6);
            $evaluaciones = array_slice($contenidoIds, 6);

            $this->command->info("6 clases + 2 evaluaciones creadas");

            // ── 4. Crear inscripciones aprobadas ────────────────────────
            $inscripcionIds = [];
            foreach ($personaIds as $personaId) {
                $idInscripcion = DB::selectOne(
                    'INSERT INTO taller.inscripciones (id_curso, id_persona, fecha_inscripcion, estado, certificado_aprobado, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?) RETURNING id_inscripcion',
                    [$idCurso, $personaId, '2026-02-20', 'aprobado', null, now(), now()]
                )->id_inscripcion;
                $inscripcionIds[] = $idInscripcion;
            }

            $this->command->info("4 inscripciones aprobadas (certificado_aprobado = NULL)");

            // ── 5. Crear asistencias (patrones variados) ────────────────
            // María: 6/6 (100%) | Carlos: 5/6 (83%) | Ana: 3/6 (50%) | Luis: 2/6 (33%)
            $asistenciaMap = [
                0 => [0, 1, 2, 3, 4, 5],
                1 => [0, 1, 2, 4, 5],
                2 => [0, 1, 4],
                3 => [0, 1],
            ];

            $totalAsistencias = 0;
            foreach ($asistenciaMap as $pIdx => $clasesAsistidas) {
                foreach ($clasesAsistidas as $cIdx) {
                    DB::table('taller.asistencias')->insert([
                        'id_contenido_curso' => $clases[$cIdx],
                        'id_persona'         => $personaIds[$pIdx],
                        'id_inscripcion'     => $inscripcionIds[$pIdx],
                        'fecha_hora_marcado' => now()->subDays(rand(1, 30)),
                        'activa'             => true,
                        'metodo_marcado'     => 'manual',
                        'ip_marcado'         => '127.0.0.1',
                    ]);
                    $totalAsistencias++;
                }
            }

            $this->command->info("{$totalAsistencias} asistencias creadas");

            // ── 6. Crear calificaciones ──────────────────────────────────
            $calificaciones = [
                0 => ['examen' => 90, 'practica' => 85],
                1 => ['examen' => 75, 'practica' => 80],
                2 => ['examen' => 55, 'practica' => 45],
                3 => ['examen' => 35, 'practica' => 25],
            ];

            foreach ($calificaciones as $pIdx => $notas) {
                DB::table('taller.calificaciones')->insert([
                    'id_curso'           => $idCurso,
                    'id_contenido_curso' => $evaluaciones[0],
                    'id_persona'         => $personaIds[$pIdx],
                    'calificacion'       => $notas['examen'],
                    'calificado_por'     => 1,
                    'creado_en'          => now(),
                ]);
                DB::table('taller.calificaciones')->insert([
                    'id_curso'           => $idCurso,
                    'id_contenido_curso' => $evaluaciones[1],
                    'id_persona'         => $personaIds[$pIdx],
                    'calificacion'       => $notas['practica'],
                    'calificado_por'     => 1,
                    'creado_en'          => now(),
                ]);
            }

            $this->command->info("8 calificaciones creadas");

            DB::commit();

            // ── Resumen ──────────────────────────────────────────────────
            $this->command->newLine();
            $this->command->info("=== DATA DE PRUEBA PARA CERTIFICACION ===");
            $this->command->info("Curso: Taller de Guion Cinematico (ID: {$idCurso})");
            $this->command->info("Estado: Finalizado (8)");
            $this->command->info("Facilitador: admin (Cristhofer Leon)");
            $this->command->info("-------------------------------------------");
            $this->command->info("Maria Garcia    | 6/6 = 100% | 87.00 | Pendiente");
            $this->command->info("Carlos Mendoza  | 5/6 =  83% | 78.00 | Pendiente");
            $this->command->info("Ana Torres      | 3/6 =  50% | 49.00 | Pendiente");
            $this->command->info("Luis Ramirez    | 2/6 =  33% | 29.00 | Pendiente");
            $this->command->info("===========================================");
            $this->command->info("Login: admin / 12345678");
            $this->command->info("Panel de Certificacion desde el detalle del curso.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("ERROR: " . $e->getMessage());
            $this->command->error("Linea: " . $e->getFile() . ":" . $e->getLine());
            throw $e;
        }
    }
}
