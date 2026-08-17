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
    │       ├── Aprobar certificación individual → certificado_aprobado = true
    │       ├── Aprobar TODOS (masivo) → marca todos los pendientes
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
    │   │       ├── Lista de actividades con ✅/❌ asistencia por cada una
    │   │       └── Lista de evaluaciones con nota individual
    │   │   
    │   ├── Botón masivo "Aprobar quienes cumplan mínimos"
    │   │   (ej: asistencia ≥ 100% Y promedio ≥ 60 → configurable)
    │   │
    │   └── Boton aprobacion individual "Aprobar un estudiante en especifico"
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
    $table->timestamp('certificado_fecha_aprobacion')->nullable();
    $table->text('certificado_motivo_denegacion')->nullable();
});
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

Extiende `BaseController`. Métodos:

| Método | Ruta | HTTP | Descripción |
|--------|------|------|-------------|
| `index($id_curso)` | `/cursos/{curso}/certificacion` | GET | Landing de revisión: tabla con participantes, asistencia, calificaciones, estado cert. |
| `aprobar(Request, $id_curso, $id_inscripcion)` | `/cursos/{curso}/certificacion/{inscripcion}/aprobar` | POST | Aprueba certificación individual |
| `denegar(Request, $id_curso, $id_inscripcion)` | `/cursos/{curso}/certificacion/{inscripcion}/denegar` | POST | Deniega certificación con motivo |
| `aprobarMasivo(Request, $id_curso)` | `/cursos/{curso}/certificacion/aprobar-masivo` | POST | Aprueba todos los pendientes |
| `aprobarConMinimos(Request, $id_curso)` | `/cursos/{curso}/certificacion/aprobar-minimos` | POST | Aprueba quienes cumplan umbral |

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

    return back()->with('success', 'Certificación denegada correctamente.');
}
```

**Detalle del método `aprobarMasivo()`:**

```php
public function aprobarMasivo(Request $request, $id_curso)
{
    $curso = Curso::findOrFail($id_curso);
    $this->verificarPermisoGestion($curso);

    $updated = Inscripcion::where('id_curso', $id_curso)
        ->where('estado', Inscripcion::ESTADO_APROBADO)
        ->whereNull('certificado_aprobado')
        ->update([
            'certificado_aprobado' => true,
            'certificado_aprobado_por' => auth()->id(),
            'certificado_fecha_aprobacion' => now(),
        ]);

    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => "$updated certificaciones aprobadas masivamente."
        ]);
    }

    return back()->with('success', "$updated certificaciones aprobadas masivamente.");
}
```

**Detalle del método `aprobarConMinimos()`:**

```php
public function aprobarConMinimos(Request $request, $id_curso)
{
    $curso = Curso::findOrFail($id_curso);
    $this->verificarPermisoGestion($curso);

    $minAsistencia = $request->input('min_asistencia', 75); // % mínimo
    $minPromedio = $request->input('min_promedio', 60);      // Nota mínima

    // Recalcular para cada participante pendiente
    // ... misma lógica que index() pero filtrando por umbrales
    // Actualizar solo los que cumplen ambos criterios

    return back()->with('success', "Certificaciones aprobadas para quienes cumplen los mínimos.");
}
```

**Método auxiliar reutilizado:**

```php
private function verificarPermisoGestion(Curso $curso): void
{
    $personalData = $this->getUsuarioAutenticado()->personalData;
    $esGestor = hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO);
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
Route::post('/cursos/{curso}/certificacion/aprobar-masivo', [\Modules\Taller\Http\Controllers\CertificacionPanelController::class, 'aprobarMasivo'])
    ->name('taller.certificacion.aprobar-masivo');
Route::post('/cursos/{curso}/certificacion/aprobar-minimos', [\Modules\Taller\Http\Controllers\CertificacionPanelController::class, 'aprobarConMinimos'])
    ->name('taller.certificacion.aprobar-minimos');
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
+    'gestionar_certificacion' => 'operativo', // Facilitador accede al panel
 ],
```

Y también en los casos especiales (después de `ajustar_certificado`, ~línea 140):

```diff
 // Capacidad de ajustar certificado (Solo para Gestión o Facilitador)
 if ($esGestor || $esOperativo) {
     if (in_array($estadoId, [5, 6, 7, 8])) {
         $misCapacidades[] = 'ajustar_certificado';
     }
 }

+// Capacidad de gestionar certificación (Solo Facilitador y Gestor, en Finalizado)
+if ($esGestor || $esOperativo) {
+    if ($estadoId == 8) {
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
┌─────────────────────────────────────────────────────────────────┐
│  Header: "Panel de Certificación" + Nombre del Curso            │
│  Badges: Total participantes / Aprobados / Pendientes / Denegados│
├─────────────────────────────────────────────────────────────────┤
│  Barra de acciones masivas:                                      │
│  [✅ Aprobar Todos] [🎯 Aprobar con Mínimos ▼] [← Volver]      │
│                           Modal con inputs:                      │
│                           - % Asistencia mínima (default 75)     │
│                           - Nota mínima (default 60)             │
├─────────────────────────────────────────────────────────────────┤
│  Tabla de Participantes:                                         │
│  ┌─────────────┬──────────┬────────────┬────────────┬──────────┐│
│  │ Participante │ Asist. % │ Promedio   │ Estado     │ Acciones ││
│  ├─────────────┼──────────┼────────────┼────────────┼──────────┤│
│  │ Juan Pérez   │ 90%  ■■■ │ 85.50/100 │ 🟡Pendiente│ ✅ ❌ 👁 ││
│  │ María López  │ 100% ■■■ │ 92.00/100 │ 🟢Aprobado │ ↩ 👁    ││
│  │ Luis García  │ 40%  ■░░ │ 45.00/100 │ 🔴Denegado │ ↩ 👁    ││
│  └─────────────┴──────────┴────────────┴────────────┴──────────┘│
│                                                                  │
│  Detalle expandible (por participante):                          │
│  ┌──────────────────────────────────────────────────────────────┐│
│  │ Actividades:  Tema 1 ✅ | Tema 2 ✅ | Tema 3 ❌ | Tema 4 ✅ ││
│  │ Evaluaciones: Examen 1: 85 | Trabajo: 90 | Final: 78        ││
│  └──────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

**Diseño visual:** Reutilizar el estilo de `AsistenciaConsolidado.blade.php` (header, tabla responsive, badges, hover effects). Los porcentajes de asistencia usan barras de progreso con colores semáforo:
- ≥ 75%: verde (`bg-success`)
- ≥ 50%: amarillo (`bg-warning`)
- < 50%: rojo (`bg-danger`)

Los promedios usan el mismo esquema de colores.

**Interacciones JavaScript:**
- Aprobar/Denegar individual: `fetch()` con AJAX → actualizar fila sin recargar
- Modal de denegación: SweetAlert con textarea para motivo
- Aprobar masivo: SweetAlert de confirmación → `fetch()` → recargar tabla
- Detalle expandible: toggle con animación slide

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

#### [MODIFY] `Modules/Taller/Resources/views/a/partials/curso-actions/finalizado-estudiante.blade.php`

> **NOTA:** Este archivo actualmente tiene un botón con `href="#"` (no funcional). Aunque el sistema usa `actions-container.blade.php`, lo actualizamos por consistencia por si se usa como fallback en algún contexto.

```blade
{{-- Estado 8: Finalizado - Estudiante --}}
{{-- El estudiante puede ver contenidos y emitir su certificado (si fue aprobado) --}}

<a class="btn btn-info w-100 mb-2" href="{{ route('taller.cursos.contenido', ['curso' => $curso->crypt_id]) }}">
    <i class="fas fa-eye me-2"></i> Ver contenidos
</a>

@if(isset($inscripcion) && $inscripcion->certificadoAprobado())
    <a class="btn btn-success w-100 mb-2" href="{{ route('taller.certificados.descargar', $curso->crypt_id) }}">
        <i class="fas fa-certificate me-2"></i> Emitir Certificado
    </a>
@else
    <button class="btn btn-secondary w-100 mb-2" disabled>
        <i class="fas fa-lock me-2"></i> Certificado en Revisión
    </button>
@endif
```

---

## Resumen de Archivos

| Acción | Archivo |
|--------|---------|
| **[NEW]** | `Modules/Taller/Database/Migrations/YYYY_MM_DD_HHMMSS_add_certificado_aprobado_to_inscripciones.php` |
| **[NEW]** | `Modules/Taller/Http/Controllers/CertificacionPanelController.php` |
| **[NEW]** | `Modules/Taller/Resources/views/a/CertificacionPanel.blade.php` |
| **[MODIFY]** | `Modules/Taller/Entities/Inscripcion.php` (4 campos fillable + 3 helpers + 1 relación + casts) |
| **[MODIFY]** | `Modules/Taller/Http/Controllers/CertificadoController.php` (validación adicional en `descargar()`) |
| **[MODIFY]** | `Modules/Taller/Services/CondicionalEstadoCurso.php` (nueva capacidad `gestionar_certificacion`) |
| **[MODIFY]** | `Modules/Taller/Routes/web.php` (5 nuevas rutas) |
| **[MODIFY]** | `Modules/Taller/Resources/views/a/partials/curso-actions/actions-container.blade.php` (botón panel + condicional certificado) |
| **[MODIFY]** | `Modules/Taller/Resources/views/a/partials/curso-actions/finalizado-estudiante.blade.php` (condicional certificado) |

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

---

## Preguntas Abiertas

1. **Estado Cerrado (9):** ¿Debería el facilitador/coordinador poder aprobar certificaciones cuando el curso ya está en estado Cerrado? Actualmente el plan solo permite gestión en estado Finalizado (8). Si se requiere en Cerrado, se agrega `9` al mapa de capacidades.

2. **Umbrales por defecto:** Los valores de "Aprobar con mínimos" (75% asistencia, 60 promedio) ¿deben ser configurables por curso o son valores fijos globales?

3. **Notificación al participante:** ¿Desean que se envíe un email al participante cuando su certificación es aprobada o denegada? Si sí, se agregarían vistas de email similar al patrón de postulación de facilitador.

4. **Re-aprobación:** Si un certificado fue denegado, ¿el facilitador puede cambiar la decisión a aprobado? En el plan actual sí es posible (el botón "Aprobar" aparece en ambos estados pendiente/denegado para el facilitador).

---

## Verificación

### Automated Tests
```bash
php artisan migrate
php artisan route:list --path=certificacion
```

### Manual Verification
1. **Como Facilitador (curso Finalizado):** Verificar que aparece "Panel de Certificación" en el detalle → Abrir panel → Ver tabla con participantes, asistencia, promedio → Aprobar uno individualmente → Denegar otro con motivo
2. **Como Participante (certificado pendiente):** Verificar que el botón "Emitir Certificado" está bloqueado con mensaje "en revisión"
3. **Como Participante (certificado aprobado):** Verificar que el botón dorado "Obtener Certificado" funciona y descarga el PDF
4. **Como Participante (certificado denegado):** Verificar que se muestra el motivo de denegación
5. **Aprobación masiva:** Verificar "Aprobar Todos" → Todos los pendientes cambian a aprobados
6. **Aprobación con mínimos:** Verificar que solo se aprueban quienes cumplan los umbrales
7. **Seguridad:** Verificar que un participante NO puede acceder al panel de certificación (403)
8. **Descarga directa por URL:** Verificar que intentar descargar el PDF vía URL directa sin certificación aprobada retorna error

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

1. **La migración debe ejecutarse en la BD existente** sin afectar inscripciones previas. Las 4 nuevas columnas son `nullable`, por lo que inscripciones existentes tendrán `certificado_aprobado = NULL` (pendiente por defecto).
2. **Cursos ya cerrados (Estado 9):** Los participantes de cursos cerrados que ya descargaron su certificado seguirán funcionando, ya que `CertificadoController::descargar()` valida `certificadoAprobado()` que retorna `false` para `NULL`. **Esto rompe la compatibilidad.** Se necesita una decisión:
   - **Opción A:** Ejecutar un UPDATE masivo para marcar `certificado_aprobado = true` en todas las inscripciones de cursos con estado 8 o 9 que ya existen.
   - **Opción B:** En `CertificadoController::descargar()`, permitir descarga si `certificado_aprobado IS NULL AND curso en estado 9 (Cerrado)` como fallback de compatibilidad.
   - **Recomendación:** Opción A (en la misma migración o seeder dedicado).
