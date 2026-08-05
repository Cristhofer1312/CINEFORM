# Aprobación de Certificación — Plan de Implementación

> **Fecha de creación:** 2026-07-27
> **Estado:** Pendiente de implementación
> **Módulo:** Taller (Certificados)

## Descripción

Modificar el sistema de certificación actual para que la emisión del certificado **no sea automática** al finalizar el curso. En su lugar, se introduce un paso intermedio: un **landing de revisión** donde el facilitador o coordinador puede observar de cada participante su asistencia, calificaciones y estado general, para luego **aprobar o denegar** individualmente la certificación.

**Cambio fundamental:** Antes de que el facilitador apruebe la certificación de un participante, el botón "Emitir Certificado" debe estar **bloqueado/deshabilitado** para ese participante.

---

## Situación Actual vs. Propuesta

### Antes (actual)
```
Curso Finalizado (Estado 8) → Participante con inscripción APROBADA → Descargar certificado (directo)
```

### Después (propuesto)
```
Curso Finalizado (Estado 8)
    │
    ├── Facilitador/Coordinador abre "Panel de Certificación"
    │   └── Ve tabla con: participantes, % asistencia, promedio calificaciones, estado certificación
    │       ├── Ver detalle por participante: clases asistidas/faltantes + evaluaciones
    │       ├── Aprobar certificación individual → certificado_aprobado = true
    │       └── Denegar certificación → certificado_aprobado = false + motivo
    │
    └── Participante ve botón "Emitir Certificado"
        ├── Si certificado_aprobado = true → Descarga PDF ✅
        └── Si certificado_aprobado = false/null → Botón bloqueado 🔒 (con mensaje explicativo)
```

---

## Flujo de Usuario

```
Facilitador / Coordinador (Curso en Estado 8: Finalizado)
    │
    ├── Ve botón "Panel de Certificación" en el detalle del curso
    │   (accesible desde actions-container.blade.php)
    │
    ├── Abre la vista CertificacionPanel.blade.php
    │   │
    │   ├── Tabla con todos los participantes aprobados (inscritos con estado 'aprobado')
    │   │   │
    │   │   ├── COLUMNAS:
    │   │   │   ├── Nombre completo + Cédula
    │   │   │   ├── % Asistencia (asistencias activas / total actividades × 100)
    │   │   │   ├── Promedio Calificaciones (ponderado, mismo cálculo que CursoDetalle)
    │   │   │   ├── Estado Certificación (Pendiente / Aprobado / Denegado)
    │   │   │   └── Acciones (Aprobar / Denegar / Ver detalle)
    │   │   │
    │   │   └── Fila expandible/modal: detalle individual
    │   │       ├── Lista de TODAS las clases con ✅/❌ asistencia por cada una
    │   │       │   (para que el facilitador identifique exactamente cuáles faltó)
    │   │       ├── % asistencia calculado (asistencias activas / total clases × 100)
    │   │       └── Lista de evaluaciones con nota individual
    │
    └── Al aprobar → se actualiza campo `certificado_aprobado` en inscripciones
        Al denegar → se actualiza con motivo

Participante (Curso en Estado 8: Finalizado)
    │
    ├── Entra al detalle del curso
    │
    ├── Si certificado_aprobado = true:
    │   └── Botón dorado "Obtener Certificado" → Descarga PDF
    │
    ├── Si certificado_aprobado = NULL (pendiente):
    │   └── Botón gris deshabilitado "Certificado en Revisión" 🔒
    │       + Texto: "Tu certificado está siendo evaluado por el facilitador"
    │
    └── Si certificado_aprobado = false (denegado):
        └── Botón rojo deshabilitado "Certificado No Aprobado" ❌
            + Texto del motivo de denegación
```

---

## Proposed Changes

### Base de Datos (Migración)

#### [NEW] Migración: `add_certificado_aprobado_to_inscripciones.php`

Agrega columnas a la tabla existente `taller.inscripciones`:

| Columna | Tipo | Nullable | Default | Notas |
|---------|------|----------|---------|-------|
| `certificado_aprobado` | boolean | SÍ | `NULL` | `NULL` = pendiente, `true` = aprobado, `false` = denegado |
| `certificado_aprobado_por` | unsignedBigInteger | SÍ | `NULL` | FK → `security.users.id` |
| `certificado_fecha_aprobacion` | timestamp | SÍ | `NULL` | Fecha en que se aprobó/denegó |
| `certificado_motivo_denegacion` | text | SÍ | `NULL` | Motivo cuando `certificado_aprobado = false` |

> **NOTA:** Se usa la tabla existente `taller.inscripciones` en lugar de crear una nueva tabla, ya que la certificación es un atributo de la inscripción del participante en un curso específico. Esto mantiene la coherencia con el modelo actual.

```php
Schema::table('taller.inscripciones', function (Blueprint $table) {
    $table->boolean('certificado_aprobado')->nullable()->default(null)
        ->comment('NULL=pendiente, true=aprobado, false=denegado');
    $table->unsignedBigInteger('certificado_aprobado_por')->nullable();
    $table->foreign('certificado_aprobado_por')->references('id')->on('security.users')->onDelete('set null');
    $table->timestamp('certificado_fecha_aprobacion')->nullable();
    $table->text('certificado_motivo_denegacion')->nullable();
});

// Backward-compat: marcar como aprobados las inscripciones existentes de cursos finalizados
// para no bloquear descargas de certificados ya emitidos
DB::table('taller.inscripciones')
    ->whereNull('certificado_aprobado')
    ->where('estado', 'aprobado')
    ->update(['certificado_aprobado' => true]);
```

---

### Modelo Eloquent

#### [MODIFY] `Modules/Taller/Entities/Inscripcion.php`

Agregar al `$fillable`:
```php
'certificado_aprobado',
'certificado_aprobado_por',
'certificado_fecha_aprobacion',
'certificado_motivo_denegacion',
```

Agregar casts:
```php
'certificado_aprobado' => 'boolean',
'certificado_fecha_aprobacion' => 'datetime',
```

Agregar helpers:
```php
/**
 * Helpers de Estado de Certificación
 */
public function certificadoPendiente(): bool
{
    return is_null($this->certificado_aprobado);
}

public function certificadoAprobado(): bool
{
    return $this->certificado_aprobado === true;
}

public function certificadoDenegado(): bool
{
    return $this->certificado_aprobado === false;
}
```

Agregar relación:
```php
public function certificadoAprobadoPor()
{
    return $this->belongsTo(\Modules\Security\Entities\User::class, 'certificado_aprobado_por');
}
```

---

### Controller

#### [NEW] `Modules/Taller/Http/Controllers/CertificacionPanelController.php`

Extiende `BaseController`. Imports necesarios:

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Entities\Asistencia;
use App\Constants\SecurityAction;
```

Métodos:

| Método | Ruta | HTTP | Descripción |
|--------|------|------|-------------|
| `index($id_curso)` | `/cursos/{curso}/certificacion` | GET | Landing de revisión: tabla con participantes, asistencia, calificaciones, estado cert. Detalle expandible con clases asistidas/faltantes. |
| `aprobar(Request, $id_curso, $id_inscripcion)` | `/cursos/{curso}/certificacion/{inscripcion}/aprobar` | POST | Aprueba certificación individual |
| `denegar(Request, $id_curso, $id_inscripcion)` | `/cursos/{curso}/certificacion/{inscripcion}/denegar` | POST | Deniega certificación con motivo |

**Detalle del método `index()`:**

```php
public function index($id_curso)
{
    $curso = Curso::with([
        'contenidos' => function ($q) {
            $q->orderBy('fecha_contenido', 'asc')
              ->orderBy('id_contenido_curso', 'asc');
        },
        'inscripciones.persona',
    ])->findOrFail($id_curso);

    $this->verificarPermisoGestion($curso);

    // Solo inscripciones aprobadas (participantes formales)
    $inscripciones = $curso->inscripciones
        ->where('estado', Inscripcion::ESTADO_APROBADO);

    $actividades = $curso->contenidos->where('es_evaluacion', false);
    $evaluaciones = $curso->contenidos->where('es_evaluacion', true);

    // Cargar asistencias de todos los participantes
    $actividadIds = $actividades->pluck('id_contenido_curso');
    $asistenciasRaw = Asistencia::whereIn('id_contenido_curso', $actividadIds)
        ->where('activa', true)
        ->get();

    // Mapa: [id_persona => cantidad_asistencias]
    $asistenciasPorPersona = $asistenciasRaw
        ->groupBy('id_persona')
        ->map(fn($group) => $group->count());

    // Mapa detallado: [id_persona => [id_contenido_curso => true]]
    // Para mostrar en el panel qué clases asistió y cuáles faltó
    $asistenciaDetallePorPersona = $asistenciasRaw
        ->groupBy('id_persona')
        ->map(fn($group) => $group->pluck('id_contenido_curso')->flip()->keys());

    $totalActividades = $actividades->count();

    // Cargar calificaciones de todos los participantes
    $calificacionesRaw = DB::table('taller.calificaciones')
        ->where('id_curso', $curso->id_curso)
        ->get();

    // Mapa: [id_persona => colección de calificaciones]
    $calificacionesPorPersona = $calificacionesRaw->groupBy('id_persona');

    // Calcular promedio por persona (mismo algoritmo que CursoDetalleController)
    $resumenParticipantes = [];
    foreach ($inscripciones as $inscripcion) {
        $idPersona = $inscripcion->id_persona;
        
        $asistencias = $asistenciasPorPersona->get($idPersona, 0);
        $porcentajeAsistencia = $totalActividades > 0
            ? round(($asistencias / $totalActividades) * 100, 1)
            : 0;

        $califs = $calificacionesPorPersona->get($idPersona, collect());
        $puntosObtenidos = 0;
        $ponderacionEvaluada = 0;

        foreach ($evaluaciones as $eval) {
            $calif = $califs->firstWhere('id_contenido_curso', $eval->id_contenido_curso);
            if ($calif && isset($calif->calificacion)) {
                $puntosObtenidos += ($calif->calificacion * $eval->ponderacion) / 100;
                $ponderacionEvaluada += $eval->ponderacion;
            }
        }

        $promedio = $ponderacionEvaluada > 0
            ? round(($puntosObtenidos / $ponderacionEvaluada) * 100, 2)
            : null;

        $resumenParticipantes[] = [
            'inscripcion' => $inscripcion,
            'asistencias' => $asistencias,
            'totalActividades' => $totalActividades,
            'porcentajeAsistencia' => $porcentajeAsistencia,
            'clasesAsistidas' => $asistenciaDetallePorPersona->get($idPersona, collect()),
            'promedio' => $promedio,
        ];
    }

    return view('taller::a.CertificacionPanel', compact(
        'curso', 'resumenParticipantes', 'actividades', 'evaluaciones'
    ));
}
```

**Detalle del método `aprobar()`:**

```php
public function aprobar(Request $request, $id_curso, $id_inscripcion)
{
    $curso = Curso::findOrFail($id_curso);
    $this->verificarPermisoGestion($curso);

    $inscripcion = Inscripcion::where('id_inscripcion', $id_inscripcion)
        ->where('id_curso', $id_curso)
        ->where('estado', Inscripcion::ESTADO_APROBADO)
        ->firstOrFail();

    $inscripcion->update([
        'certificado_aprobado' => true,
        'certificado_aprobado_por' => auth()->id(),
        'certificado_fecha_aprobacion' => now(),
        'certificado_motivo_denegacion' => null,
    ]);

    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Certificación aprobada para ' . $inscripcion->persona->nombre_completo
        ]);
    }

    // Enviar email de notificación al participante
    try {
        Mail::send('taller::emails.certificacion_aprobada', [
            'inscripcion' => $inscripcion,
        ], function ($email) use ($inscripcion) {
            $email->subject('Certificación de Curso - Aprobada');
            $email->to($inscripcion->persona->user->email);
        });
    } catch (\Exception $e) {
        // No detenemos el proceso si falla el correo
    }

    return back()->with('success', 'Certificación aprobada correctamente.');
}
```

**Detalle del método `denegar()`:**

```php
public function denegar(Request $request, $id_curso, $id_inscripcion)
{
    $curso = Curso::findOrFail($id_curso);
    $this->verificarPermisoGestion($curso);

    $request->validate([
        'motivo' => 'required|string|max:500',
    ]);

    $inscripcion = Inscripcion::where('id_inscripcion', $id_inscripcion)
        ->where('id_curso', $id_curso)
        ->where('estado', Inscripcion::ESTADO_APROBADO)
        ->firstOrFail();

    $inscripcion->update([
        'certificado_aprobado' => false,
        'certificado_aprobado_por' => auth()->id(),
        'certificado_fecha_aprobacion' => now(),
        'certificado_motivo_denegacion' => $request->motivo,
    ]);

    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Certificación denegada para ' . $inscripcion->persona->nombre_completo
        ]);
    }

    // Enviar email de notificación al participante
    try {
        Mail::send('taller::emails.certificacion_rechazada', [
            'inscripcion' => $inscripcion,
        ], function ($email) use ($inscripcion) {
            $email->subject('Certificación de Curso - Observaciones');
            $email->to($inscripcion->persona->user->email);
        });
    } catch (\Exception $e) {
        // No detenemos el proceso si falla el correo
    }

    return back()->with('success', 'Certificación denegada correctamente.');
}
```

**Método auxiliar reutilizado:**

```php
private function verificarPermisoGestion(Curso $curso): void
{
    $personalData = $this->getUsuarioAutenticado()->personalData;
    $esGestor = hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_ASISTENCIA)
        || hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO);
    $esFacilitador = $curso->id_persona == $personalData->id_persona;

    if (!$esGestor && !$esFacilitador) {
        abort(403, 'No tienes permiso para gestionar la certificación de este curso.');
    }
}
```

> **NOTA DE VERIFICACIÓN:** Este método reutiliza exactamente el mismo patrón de `AsistenciaController::verificarPermisoGestion()`. Se podría extraer a `BaseController` en un refactor posterior.

---

#### [MODIFY] `Modules/Taller/Http/Controllers/CertificadoController.php`

Modificar el método `descargar()` para agregar la validación de certificación aprobada:

```diff
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

+    // 3. Verificar que la certificación haya sido aprobada por el facilitador
+    if (!$inscripcion->certificadoAprobado()) {
+        return redirect()->back()->with('error', 'Tu certificación aún no ha sido aprobada por el facilitador.');
+    }

-    // 3. Generar PDF
+    // 4. Generar PDF
     try {
         $pdfContent = $this->certificadoService->generarPdf($curso, $inscripcion);
 ```

También modificar el método `verificar()` para incluir la validación de certificación aprobada:

 ```diff
  public function verificar($codigo)
  {
      // ... parseo de código, búsqueda de curso/persona/inscripción ...

-     $valido = $inscripcion && in_array($curso->id_estado, [EstadoCurso::FINALIZADO->value, EstadoCurso::CERRADO->value]);
+     $valido = $inscripcion
+         && $inscripcion->certificadoAprobado()
+         && in_array($curso->id_estado, [EstadoCurso::FINALIZADO->value, EstadoCurso::CERRADO->value]);
 ```

---

### Rutas

#### [MODIFY] `Modules/Taller/Routes/web.php`

Agregar dentro del grupo `auth` (después de las rutas de certificados existentes, línea ~150):

```php
// ── Panel de Certificación (Aprobación por Facilitador) ──
Route::get('/cursos/{curso}/certificacion', [\Modules\Taller\Http\Controllers\CertificacionPanelController::class, 'index'])
    ->name('taller.certificacion.panel');
Route::post('/cursos/{curso}/certificacion/{inscripcion}/aprobar', [\Modules\Taller\Http\Controllers\CertificacionPanelController::class, 'aprobar'])
    ->name('taller.certificacion.aprobar');
Route::post('/cursos/{curso}/certificacion/{inscripcion}/denegar', [\Modules\Taller\Http\Controllers\CertificacionPanelController::class, 'denegar'])
    ->name('taller.certificacion.denegar');
```

---

### Lógica de Capacidades

#### [MODIFY] `Modules/Taller/Services/CondicionalEstadoCurso.php`

Agregar nueva capacidad `gestionar_certificacion` en el mapa:

```diff
 8 => [ // Finalizado
     'acceder_contenido' => 'participante',
     'emitir_certificado' => 'participante',
     'gestionar' => 'gestion',
     'cerrar_curso' => 'gestion',
     'consultar_asistencia' => 'operativo',
 ],
```

Y agregar en los casos especiales (después de `ajustar_certificado`, ~línea 140):

```diff
 // Capacidad de ajustar certificado (Solo para Gestión o Facilitador)
 if ($esGestor || $esOperativo) {
     if (in_array($estadoId, [5, 6, 7, 8])) {
         $misCapacidades[] = 'ajustar_certificado';
     }
 }

+// Capacidad de gestionar certificación (Solo Facilitador y Gestor, en Finalizado o Cerrado)
+if ($esGestor || $esOperativo) {
+    if (in_array($estadoId, [8, 9])) {
+        $misCapacidades[] = 'gestionar_certificacion';
+    }
+}
```

---

### Vistas Blade

#### [NEW] `Modules/Taller/Resources/views/a/CertificacionPanel.blade.php`

Vista principal del landing de revisión de certificación. Layout: `layouts.kaiadmin-menu`.

**Estructura de la vista:**

```
┌─────────────────────────────────────────────────────────────────────┐
│  Header: "Panel de Certificación" + Nombre del Curso                │
│  Badges: Total participantes / Aprobados / Pendientes / Denegados    │
├─────────────────────────────────────────────────────────────────────┤
│  [← Volver al Curso]                                                │
├─────────────────────────────────────────────────────────────────────┤
│  Tabla de Participantes:                                             │
│  ┌─────────────┬──────────┬────────────┬────────────┬──────────┐    │
│  │ Participante │ Asist. % │ Promedio   │ Estado     │ Acciones │    │
│  ├─────────────┼──────────┼────────────┼────────────┼──────────┤    │
│  │ Juan Pérez   │ 90%  ■■■ │ 85.50/100 │ 🟡Pendiente│ ✅ ❌    │    │
│  │ María López  │ 100% ■■■ │ 92.00/100 │ 🟢Aprobado │ ↩ ❌    │    │
│  │ Luis García  │ 40%  ■░░ │ 45.00/100 │ 🔴Denegado │ ✅ ↩    │    │
│  └─────────────┴──────────┴────────────┴────────────┴──────────┘    │
│                                                                      │
│  Fila expandible (click en participante):                            │
│  ┌──────────────────────────────────────────────────────────────┐    │
│  │ CLASES (10 total):                                           │    │
│  │ ┌──────────────────┬────────┐                                │    │
│  │ │ Clase 1 - Tema X │  ✅    │  ← asistió                    │    │
│  │ │ Clase 2 - Tema Y │  ✅    │                                │    │
│  │ │ Clase 3 - Tema Z │  ❌    │  ← faltó                      │    │
│  │ │ Clase 4 - Tema W │  ✅    │                                │    │
│  │ │ ...               │       │                                │    │
│  │ └──────────────────┴────────┘                                │    │
│  │ Asistencia: 9/10 = 90%                                       │    │
│  │                                                              │    │
│  │ EVALUACIONES:                                                │    │
│  │ Examen 1: 85 | Trabajo: 90 | Final: 78                      │    │
│  │ Promedio: 85.50/100                                          │    │
│  └──────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────┘
```

**Diseño visual:** Reutilizar el estilo de `AsistenciaConsolidado.blade.php` (header, tabla responsive, badges, hover effects). Los porcentajes de asistencia usan barras de progreso con colores semáforo:
- ≥ 75%: verde (`bg-success`)
- ≥ 50%: amarillo (`bg-warning`)
- < 50%: rojo (`bg-danger`)

Los promedios usan el mismo esquema de colores.

**Interacciones JavaScript:**
- Aprobar/Denegar individual: `fetch()` con AJAX → actualizar fila sin recargar
- Modal de denegación: SweetAlert con textarea para motivo
- Fila expandible: toggle con animación slide, muestra detalle de clases con ✅/❌

---

#### [MODIFY] `Modules/Taller/Resources/views/a/partials/curso-actions/actions-container.blade.php`

1. Agregar `gestionar_certificacion` a la lista de capacidades de gestión:

```diff
 $gestion = ['ver_participantes', 'ajustar_certificado', 'aceptar_asignacion', 'rechazar_asignacion',
-    'editar', 'enviar_aprobacion', 'aprobar', 'rechazar', 'finalizar_inscripciones', 'finalizar_curso', 'cerrar_curso', 'ver_motivo', 'en_revision'];
+    'editar', 'enviar_aprobacion', 'aprobar', 'rechazar', 'finalizar_inscripciones', 'finalizar_curso', 'cerrar_curso', 'ver_motivo', 'en_revision', 'gestionar_certificacion'];
```

2. Agregar el case para el botón en la zona de gestión:

```php
@case('gestionar_certificacion')
    <a href="{{ route('taller.certificacion.panel', $curso->crypt_id) }}" 
       class="btn btn-outline-success btn-sm py-2 fw-bold border shadow-xs rounded-pill">
        <i class="fas fa-clipboard-check me-1"></i> Panel de Certificación
    </a>
    @break
```

3. Modificar el case `emitir_certificado` para condicionar al estado de la certificación:

```php
@case('emitir_certificado')
    @if(isset($inscripcion) && $inscripcion->certificadoAprobado())
        {{-- Certificado aprobado: botón activo --}}
        <a class="btn btn-gold w-100 fw-bold py-3 rounded-pill shadow-sm hvr-push border-0" 
           style="background: linear-gradient(135deg, #d4af37 0%, #f9d71c 100%); color: #000;" 
           href="{{ route('taller.certificados.descargar', $curso->crypt_id) }}">
            <i class="fas fa-award me-2"></i> Obtener Certificado
        </a>
    @elseif(isset($inscripcion) && $inscripcion->certificadoDenegado())
        {{-- Certificado denegado: botón rojo deshabilitado --}}
        <div class="alert alert-danger text-center small p-3 mb-0 rounded-4 border-0">
            <i class="fas fa-times-circle fa-2x mb-2 d-block text-danger opacity-60"></i>
            <strong class="d-block mb-1">Certificado No Aprobado</strong>
            <p class="mb-0 opacity-75">{{ $inscripcion->certificado_motivo_denegacion }}</p>
        </div>
    @else
        {{-- Certificado pendiente: botón bloqueado --}}
        <div class="alert alert-secondary text-center small p-3 mb-0 rounded-4 border-0">
            <i class="fas fa-hourglass-half fa-2x mb-2 d-block opacity-40"></i>
            <strong class="d-block mb-1">Certificado en Revisión</strong>
            <p class="mb-0 opacity-75">Tu certificado está siendo evaluado por el facilitador.</p>
        </div>
    @endif
    @break
```

> **NOTA:** La variable `$inscripcion` ya se pasa a la vista desde `CursoDetalleController::show()`.

---

## Resumen de Archivos

| Acción | Archivo |
|--------|---------|
| **[NEW]** | `Modules/Taller/Database/Migrations/YYYY_MM_DD_HHMMSS_add_certificado_aprobado_to_inscripciones.php` |
| **[NEW]** | `Modules/Taller/Http/Controllers/CertificacionPanelController.php` |
| **[NEW]** | `Modules/Taller/Resources/views/a/CertificacionPanel.blade.php` |
| **[NEW]** | `Modules/Taller/Resources/views/emails/certificacion_aprobada.blade.php` |
| **[NEW]** | `Modules/Taller/Resources/views/emails/certificacion_rechazada.blade.php` |
| **[MODIFY]** | `Modules/Taller/Entities/Inscripcion.php` (4 campos fillable + 3 helpers + 1 relación + casts) |
| **[MODIFY]** | `Modules/Taller/Http/Controllers/CertificadoController.php` (validación en `descargar()` + `verificar()`) |
| **[MODIFY]** | `Modules/Taller/Services/CondicionalEstadoCurso.php` (nueva capacidad `gestionar_certificacion`) |
| **[MODIFY]** | `Modules/Taller/Routes/web.php` (3 nuevas rutas) |
| **[MODIFY]** | `Modules/Taller/Resources/views/a/partials/curso-actions/actions-container.blade.php` (botón panel + condicional certificado) |

---

## Patrones de Referencia del Código Existente

Este plan reutiliza los siguientes patrones ya implementados:

| Patrón | Referencia Original | Uso en este Plan |
|--------|---------------------|------------------|
| Verificación de permisos (gestor/facilitador) | `AsistenciaController::verificarPermisoGestion()` | `CertificacionPanelController::verificarPermisoGestion()` |
| Cálculo de promedio ponderado | `CursoDetalleController::calcularPromedioEstudiante()` | Panel de certificación (misma fórmula) |
| Tabla de asistencia consolidada | `AsistenciaConsolidado.blade.php` | Vista del panel (mismo estilo visual) |
| Acciones AJAX con SweetAlert | `actions-container.blade.php` (aprobar/rechazar) | Aprobar/denegar certificación |
| Capacidades por estado | `CondicionalEstadoCurso::MAPA_CAPACIDADES` | Nueva capacidad `gestionar_certificacion` |
| Campo boolean nullable (tri-state) | Patrón estándar Laravel | `certificado_aprobado`: NULL/true/false |
| Envío de email vía closure | `PostulacionFacilitadorController` (Mail::send) | Notificación de aprobación/denegación |

---

## Preguntas Abiertas

1. **Estado Cerrado (9):** ✅ RESUELTO — El facilitador/coordinador puede gestionar certificaciones tanto en estado Finalizado (8) como Cerrado (9). La capacidad `gestionar_certificacion` se agrega para ambos estados en `CondicionalEstadoCurso`.

2. **~~Umbrales por defecto:~~** ELIMINADO — Se removieron las acciones masivas ("Aprobar Todos" / "Aprobar con Mínimos"). El facilitador aprueba/deniega individualmente tras revisar cada participante.

3. **Notificación al participante:** ✅ RESUELTO — Se envían emails de notificación al participante cuando su certificación es aprobada o denegada. Se crean 2 vistas de email: `certificacion_aprobada.blade.php` y `certificacion_rechazada.blade.php`. El destinatario se obtiene via `$inscripcion->persona->user->email` (el email vive en `security.users`, NO en `comun.personas`).

4. **Re-aprobación:** ✅ RESUELTO — Sí es posible. El facilitador puede cambiar la decisión de denegado a aprobado en cualquier momento (el botón "Aprobar" aparece en estados pendiente y denegado).

---

## Verificación

### Automated Tests
```bash
php artisan migrate
php artisan route:list --path=certificacion
```

### Manual Verification
1. **Como Facilitador (curso Finalizado):** Verificar que aparece "Panel de Certificación" en el detalle → Abrir panel → Ver tabla con participantes, asistencia, promedio → Expandir fila → Ver detalle de clases asistidas/faltantes con ✅/❌ → Aprobar uno individualmente → Denegar otro con motivo
2. **Como Participante (certificado pendiente):** Verificar que el botón "Emitir Certificado" está bloqueado con mensaje "en revisión"
3. **Como Participante (certificado aprobado):** Verificar que el botón dorado "Obtener Certificado" funciona y descarga el PDF
4. **Como Participante (certificado denegado):** Verificar que se muestra el motivo de denegación
5. **Seguridad:** Verificar que un participante NO puede acceder al panel de certificación (403)
6. **Descarga directa por URL:** Verificar que intentar descargar el PDF vía URL directa sin certificación aprobada retorna error
7. **Verificación externa:** Verificar que el endpoint `/verificar/{codigo}` rechaza certificados no aprobados
8. **Emails:** Verificar que se envía email al aprobar/denegar (revisar logs si no hay SMTP configurado)
9. **Backward-compat:** Verificar que inscripciones existentes de cursos finalizados mantienen capacidad de descarga

---

## Notas de Verificación (Auditoría vs Código Fuente)

> Verificación realizada el 2026-07-27 contra el código fuente actual del proyecto.

### ✅ Verificado y Correcto

| Punto del Plan | Verificado contra | Estado |
|---|---|---|
| Tabla `taller.inscripciones` existe con columna `estado` (varchar 20) | Migración `create_taller_tables.php` L124-137 | ✅ Correcto |
| Modelo `Inscripcion` tiene `$fillable` para `estado`, `motivo_estado` | `Inscripcion.php` L16-24 | ✅ Correcto |
| Los estados de inscripción son strings: 'postulado', 'aprobado', etc. | `Inscripcion.php` constantes L27-30 | ✅ Correcto |
| Tabla `taller.calificaciones` con `id_persona`, `id_contenido_curso`, `calificacion` | Migración L150-165 | ✅ Correcto |
| `CursoDetalleController` ya pasa `$inscripcion` a la vista | `show()` L61-64 | ✅ Correcto |
| Cálculo de promedio ponderado: `(calificacion * ponderacion) / 100` | `calcularPromedioEstudiante()` L184-195 | ✅ Correcto |
| `AsistenciaController::consolidado()` ya calcula asistencias por persona | `AsistenciaController` L23-55 | ✅ Correcto |
| `actions-container.blade.php` ya maneja `$inscripcion` para mostrar estados | Vista L16-60 | ✅ Correcto |
| Middleware `decrypt_id` se aplica globalmente al grupo de rutas Taller | `web.php` L29 | ✅ Correcto |
| `BaseController` provee `getUsuarioAutenticado()` | Patrón usado por todos los controllers | ✅ Correcto |

### 📋 Dependencias a Considerar

1. **Backward-compat:** La migración incluye un UPDATE masivo que marca `certificado_aprobado = true` en todas las inscripciones existentes con estado `aprobado`. Esto preserva la funcionalidad de descarga para cursos ya finalizados.
2. **Email en `comun.personas`:** La tabla `comun.personas` NO tiene columna `email`. El email vive en `security.users.email`. Se accede via `$inscripcion->persona->user->email`. NOTA: `InscripcionController::store()` línea 53 usa `$user->personalData->email` — este es un bug existente en el código actual.
3. **FK constraint:** La migración incluye `foreign('certificado_aprobado_por')->references('id')->on('security.users')->onDelete('set null')` consistente con el patrón del proyecto.
4. **Rutas:** Solo 3 rutas nuevas (panel, aprobar, denegar). Se eliminaron las rutas masivas.
5. **`finalizado-estudiante.blade.php`:** No existe en el proyecto. El botón del certificado se maneja íntegramente en `actions-container.blade.php`.
6. **`Calificacion` model:** No existe Eloquent model para `taller.calificaciones`. Se accede via `DB::table()` consistente con el patrón del proyecto.
