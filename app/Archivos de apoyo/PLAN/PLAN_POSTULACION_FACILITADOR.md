# Postulación a Facilitador — Plan de Implementación

> **Fecha de creación:** 2026-07-27
> **Estado:** Pendiente de implementación
> **Módulo:** Taller

## Descripción

Implementar un sistema de **postulación a facilitador** dentro del módulo Taller. Los participantes que **no tengan el perfil de Facilitador** podrán postularse entregando recaudos definidos por coordinadores. El sistema reutiliza el patrón existente de requisitos/respuestas usado en inscripciones de cursos.

**Dos vistas principales:**
1. **Landing público (participantes):** Visible en el menú Formación para usuarios sin perfil Facilitador. Muestra un landing con contexto sobre el rol de facilitador, lista de requisitos y formulario de carga de documentos.
2. **Panel administrativo (coordinadores):** Visible en el menú Administración. Permite gestionar los requisitos y ver una vista previa del landing.

---

## Flujo de Usuario

```
Participante (sin perfil Facilitador)
    │
    ├── Ve en menú lateral: "Ser Facilitador" (Formación)
    │
    ├── Entra al Landing → Lee deberes, responsabilidades y requisitos
    │
    ├── Clic "Postularme" → Formulario de carga de documentos
    │   (replica el patrón de CursoInscribirse.blade.php)
    │
    ├── Envía → Estado: "pendiente" → Email de confirmación
    │
    ├── Coordinador revisa → Aprueba (asigna perfil Facilitador) / Rechaza (con motivo)
    │   │
    │   ├── Aprobada → Email de aprobación → Ya es Facilitador
    │   │
    │   └── Rechazada → Email de rechazo → Documentos eliminados del storage
    │       │
    │       └── Participante puede re-postularse (sin límite de intentos)
    │           → Nuevo formulario → Estado: "pendiente"
    │
    └── Si tiene postulación pendiente: muestra estado (no puede postularse de nuevo)

Coordinador / Administrador
    │
    ├── Ve en menú lateral: "Requisitos Facilitador" (Administración)
    │
    ├── CRUD de requisitos (tipo documento/pregunta/recurso)
    │
    ├── Vista previa del landing
    │
    └── Listado de postulaciones → Ver documentos / Aprobar / Rechazar
```

---

## Proposed Changes

### Base de Datos (Migración)

#### [NEW] Migración: `create_postulacion_facilitador_tables.php`

Crea 3 tablas nuevas en el schema `taller`:

**`taller.requisitos_facilitador`** — Requisitos globales (no ligados a un curso)
| Columna | Tipo | Nullable | Notas |
|---------|------|----------|-------|
| `id_requisito_facilitador` | bigint PK | NO | |
| `tipo` | varchar(50) | NO | `'pregunta'`, `'recurso'`, `'documento'` |
| `titulo` | varchar(255) | NO | |
| `descripcion` | text | SÍ | |
| `obligatorio` | boolean | NO | Default: `true` |
| `orden` | integer | NO | Default: `0` (para ordenar) |
| `activo` | boolean | NO | Default: `true` |
| `creado_por` | bigint | NO | FK → `security.users.id` |
| `creado_en` | timestamp | NO | |
| `actualizado_en` | timestamp | NO | |

**`taller.postulaciones_facilitador`** — Registro de cada postulación
| Columna | Tipo | Nullable | Notas |
|---------|------|----------|-------|
| `id_postulacion` | bigint PK | NO | |
| `id_persona` | bigint FK | NO | FK → `comun.personas` |
| `estado` | varchar(20) | NO | `'pendiente'`, `'aprobada'`, `'rechazada'` |
| `motivo_rechazo` | text | SÍ | |
| `revisada_por` | bigint | SÍ | FK → `security.users.id` |
| `fecha_revision` | timestamp | SÍ | |
| `creado_en` | timestamp | NO | |
| `actualizado_en` | timestamp | NO | |

> **IMPORTANTE:** Unique constraint en `(id_persona, estado)` con condición `WHERE estado = 'pendiente'` para evitar postulaciones duplicadas activas. Si no es viable en migración estándar, se valida en el controller.
>
> **NOTA DE VERIFICACIÓN:** PostgreSQL soporta índices parciales. Se puede usar:
> `$table->unique(['id_persona'], 'uniq_postulacion_pendiente')->where('estado', 'pendiente');`
> O alternativamente en raw SQL: `CREATE UNIQUE INDEX ... WHERE estado = 'pendiente'`
>
> **ÍNDICE ADICIONAL:** Agregar índice en `id_persona` para búsquedas por persona:
> `$table->index('id_persona', 'idx_postulaciones_persona');`

**`taller.postulacion_respuestas`** — Respuestas/archivos del postulante
| Columna | Tipo | Nullable | Notas |
|---------|------|----------|-------|
| `id_respuesta` | bigint PK | NO | |
| `id_postulacion` | bigint FK | NO | FK → `postulaciones_facilitador` ON DELETE CASCADE |
| `id_requisito_facilitador` | bigint FK | NO | FK → `requisitos_facilitador` ON DELETE CASCADE |
| `respuesta_texto` | text | SÍ | Para tipo `'pregunta'` |
| `ruta_archivo` | varchar(255) | SÍ | Para tipo `'documento'` |
| `creado_en` | timestamp | NO | |
| `actualizado_en` | timestamp | NO | |

---

### Modelos Eloquent (Entities)

> **NOTA DE VERIFICACIÓN:** La tabla existente `taller.curso_requisitos` usa `$table->timestamps()` (created_at/updated_at estándar de Laravel), NO las columnas personalizadas `creado_en`/`actualizado_en`.
> Sin embargo, otros modelos del sistema SÍ usan `creado_en`/`actualizado_en` (ej: `Asistencia`, `AsistenciaToken`).
> **Decisión:** Las nuevas tablas usarán `creado_en`/`actualizado_en` para ser consistentes con las tablas más recientes del módulo Taller. Los modelos deben definir `const CREATED_AT = 'creado_en'; const UPDATED_AT = 'actualizado_en';`

#### [NEW] `Modules/Taller/Entities/RequisitoFacilitador.php`
- `$table = 'taller.requisitos_facilitador'`
- `$primaryKey = 'id_requisito_facilitador'`
- Timestamps: `const CREATED_AT = 'creado_en'; const UPDATED_AT = 'actualizado_en';`
- Relaciones: `respuestas()`, `creador()`
- Scope: `scopeActivos()` → `where('activo', true)`
- `$appends = ['crypt_id']` + `getCryptIdAttribute()` usando `Encryptor::encrypt($this->id_requisito_facilitador)`

#### [NEW] `Modules/Taller/Entities/PostulacionFacilitador.php`
- `$table = 'taller.postulaciones_facilitador'`
- `$primaryKey = 'id_postulacion'`
- Timestamps: `creado_en` / `actualizado_en`
- Constantes: `ESTADO_PENDIENTE`, `ESTADO_APROBADA`, `ESTADO_RECHAZADA`
- Relaciones: `persona()`, `respuestas()`, `revisor()`
- Helpers: `esPendiente()`, `esAprobada()`, `esRechazada()`
- `$appends = ['crypt_id']`

#### [NEW] `Modules/Taller/Entities/PostulacionRespuesta.php`
- `$table = 'taller.postulacion_respuestas'`
- `$primaryKey = 'id_respuesta'`
- Relaciones: `postulacion()`, `requisito()`

---

### RBAC (Permisos)

#### [MODIFY] `app/Constants/SecurityAction.php`
Agregar 2 nuevas acciones:

| ID | Constante | Slug DB | Uso |
|----|-----------|---------|-----|
| 24 | `POSTULARSE_FACILITADOR` | `apply_facilitator` | Participante: ver landing y postularse |
| 25 | `GESTIONAR_POSTULACIONES_FACILITADOR` | `manage_facilitator_applications` | Coordinador: CRUD requisitos, revisar postulaciones |

#### [NEW] Seeder: `database/seeders/PostulacionFacilitadorPermissionsSeeder.php`

> **NOTA DE VERIFICACIÓN — IDs de Procesos:**
> Los `process_id` en `security.processes` son **autoincrementales** (PK `id` con serial).
> Actualmente existen 7 procesos (IDs 1-7 según `ProcessesSeeder.php`).
> Los nuevos procesos recibirán IDs autogenerados (probablemente 8 y 9), pero **NO se deben hardcodear**.
> El seeder debe usar `DB::table('security.processes')->insertGetId(...)` para obtener el ID real y usarlo al crear permisos.

> **NOTA DE VERIFICACIÓN — IDs de Menús (confirmados):**
> - menu_id 1 → Seguridad
> - menu_id 2 → Formación ← Para "Ser Facilitador"
> - menu_id 3 → Administración ← Para "Requisitos Facilitador"
> (Verificado en `ModulesMenusSeeder.php` y `ProcessesSeeder.php`)

- Crea un **nuevo proceso** en `security.processes`:
  - Nombre: `Ser Facilitador` | Ruta: `taller.postulacion-facilitador.landing` | menu_id: 2 (Formación)
  - Actions: `view|apply_facilitator`
- Crea un **nuevo proceso** en `security.processes`:
  - Nombre: `Requisitos Facilitador` | Ruta: `taller.postulacion-facilitador.admin` | menu_id: 3 (Administración)
  - Actions: `view|manage_facilitator_applications`
- Crea los permisos en `security.permissions` usando los IDs obtenidos de `insertGetId()`
- Asigna al perfil Participante (3): `view` + `apply_facilitator` del proceso "Ser Facilitador"
- Asigna al perfil Coordinador (4) y Admin (1): `view` + `manage_facilitator_applications` del proceso "Requisitos Facilitador"

> **IMPORTANTE:** El proceso "Ser Facilitador" en el sidebar solo debe ser visible para perfiles que NO sean Facilitador. La visibilidad por sidebar se controla por `profile_permissions`, por lo que al asignar el permiso solo al perfil Participante, solo estos lo verán. Cuando un participante sea aprobado y reciba el perfil Facilitador, al cambiar de perfil en sesión dejará de ver la opción.

---

### Controllers

#### [NEW] `Modules/Taller/Http/Controllers/PostulacionFacilitadorController.php`

Extiende `BaseController`. Métodos:

| Método | Ruta | Descripción |
|--------|------|-------------|
| `landing()` | GET `/postulacion-facilitador` | Landing informativo con deberes, requisitos y botón "Postularme" |
| `formulario()` | GET `/postulacion-facilitador/postularse` | Formulario con los requisitos activos (replica `CursoInscribirse`) |
| `postular(Request)` | POST `/postulacion-facilitador/postularse` | Procesa la postulación: valida, sube archivos, crea registros |
| `adminIndex()` | GET `/postulacion-facilitador/admin` | Panel admin: requisitos CRUD + listado postulaciones |
| `storeRequisito(Request)` | POST `/postulacion-facilitador/requisitos` | Crear requisito |
| `updateRequisito(Request, $id)` | PUT `/postulacion-facilitador/requisitos/{id}` | Actualizar requisito |
| `toggleRequisito($id)` | PATCH `/postulacion-facilitador/requisitos/{id}/toggle` | Activar/desactivar requisito |
| `aprobar($id)` | POST `/postulacion-facilitador/{id}/aprobar` | Aprueba postulación → asigna perfil Facilitador |
| `rechazar(Request, $id)` | POST `/postulacion-facilitador/{id}/rechazar` | Rechaza postulación con motivo |
| `verDocumentos($id)` | GET `/postulacion-facilitador/{id}/documentos` | Ver documentos del postulante |
| `descargarDocumento($id)` | GET `/postulacion-facilitador/documento/{id}/descargar` | Descargar archivo adjunto |
| `previewLanding()` | GET `/postulacion-facilitador/admin/preview` | Vista previa del landing (para coordinadores) |

**Lógica de aprobación:**
```php
// Al aprobar (ENVUELTO EN DB::transaction()):
DB::beginTransaction();
try {
    // 1. Verificar que el usuario NO tenga ya el perfil Facilitador
    $user = $postulacion->persona->user;
    if ($user->hasRole('facilitador')) {
        DB::rollBack();
        return back()->with('error', 'Este usuario ya tiene el perfil de Facilitador.');
    }

    // 2. Cambiar estado → 'aprobada', registrar revisada_por y fecha_revision
    $postulacion->update([
        'estado' => 'aprobada',
        'revisada_por' => auth()->id(),
        'fecha_revision' => Carbon::now()
    ]);

    // 3. Insertar en security.profiles_users:
    //    COLUMNAS REALES de profiles_users (verificado en migración):
    //    - id_rol_persona (PK autoincremental)
    //    - id_rol          → ID del perfil (2 = Facilitador)
    //    - id_users        → ID del usuario (NO id_persona)
    //    - status          → 1 (activo) — Es unsignedBigInteger, default 0
    //    - fecha_aprobacion → Carbon::now()
    //    - aprobado_por     → ID del coordinador que aprueba
    //    - creado_por       → ID del coordinador
    //    - creado_en        → Carbon::now()
    DB::table('security.profiles_users')->insert([
        'id_rol' => 2, // Facilitador
        'id_users' => $user->id, // IMPORTANTE: user_id, NO id_persona
        'status' => 1,
        'fecha_aprobacion' => Carbon::now(),
        'aprobado_por' => auth()->id(),
        'creado_por' => auth()->id(),
        'creado_en' => Carbon::now()
    ]);

    // 4. Enviar email de aprobación
    Mail::send('taller::emails.postulacion_facilitador_aprobada', [
        'postulacion' => $postulacion
    ], function ($email) use ($postulacion) {
        $email->subject('Postulación como Facilitador - Aprobada');
        $email->to($postulacion->persona->email);
    });

    DB::commit();
    return back()->with('success', 'Postulación aprobada. El participante ahora es Facilitador.');
} catch (\Exception $e) {
    DB::rollBack();
    return back()->with('error', 'Error al aprobar: ' . $e->getMessage());
}
```

**Lógica de rechazo:**
```php
// Al rechazar (ENVUELTO EN DB::transaction()):
DB::beginTransaction();
try {
    // 1. Cambiar estado → 'rechazada', registrar motivo
    $postulacion->update([
        'estado' => 'rechazada',
        'motivo_rechazo' => $request->motivo_rechazo,
        'revisada_por' => auth()->id(),
        'fecha_revision' => Carbon::now()
    ]);

    // 2. ELIMINAR documentos del storage (patrón: unlink(storage_path(...)))
    $postulacion->respuestas->each(function ($respuesta) {
        if ($respuesta->ruta_archivo) {
            $filePath = storage_path('app/public/' . $respuesta->ruta_archivo);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    });

    // 3. Enviar email de rechazo (con opción de re-postularse)
    Mail::send('taller::emails.postulacion_facilitador_rechazada', [
        'postulacion' => $postulacion
    ], function ($email) use ($postulacion) {
        $email->subject('Postulación como Facilitador - Observaciones');
        $email->to($postulacion->persona->email);
    });

    DB::commit();
    return back()->with('success', 'Postulación rechazada. El participante puede corregir y volver a postularse.');
} catch (\Exception $e) {
    DB::rollBack();
    return back()->with('error', 'Error al rechazar: ' . $e->getMessage());
}
```

**Verificación de propiedad (en `verDocumentos()` y `descargarDocumento()`):**
```php
// Verificar que el usuario sea el dueño de la postulación O sea Coordinador/Admin
if (auth()->id() !== $postulacion->id_persona && !auth()->user()->hasRole('coordinador')) {
    abort(403, 'No autorizado para ver estos documentos.');
}
```

> **NOTA DE VERIFICACIÓN — Columnas `profiles_users`:**
> La relación en `User.php` usa `id_users` (FK usuario) e `id_rol` (FK perfil).
> La tabla tiene campos de auditoría: `creado_por`, `creado_en`, `actualizado_por`, `actualizado_en`.
> También tiene `fecha_aprobacion` y `aprobado_por` que son perfectos para este caso de uso.

---

### Vistas Blade

#### [NEW] `Modules/Taller/Resources/views/a/PostulacionFacilitadorLanding.blade.php`
Landing informativo con:
- Header hero con título "Sé Facilitador en CINEFORM"
- Sección "¿Qué hace un facilitador?" (deberes y responsabilidades)
- Sección "Requisitos para postularte" (lista de requisitos activos con íconos)
- Botón CTA "Postularme" → lleva al formulario
- Si ya tiene postulación pendiente: muestra estado
- Si ya fue rechazado: muestra motivo y opción de re-postularse

#### [NEW] `Modules/Taller/Resources/views/a/PostulacionFacilitadorFormulario.blade.php`
Formulario de carga (replica el patrón de `CursoInscribirse.blade.php`):
- Sección de recursos informativos
- Sección de preguntas y documentos
- Input type file para documentos, textarea para preguntas
- Validación JS antes de submit
- SweetAlert de loading al enviar

#### [NEW] `Modules/Taller/Resources/views/a/PostulacionFacilitadorAdmin.blade.php`
Panel administrativo con 3 secciones:
1. **Requisitos:** Tabla CRUD (como Catálogos) + botón "Agregar Requisito" + modal crear/editar
2. **Vista Previa:** Botón que abre el landing en modal o iframe para ver cómo se ve
3. **Postulaciones:** Tabla con listado de postulaciones (nombre, cédula, fecha, estado, acciones: ver docs / aprobar / rechazar)

#### [NEW] `Modules/Taller/Resources/views/a/PostulacionFacilitadorDocumentos.blade.php`
Vista de documentos del postulante (como `CursoParticipantesRespuestas.blade.php`)

---

### Rutas

#### [MODIFY] `Modules/Taller/Routes/web.php`

Agregar dentro del grupo `auth`:

```php
// ── Postulación a Facilitador ──
// Landing (participantes)
Route::get('/postulacion-facilitador', [PostulacionFacilitadorController::class, 'landing'])
    ->name('taller.postulacion-facilitador.landing');
Route::get('/postulacion-facilitador/postularse', [PostulacionFacilitadorController::class, 'formulario'])
    ->name('taller.postulacion-facilitador.formulario');
Route::post('/postulacion-facilitador/postularse', [PostulacionFacilitadorController::class, 'postular'])
    ->name('taller.postulacion-facilitador.postular');

// Admin (coordinadores)
Route::get('/postulacion-facilitador/admin', [PostulacionFacilitadorController::class, 'adminIndex'])
    ->name('taller.postulacion-facilitador.admin');
Route::get('/postulacion-facilitador/admin/preview', [PostulacionFacilitadorController::class, 'previewLanding'])
    ->name('taller.postulacion-facilitador.admin.preview');
Route::post('/postulacion-facilitador/requisitos', [PostulacionFacilitadorController::class, 'storeRequisito'])
    ->name('taller.postulacion-facilitador.requisitos.store');
Route::put('/postulacion-facilitador/requisitos/{requisito}', [PostulacionFacilitadorController::class, 'updateRequisito'])
    ->name('taller.postulacion-facilitador.requisitos.update');
Route::patch('/postulacion-facilitador/requisitos/{requisito}/toggle', [PostulacionFacilitadorController::class, 'toggleRequisito'])
    ->name('taller.postulacion-facilitador.requisitos.toggle');
Route::post('/postulacion-facilitador/{postulacion}/aprobar', [PostulacionFacilitadorController::class, 'aprobar'])
    ->name('taller.postulacion-facilitador.aprobar');
Route::post('/postulacion-facilitador/{postulacion}/rechazar', [PostulacionFacilitadorController::class, 'rechazar'])
    ->name('taller.postulacion-facilitador.rechazar');
Route::get('/postulacion-facilitador/{postulacion}/documentos', [PostulacionFacilitadorController::class, 'verDocumentos'])
    ->name('taller.postulacion-facilitador.documentos');
Route::get('/postulacion-facilitador/documento/{respuesta}/descargar', [PostulacionFacilitadorController::class, 'descargarDocumento'])
    ->name('taller.postulacion-facilitador.documento.descargar');
```

---

### Storage de Archivos

**Almacenamiento:**
```
storage/app/public/postulaciones_facilitador/{cedula}/
    ├── {titulo_requisito_safe}.pdf
    ├── {titulo_requisito_safe}.jpg
    └── ...
```

Mismo patrón que inscripciones: `$file->storeAs("public/postulaciones_facilitador/{$cedula}", $finalFileName)`

**Limpieza de archivos (al rechazar):**
```php
// Patrón de eliminación basado en SecurityController (unlink + storage_path)
$postulacion->respuestas->each(function ($respuesta) {
    if ($respuesta->ruta_archivo) {
        $filePath = storage_path('app/public/' . $respuesta->ruta_archivo);
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }
});
```

**Configuración requerida:**
- Symlink: `php artisan storage:link` (ya ejecutado en el proyecto)
- Tipos permitidos: `.pdf`, `.jpg`, `.jpeg`, `.png`
- Tamaño máximo: 5MB por archivo (validar en FormRequest)
- Los archivos se eliminan cuando la postulación es rechazada
- No se eliminan cuando el postulante cancela voluntariamente (para auditoría)

---

## Resumen de Archivos

| Acción | Archivo |
|--------|---------|
| **[NEW]** | `Modules/Taller/Database/Migrations/2026_07_27_000000_create_postulacion_facilitador_tables.php` |
| **[NEW]** | `Modules/Taller/Entities/RequisitoFacilitador.php` |
| **[NEW]** | `Modules/Taller/Entities/PostulacionFacilitador.php` |
| **[NEW]** | `Modules/Taller/Entities/PostulacionRespuesta.php` |
| **[NEW]** | `Modules/Taller/Http/Controllers/PostulacionFacilitadorController.php` |
| **[NEW]** | `Modules/Taller/Http/Requests/PostulacionFacilitadorRequest.php` |
| **[NEW]** | `Modules/Taller/Http/Requests/StoreRequisitoRequest.php` |
| **[NEW]** | `Modules/Taller/Resources/views/a/PostulacionFacilitadorLanding.blade.php` |
| **[NEW]** | `Modules/Taller/Resources/views/a/PostulacionFacilitadorFormulario.blade.php` |
| **[NEW]** | `Modules/Taller/Resources/views/a/PostulacionFacilitadorAdmin.blade.php` |
| **[NEW]** | `Modules/Taller/Resources/views/a/PostulacionFacilitadorDocumentos.blade.php` |
| **[NEW]** | `Modules/Taller/Resources/views/emails/postulacion_facilitador_recibida.blade.php` |
| **[NEW]** | `Modules/Taller/Resources/views/emails/postulacion_facilitador_aprobada.blade.php` |
| **[NEW]** | `Modules/Taller/Resources/views/emails/postulacion_facilitador_rechazada.blade.php` |
| **[MODIFY]** | `app/Constants/SecurityAction.php` (2 nuevas constantes) |
| **[MODIFY]** | `Modules/Taller/Routes/web.php` (13 nuevas rutas) |
| **[NEW]** | `database/seeders/PostulacionFacilitadorPermissionsSeeder.php` |

---

## Preguntas Abiertas

1. **Contenido del Landing:** ¿Hay un texto específico para la sección "Deberes del facilitador" y "Requisitos para postularse", o se redacta un placeholder genérico editable?

2. **Al aprobar una postulación:** ✅ **RESUELTO** — El perfil Facilitador se asigna automáticamente via insert en `profiles_users`. El participante puede usarlo inmediatamente en su próximo login. Se envía email de notificación.

3. **Re-postulación:** ✅ **RESUELTO** — Si un participante fue rechazado, puede volver a postularse **ilimitadamente**. Los documentos de la postulación rechazada se eliminan del storage para liberar espacio. La postulación rechazada permanece en BD para auditoría.

---

## Patrones de Referencia del Código Existente

Este plan reutiliza los siguientes patrones ya implementados:
- **Requisitos/Respuestas:** `CursoRequisito` → `InscripcionRespuesta` (misma estructura para `RequisitoFacilitador` → `PostulacionRespuesta`)
- **Formulario de inscripción:** `CursoInscribirse.blade.php` + `InscripcionController::procesarInscripcion()`
- **Panel administrativo:** `Catalogos.blade.php` + `CatalogoController` (CRUD con modales y tablas)
- **RBAC:** `SecurityAction` + `ProcessesSeeder` + `AdminPermissionsSeeder`
- **Sidebar dinámico:** `Process.profile_array` → `User.captureMenu()`

---

## Verification Plan

### Automated Tests
```bash
php artisan migrate
php artisan db:seed --class=PostulacionFacilitadorPermissionsSeeder
php artisan route:list --path=postulacion-facilitador
```

### Manual Verification
1. **Como Participante:** Verificar que "Ser Facilitador" aparece en menú → Landing → Formulario → Subir archivos → Postularse → Email de confirmación
2. **Como Coordinador:** Verificar "Requisitos Facilitador" en Administración → CRUD requisitos → Vista previa → Revisar postulaciones → Ver documentos → Aprobar/Rechazar
3. **Post-aprobación:** Verificar que el participante recibe perfil Facilitador y deja de ver la opción "Ser Facilitador" en el menú → Email de aprobación
4. **Post-rechazo:** Verificar que los documentos se eliminan del storage → Email de rechazo → Participante puede re-postularse
5. **Re-postulación:** Verificar que el participante rechazado puede crear una nueva postulación → Nuevo estado "pendiente"
6. **Como Facilitador:** Verificar que NO ve la opción "Ser Facilitador"
7. **Seguridad:** Verificar que un participante NO puede ver documentos de otro participante (solo Coordinador/Admin)

---

## Notas de Verificación (Auditoría vs Código Fuente)

> Verificación realizada el 2026-07-27 contra el código fuente actual del proyecto.

### ✅ Verificado y Correcto

| Punto del Plan | Verificado contra | Estado |
|---|---|---|
| IDs de Menús (menu_id 2=Formación, 3=Administración) | `ModulesMenusSeeder.php` | ✅ Correcto |
| IDs de Perfiles (1=Admin, 2=Facilitador, 3=Participante, 4=Coordinador) | `ProfilesSeeder.php` | ✅ Correcto |
| Patrón de requisitos (tipo, titulo, descripcion, obligatorio) | `CursoRequisito.php` + migración | ✅ Correcto |
| Patrón de respuestas (respuesta_texto, ruta_archivo) | `InscripcionRespuesta.php` + migración | ✅ Correcto |
| Patrón de subida de archivos (`storeAs`) | `InscripcionController::procesarInscripcion()` | ✅ Correcto |
| Sidebar dinámico por `profile_permissions` | `Process.getProfileArrayAttribute()` + `User.captureMenu()` | ✅ Correcto |
| Las rutas van dentro del grupo `auth` + `decrypt_id` | `web.php` línea 28-30 | ✅ Correcto |
| El seeder debe registrarse en `DatabaseSeeder.php` | `DatabaseSeeder.php` (24 seeders existentes) | ✅ Pendiente de agregar |
| Los 7 process_id actuales (1-7) | `ProcessesSeeder.php` + `PermissionsSeeder.php` | ✅ Confirmado, IDs 8-9 están libres |

### ⚠️ Corregido en esta Auditoría

| Punto Original | Error Detectado | Corrección Aplicada |
|---|---|---|
| `profiles_users` insert simple | El plan original decía "Insertar en profiles_users" sin especificar columnas | Corregido: columnas reales son `id_rol`, `id_users`, `status`, `creado_por`, `creado_en`, `fecha_aprobacion`, `aprobado_por` |
| Process IDs hardcodeados (8 y 9) | Asumir IDs fijos es frágil si se ejecuta en BD con datos previos | Corregido: usar `insertGetId()` para obtener IDs reales |
| Timestamps en modelos nuevos | No estaba claro si usar `created_at`/`updated_at` o `creado_en`/`actualizado_en` | Aclarado: usar `creado_en`/`actualizado_en` con `const CREATED_AT`/`UPDATED_AT` (consistente con tablas recientes como `asistencias`) |
| `RequisitoFacilitador` sin `crypt_id` | Faltaba el accessor `crypt_id` necesario para URLs encriptadas en edición/toggle | Agregado: `$appends = ['crypt_id']` |

### 📋 Dependencias Externas a Considerar

1. **`Encryptor` class** (`app/Helpers/Encryptor.php`): Necesario para `getCryptIdAttribute()` en los modelos nuevos
2. **Middleware `decrypt_id`**: Ya aplicado globalmente al grupo de rutas de Taller (línea 28 de `web.php`), por lo que las nuevas rutas lo heredan automáticamente
3. **`BaseController`**: Los nuevos controllers deben extenderlo para tener acceso a `getUsuarioAutenticado()` y `usuarioSinDatosPersonales()`
4. **`comun.personas.user_id`**: Necesario para el insert en `profiles_users.id_users` al aprobar (la FK es al user, no a la persona)

---

## FormRequest (Validación)

Siguiendo el patrón existente de `StoreCursoRequest` en `Modules/Taller/Http/Requests/`:

#### [NEW] `Modules/Taller/Http/Requests/PostulacionFacilitadorRequest.php`
```php
namespace Modules\Taller\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostulacionFacilitadorRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'curso_id' => 'required|integer|exists:taller.cursos,id_curso',
            'requisitos' => 'required|array',
            'requisitos.*.id_requisito_facilitador' => 'required|integer|exists:taller.requisitos_facilitador,id_requisito_facilitador',
            'requisitos.*.respuesta_texto' => 'nullable|string|max:5000',
            'requisitos.*.archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ];
    }

    public function messages()
    {
        return [
            'curso_id.required' => 'Debe seleccionar un curso.',
            'curso_id.exists' => 'El curso seleccionado no es válido.',
            'requisitos.required' => 'Debe completar al menos un requisito.',
            'requisitos.*.archivo.mimes' => 'Los archivos deben ser PDF, JPG o PNG.',
            'requisitos.*.archivo.max' => 'El archivo no debe exceder 5MB.',
        ];
    }
}
```

#### [NEW] `Modules/Taller/Http/Requests/StoreRequisitoRequest.php`
```php
namespace Modules\Taller\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequisitoRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'titulo' => 'required|string|max:255',
            'tipo' => 'required|in:pregunta,recurso,documento',
            'descripcion' => 'nullable|string|max:1000',
            'obligatorio' => 'nullable|boolean',
            'orden' => 'nullable|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'titulo.required' => 'El título del requisito es obligatorio.',
            'tipo.required' => 'El tipo de requisito es obligatorio.',
            'tipo.in' => 'El tipo debe ser: pregunta, recurso o documento.',
        ];
    }
}
```

---

## Emails (Notificaciones)

Siguiendo el patrón de `Mail::send` con vistas Blade inline (existente en `SecurityController`):

#### [NEW] `Modules/Taller/Resources/views/emails/postulacion_facilitador_recibida.blade.php`
```html
<!DOCTYPE html>
<html>
<head>
    <title>Postulación Recibida - CINEFORM</title>
</head>
<body>
    <h2>Hola, {{ $postulacion->persona->primer_nombre }}</h2>
    <p>Hemos recibido correctamente tu postulación como Facilitador en el programa: <strong>{{ $postulacion->curso->nombre }}</strong>.</p>
    <p>Actualmente, tu solicitud se encuentra en <strong>fase de revisión</strong>. Te notificaremos por este mismo medio una vez que tengamos una respuesta.</p>
    <p>Gracias por tu interés en formar parte de nuestro equipo de facilitadores.</p>
    <br>
    <p>Atentamente,</p>
    <p>El equipo de CINEFORM</p>
</body>
</html>
```

#### [NEW] `Modules/Taller/Resources/views/emails/postulacion_facilitador_aprobada.blade.php`
```html
<!DOCTYPE html>
<html>
<head>
    <title>Postulación Aprobada - CINEFORM</title>
</head>
<body>
    <h2>¡Felicidades, {{ $postulacion->persona->primer_nombre }}!</h2>
    <p>Tu postulación como Facilitador en el programa <strong>{{ $postulacion->curso->nombre }}</strong> ha sido <strong>APROBADA</strong>.</p>
    <p>Ya eres un facilitador formal de CINEFORM. Tu nuevo perfil está disponible inmediatamente.</p>
    <p><a href="{{ route('login') }}">Haz clic aquí para iniciar sesión</a></p>
    <br>
    <p>Atentamente,</p>
    <p>El equipo de CINEFORM</p>
</body>
</html>
```

#### [NEW] `Modules/Taller/Resources/views/emails/postulacion_facilitador_rechazada.blade.php`
```html
<!DOCTYPE html>
<html>
<head>
    <title>Observaciones en tu Postulación - CINEFORM</title>
</head>
<body>
    <h2>Hola, {{ $postulacion->persona->primer_nombre }}</h2>
    <p>Se han encontrado las siguientes observaciones en tu postulación como Facilitador en el programa <strong>{{ $postulacion->curso->nombre }}</strong>:</p>
    
    <div style="background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <strong>Motivo:</strong><br>
        {{ $postulacion->motivo_rechazo }}
    </div>

    <p>No te preocupes, puedes corregir tus respuestas o volver a subir los documentos solicitados y <strong>re-postularte</strong>.</p>
    <p>Por favor, accede al siguiente enlace para volver a postularte:</p>
    <p><a href="{{ route('taller.postulacion-facilitador.landing') }}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Volver a Postularme</a></p>
    
    <br>
    <p>Atentamente,</p>
    <p>El equipo de CINEFORM</p>
</body>
</html>
```

**Nota:** Estos templates son para postulaciones a Facilitador, NO para inscripciones a cursos. Los templates existentes (`postulacion_recibida.blade.php`, etc.) son para inscripciones y referencian `$inscripcion`, que no aplica aquí.
