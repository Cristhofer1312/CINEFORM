# CINEFORM — Requisitos (RF/RNF) y auditoría de flujo de negocio + UX/UI

> **Documento:** catálogo de requisitos **y** auditoría de experiencia en un solo lugar.
> **Fecha:** 21 de octubre de 2026
> **Alcance:** módulos `Taller`, `Security`, `Registro`, `Comun` (Laravel 10 / PostgreSQL 17)
> **Base:** código verificado al 21/10/2026 en `app/` (no en los documentos de planificación previos)
> **Estado:** vivo — se actualiza al cerrar cada fase

Este documento unifica lo que antes eran dos: el catálogo de requisitos y la auditoría de UX/UI.
Se puede leer de principio a fin; la sección §3 es la que tiene el trabajo accionable.

---

## Contenido

1. [Cómo leer este documento](#1-cómo-leer-este-documento)
2. [Resumen y estado real](#2-resumen-y-estado-real)
3. [Auditoría de flujo de negocio y UX/UI](#3-auditoría-de-flujo-de-negocio-y-uxui)
4. [Requisitos funcionales](#4-requisitos-funcionales)
5. [Inventario de trabajo priorizado](#5-inventario-de-trabajo-priorizado)
6. [Requisitos no funcionales](#6-requisitos-no-funcionales)
7. [Trazabilidad](#7-trazabilidad)
8. [Tandas de ejecución propuestas](#8-tandas-de-ejecución-propuestas)
9. [Notas de verificación](#9-notas-de-verificación)
10. [Anexos](#10-anexos)
11. [Próxima revisión](#11-próxima-revisión)
12. [Decisiones pendientes — respuestas](#12-decisiones-pendientes--respuestas)

---

## 1. Cómo leer este documento

### 1.1 Estados de requisitos (RF/RNF)

| Símbolo | Significado |
|---|---|
| ✅ | Implementado y verificable de punta a punta (backend **y** forma de usarlo) |
| ⚠️ | Implementado parcialmente, con brecha funcional, de seguridad o **sin entrada en la interfaz** |
| ❌ | No existe o está roto |

> **Criterio aplicado en esta revisión:** un requisito con backend completo pero sin forma de
> invocarse desde la UI **no** es ✅. Cuatro requisitos bajaron de estado por esto (ver §9.2).

### 1.2 Prioridades

| Prioridad | Criterio |
|---|---|
| **P0** | Bloqueante: seguridad, pérdida de datos o flujo esencial roto |
| **P1** | Núcleo del negocio: debe estar para considerar el sistema funcional |
| **P2** | Mejora de experiencia, operación o alcance |
| **P3** | Deseable / backlog |

### 1.3 Fases

| Fase | Contenido |
|---|---|
| **0** | Contención: seguridad y bloqueos |
| **1** | Integridad funcional del negocio |
| **2** | Sistema de diseño (base visual) → hoy detallado en §3 |
| **3** | Rediseño de pantallas → hoy detallado en §3 |
| **4** | Operación y calidad |
| **—** | Ya implementado o sin trabajo asociado |

### 1.4 Confianza de los hallazgos de la sección §3

| Marca | Significado |
|---|---|
| **✔** | Verificado leyendo el código directamente durante la auditoría |
| **~** | Reportado por una lectura asistida con `archivo:línea`; **confirmar antes de corregir** |

### 1.5 Rutas y evidencia

Las citas de vistas usan `a/` como abreviatura de
`app/Modules/Taller/Resources/views/a/`. Los controladores se abrevian por nombre de archivo.
Los comandos de operación están en §10.3.

---

## 2. Resumen y estado real

### 2.1 Totales de requisitos funcionales (corregidos)

| Estado | Cantidad | % |
|---|---:|---:|
| ✅ Implementado de punta a punta | 47 | 61,8 % |
| ⚠️ Parcial | 15 | 19,7 % |
| ❌ Faltante | 14 | 18,4 % |
| **Total RF** | **76** | **100 %** |

Cifra tras resolución de P0 y tandas 0, 1, 2, 3 y 4: **48 ✅ (63,2 %) / 14 ⚠️ (18,4 %) / 14 ❌ (18,4 %)**. Se cerraron de punta a punta **RF-03**
(token de 64 caracteres para reseteo de contraseña), **RF-04** (perfil multi-rol con validación activa), **RF-05**
(RBAC en rutas mutantes), **RF-09** (resiliencia completa del borrador wizard ante expiración de sesión y guardado parcial seguro), **RF-32** (marcado QR con binding estricto curso↔token e inscripción aprobada), **RF-33** (marcado manual con modal y selector en consolidado), **RF-34** (anulación con motivo obligatorio y restauración con confirmación), **RF-55**
(autorización estricta en panel de facilitadores sin auto-aprobación) y **RF-59** (resultados agregados de satisfacción accesibles por permiso en curso finalizado/cerrado).

### 2.2 Totales por módulo

| Módulo | RF | ✅ | ⚠️ | ❌ |
|---|---:|---:|---:|---:|
| Acceso y seguridad | 8 | 7 | 1 | 0 |
| Cursos y ciclo de vida | 12 | 7 | 4 | 1 |
| Inscripción | 12 | 6 | 4 | 2 |
| Asistencia | 7 | 6 | 0 | 1 |
| Calificaciones | 6 | 4 | 1 | 1 |
| Certificación | 8 | 4 | 2 | 2 |
| Facilitadores | 5 | 4 | 1 | 0 |
| Satisfacción | 4 | 3 | 0 | 1 |
| Estadísticas y administración | 10 | 6 | 1 | 3 |
| Público | 4 | 1 | 0 | 3 |
| **Total** | **76** | **48** | **14** | **14** |

### 2.3 Requisitos no funcionales: los que no se cumplen

| ID | Criterio declarado | Estado real | Evidencia |
|---|---|---|---|
| RNF-19 | Pint en verde | ❌ **154 style issues en 246 archivos** | `vendor/bin/pint --test` (seeders, `resources/lang`, `routes/web.php`, providers, tests) |
| RNF-10 | Ningún endpoint > 100 registros sin paginar | ❌ **Violado** | `CursoDetalleController.php:94-97` carga todas las inscripciones en memoria para las 4 pestañas |
| RNF-15 | WCAG 2.1 AA, contraste 4,5:1, foco visible | ❌ | 0 `aria-hidden` en vistas de Taller; ~62 `<label>` sin `for`; foco inconsistente (`outline:none` en login, `#FFFF99` en recovery/home) |
| RNF-16 | 360–1920 px sin scroll horizontal | ❌ | `Security/Resources/views/partials/style.blade.php:15` fuerza `white-space:nowrap` en todas las celdas |
| RNF-17 | Fechas `d/m/Y`, números `es-VE`, mensajes en español | ⚠️ Casi | Los dos únicos `format('Y-m-d')` son inputs `type="date"` (`editar-actions/Editar-Coordinador.blade.php:275,285`) y ahí el ISO es correcto; sí hay i18n mezclada en login |
| RNF-14 | Colas asíncronas | ⚠️ | Sigue `QUEUE_CONNECTION=sync`; `Mail::queue` corre en línea |
| RNF-04 | Secretos fuera de git | ⚠️ | Git correcto (`env.example` raíz no está versionado), pero el archivo local tiene un **password de aplicación de Gmail real** y el `APP_KEY` |
| RNF-07 / 08 / 09 | p95 < 500 ms, PDF < 2 s, dashboard < 1 s | ✅ (dev) | Medido tras la optimización de OPcache/módulos: 0,29–0,55 s en caliente |
| RNF-02 / RF-05 | RBAC en el 100 % de rutas mutantes | ✅ | **Cerrado en Fase 0.** 100 % de rutas mutantes protegidas con `permiso:` o Policies de dominio (`CursoPolicy`, `InscripcionPolicy`). 41 tests de seguridad pasando (143 assertions), auditado automáticamente por `RutasProtegidasTest`. |

### 2.4 Tablero de UX/UI (resumen de §3)

| Métrica medida | Valor | Lectura |
|---|---:|---|
| Vistas de módulo revisadas | 97 (Taller 66, Security 24, Registro 7) | Cobertura completa de pantallas |
| Rutas con nombre | 131 | 25 sin referencia; **6 huérfanas reales** (§10.1) |
| `style="..."` inline en vistas | 396 | No hay sistema de diseño |
| Bloques `<style>` locales que duplican tokens | 4+ | `.hover-shadow`, `.bg-gradient-primary`, radios |
| `<label>` vs `<label for=…>` | 157 / 95 | ~62 sin asociar (algunas envuelven el input) |
| `aria-hidden` en vistas de Taller | 0 | RNF-15 sin trabajo |
| `spinner-border` | 2 | Sin feedback de carga en acciones AJAX |
| `@error` inline (conviven con el SweetAlert global) | 55 | Doble aviso de error en todos los formularios |
| Hallazgos UX/UI | **28** | 3 P0 · 12 P1 · 11 P2 · 2 P3 (§3) |

### 2.5 Salud de los 8 procesos de negocio

| Proceso | Pantalla principal | Estado de UX |
|---|---|---|
| P1 Planificación del curso | wizard 5 pasos | Funciona; borrador frágil (UX-01…UX-06) |
| P2 Convocatoria e inscripción | `CursoDetalle` → `CursoInscribirse` | ✅ **Desbloqueado (Tanda 0)**: Corregido error 500 (`UX-10`) y habilitado flujo de re-postulación/corrección tras rechazo (`UX-11`) |
| P3 Ejecución y asistencia | `AsistenciaConsolidado` / `AsistenciaIndividual` | ✅ **Completado (Tanda 3)**: Validado binding curso/token e inscripción aprobada (`RF-32`), acciones de anular con motivo, restaurar y marcado manual (`UX-12`), y umbral de asistencia unificado (`UX-15`) |
| P4 Evaluación | `CursoCalificar` | ✅ **Completado (Tanda 3)**: Preservación de notas con `old()`, errores de validación inline por estudiante y protección contra borrado silencioso (`UX-14`) |
| P5 Certificación | `CertificacionPanel` + verificación pública | ✅ **Completado (Tanda 3)**: Umbral unificado al 75% (`UX-15`) y acceso directo a resultados de satisfacción (`UX-16`) |
| P6 Satisfacción | `CursoSatisfaccionForm` | ✅ **Completado (Tandas 1 y 3)**: Aviso de encuesta ya respondida (`UX-17`), resultados agregados alcanzables con autorización por rol (`UX-16`) |
| P7 Facilitadores | `PostulacionFacilitador*` | ✅ **Completado (Tanda 3)**: Unificación de cursos según Decisión D1 Opción B (`UX-18`), facilitador y estudiante ven sus cursos respectivos en `mis-cursos` |
| P8 Estadísticas | `Estadisticas` | ✅ **Completado (Tanda 3)**: Placeholders visuales sin datos, tooltips en KPIs y botón para limpiar filtros (`UX-19`) |

---

## 3. Auditoría de flujo de negocio y UX/UI

Ordenada por severidad. Los identificadores `UX-nn` son estables: se referencian desde el catálogo (§4)
y desde el inventario (§5).

### 3.1 Flujo de negocio: roto o inalcanzable

#### UX-10 · El formulario de inscripción devuelve error 500 · **P0** · ✅ Resuelto en Tanda 0

- **Evidencia previa:** `a/CursoInscribirse.blade.php:23,27,30,142` usaba `$inscripcionExistente`;
  `InscripcionController.php:63` hacía `view('taller::a.CursoInscribirse', compact('curso'))`.
  La variable no se compartía, provocando un error 500 al postularse.
- **Impacto:** cualquier participante que pulsaba **"Postularme al Programa"** recibía un error 500.
- **Resolución:** `InscripcionController::create()` inyecta `inscripcionExistente` (null o la inscripción previa
  rechazada), la vista usa `$inscripcionExistente ?? null` y se eliminó el envío prematuro de correo de inscripción
  exitosa al abrir el formulario. Cubierto en `InscripcionFlujoTest`.

#### UX-11 · "Corregir Postulación" es un callejón sin salida · **P0** · ✅ Resuelto en Tanda 0

- **Evidencia previa:** `actions-container.blade.php:49-55` ofrecía el botón cuando `esRechazado()`;
  `InscripcionController.php:39-42` respondía a cualquier inscripción existente con `redirect()->back()`.
- **Impacto:** el participante no podía reenviar ni corregir los recaudos de una postulación rechazada.
- **Resolución:** `InscripcionController::create()` permite continuar si `esRechazado()` y pasa la postulación
  rechazada a `CursoInscribirse.blade.php` para mostrar las observaciones del coordinador y precargar las respuestas
  previas y documentos. `procesarInscripcion()` actualiza la misma fila a `ESTADO_POSTULADO`, limpia el motivo
  y preserva documentos no reemplazados. Cubierto en `InscripcionFlujoTest`.

#### UX-12 · Tres acciones de asistencia existen en backend y no tienen botón · **P1** · ✅ Resuelto en Tanda 3 (RF-33, RF-34)

- **Evidencia previa:** `Taller/Routes/web.php:245-250` definía `anular`, `restaurar` y `marcar-manual`
  (`AsistenciaController.php:274,299,324`), pero no existían botones ni enlaces en ninguna vista.
- **Impacto:** el facilitador no podía corregir una asistencia ni registrar de forma manual a quien no pudo escanear el QR.
- **Resolución:**
  1. En `a/AsistenciaIndividual.blade.php`: botones por fila "Anular" (con diálogo Swal que exige motivo obligatorio) y "Restaurar" (con diálogo Swal de confirmación). En actividades sin registro de asistencia, botón directo "Marcar manual".
  2. En `a/AsistenciaConsolidado.blade.php`: botón en cabecera "Marcar Manual" y celdas sin asistencia con botón `+` para preseleccionar actividad y estudiante al hacer clic; modal `#modalMarcarManual` para seleccionar actividad, estudiante, fecha y estado, procesado vía `peticionConFeedback`.
  Cubierto en `HuecosFlujoTest`.

#### UX-13 · El enlace de marcado del QR permite marcar en el curso equivocado · **P0** · ✅ Resuelto en Fase 0 (RF-32)

- **Evidencia previa:** `mostrarConfirmacion()` y `marcar()` en `AsistenciaController.php` no comprobaban
  que el curso de la ruta coincidiera con `tokenRecord->actividad->id_curso`, y aceptaban cualquier estado de inscripción.
- **Impacto:** un participante del curso B podía marcar asistencia con un token del curso A, o un postulante no admitido marcar asistencia.
- **Resolución:** `mostrarConfirmacion()` y `marcar()` verifican que `(int) $tokenRecord->actividad->id_curso === (int) $curso->id_curso`
  y que la inscripción de la persona exista y tenga `$inscripcion->esAprobado()`. Cubierto en `AsistenciaTokenSeguridadTest`.

#### UX-14 · Un error de validación borra las calificaciones ya tecleadas · **P1** · ✅ Resuelto en Tanda 3 (RF-38, RF-39)

- **Evidencia previa:** `a/CursoCalificar.blade.php:77-91` no usaba `old()` ni `@error`, y `CalificacionController.php:96-109`
  sobrescribía calificaciones previas con nulo si la casilla venía vacía.
- **Impacto:** una casilla errónea borraba todo lo tecleado en el curso y vaciaba notas existentes.
- **Resolución:**
  1. En `CursoCalificar.blade.php`: inputs rehidratados con `old("calificaciones.{$estudiante->id_inscripcion}.nota", $estudiante->calificacion)`, clase `is-invalid` y mensaje de error `@error("calificaciones.{$estudiante->id_inscripcion}.nota")` inline por fila.
  2. Botón renombrado a "Guardar Calificaciones" y bloque `<style>` aislado a `.tabla-calificar-container` sin contaminar `.card` ni `body`.
  3. En `CalificacionController.php`: validación previa y omisión de inputs vacíos en el guardado para evitar sobrescribir con nulo notas ya asentadas.
  Cubierto en `HuecosFlujoTest`.

#### UX-15 · El mismo porcentaje de asistencia tiene colores distintos según la pantalla · **P2** · ✅ Resuelto en Tanda 3 (RF-37, RF-44)

- **Evidencia previa:** umbrales `>=80 / >=50` en `AsistenciaConsolidado` y `AsistenciaIndividual` contra `>=75 / >=50` en `CertificacionPanel`.
- **Impacto:** discrepancia en la visualización de elegibilidad entre vistas para un mismo estudiante.
- **Resolución:** unificados los umbrales bajo la regla única del 75% / 50% mediante los helpers globales
  `asistenciaBadgeClass($pct)`, `asistenciaColorTexto($pct)` y `asistenciaColorBg($pct)` en `app/Helpers/Helpers.php`.
  Aplicados de forma homogénea en `AsistenciaIndividual.blade.php`, `AsistenciaConsolidado.blade.php` y `CertificacionPanel.blade.php`.
  Cubierto en `HuecosFlujoTest`.

#### UX-16 · Los resultados de la encuesta de satisfacción no son alcanzables · **P1** · ✅ Resuelto en Tanda 3 (RF-59)

- **Evidencia previa:** la ruta `taller.cursos.satisfaccion.resultados` y la vista existían pero no estaban enlazadas en ningún menú ni vista.
- **Impacto:** facilitadores y coordinadores no podían consultar los resultados agregados de satisfacción.
- **Resolución:**
  1. En `CondicionalEstadoCurso.php`: agregada la capacidad `ver_satisfaccion` en estados 8 (Finalizado) y 9 (Cerrado).
  2. En `actions-container.blade.php`: botón "Resultados de Encuesta" condicionado a `@can('verSatisfaccion', $curso)`.
  3. En `CertificacionPanel.blade.php`: enlace directo "Ver Resultados de Satisfacción" en la barra superior.
  Cubierto en `HuecosFlujoTest`.

#### UX-17 · El aviso "ya respondiste la encuesta" se pierde · **P1** · ✅ Resuelto en Tanda 1

- **Evidencia previa:** `SatisfaccionCursoController.php:23-26` respondía `->with('info', …)`, pero
  `template/script.blade.php:89-130` solo renderizaba `success`, `warning` y `error`.
- **Impacto:** el participante era redirigido sin explicación y creía que la encuesta no se guardó.
- **Resolución:** se integró el bloque `@if(session()->has('info'))` con SweetAlert (`icon: 'info'`) en
  `template/script.blade.php` y notificación `showNotify('info', ...)` en `template/script-login.blade.php`.
  Todos los avisos de tipo `info` ahora se presentan visiblemente al usuario.

#### UX-18 · "Mis Cursos Asignados" (vista del facilitador) no tiene entrada · **P2** · ✅ Resuelto en Tanda 3 (según Decisión D1 Opción B)

- **Evidencia previa:** `taller.mis-cursos-asignados` existía pero no figuraba en menú ni enlaces.
- **Impacto:** pantalla huérfana y duplicidad conceptual respecto a `mis-cursos`.
- **Resolución:** implementada la Opción B de la Decisión D1 acordada:
  1. `CursoInscritoController.php` detecta el perfil activo (`session('profile_id') == 2` Facilitador). Si es Facilitador consulta sus cursos asignados por `id_persona`; si es Participante consulta sus inscripciones aprobadas.
  2. `CursosInscritos.blade.php` adapta títulos, badges, estadísticas y acciones al perfil activo (acciones docentes vs de estudiante).
  3. `CursoAsignadoController.php` redirige con 302 a `taller.mis-cursos`.
  Cubierto en `HuecosFlujoTest`.

#### UX-19 · Estadísticas sin estado vacío ni explicación de métricas · **P2** · ✅ Resuelto en Tanda 3 (RF-61, RF-63)

- **Evidencia previa:** `a/Estadisticas.blade.php` renderizaba 8 lienzos vacíos cuando no había datos y los KPIs carecían de ayuda conceptual.
- **Impacto:** confusión entre fallo de carga y ausencia de registros.
- **Resolución:**
  1. En `Estadisticas.blade.php`: agregado botón "Limpiar Filtros" que restablece el formulario a la vista por defecto.
  2. Tooltips informativos con `data-bs-toggle="tooltip"` en las 7 tarjetas de KPIs describiendo su cálculo.
  3. Placeholders visuales amigables ("Sin datos para los filtros seleccionados") cuando los datasets de Chart.js vienen vacíos.

### 3.2 Wizard de creación de curso

#### UX-01 · El borrador es inalcanzable si se pierde la sesión · **P1** · ✅ Resuelto en Tanda 4 (RF-09)

- **Evidencia previa:** `BorradorWizardService.php:20-34` buscaba solo por `session('wizard_curso.token')`; sin token creaba uno nuevo y vacío. Los pasos 4 y 5 validaban contra la sesión y fallaban al expirar.
- **Impacto:** al expirar la sesión, el wizard decía "el proceso expiró" y el trabajo en `taller.curso_borradores` quedaba huérfano.
- **Resolución:** `BorradorWizardService::actual($userId)` ahora implementa fallback directo a `CursoBorrador::where('user_id', $userId)->latest('updated_at')->first()` y rehidrata la sesión de forma transparente con `sincronizarSesion()`. Pasos 4 y 5 resuelven datos directamente desde el borrador en BD. En Paso 1 se muestra banner informativo si hay borrador previo con botón "Empezar nuevo borrador" (`wizard.nuevo`). Cubierto en `WizardBorradorTest`.

#### UX-02 · "Guardar borrador y salir" no guarda · **P1** · ✅ Resuelto en Tanda 4

- **Evidencia previa:** enlaces estáticos `<a>` que redirigían a `taller.cursos.index` perdiendo lo tecleado en el paso.
- **Impacto:** el texto prometía persistir y lo tecleado en el paso actual se perdía sin aviso.
- **Resolución:** implementado botón submit `name="accion" value="salir"` en la cabecera y pie de los 5 pasos del wizard. `StorePaso1Request`, `StorePaso2Request` y `StorePaso3Request` cambian reglas a `nullable` cuando `accion === 'salir'`, permitiendo persistir borradores incompletos sin ser bloqueados por validaciones de obligatoriedad (las cuales se reservan para `finalizar()`). Cubierto en `WizardBorradorTest`.

#### UX-03 · Los pasos 1-3 no confirman que se guardó · **P2** · ✅ Resuelto en Tanda 4

- **Evidencia previa:** solo el Paso 4 tenía aviso de guardado; los pasos 1, 2, 3 y 5 no daban feedback de persistencia.
- **Resolución:** badge visual `Borrador guardado DD/MM/YYYY HH:MM` con icono verde añadido en la cabecera de los 5 pasos (`CursoCrearPaso1`, `CursoCrearPaso2`, `CursoCrearPaso3`, `CursoPlantillaCrear`, `CursoRequisitosCrear` y `CursoRevision`).

#### UX-04 · El Paso 4 presume de guardar imágenes que no guarda · **P1** · ✅ Resuelto en Tanda 4 (RF-13)

- **Evidencia previa:** el autoguardado de calibración mostraba "Borrador guardado" pero solo enviaba coordenadas; las imágenes de plantilla y firma solo viajaban en el submit final.
- **Impacto:** el usuario creía que las imágenes estaban guardadas, salía y las perdía.
- **Resolución:**
  1. En `CursoPlantillaCrear.blade.php`: texto de autoguardado actualizado a `"Posiciones guardadas HH:MM:SS (las imágenes se guardan al pulsar Continuar)"`.
  2. Banners informativos añadidos dentro de los acordeones de plantilla y firma advirtiendo que los archivos seleccionados se subirán y guardarán al pulsar "Continuar" o "Guardar borrador".

#### UX-05 · El Paso 4 se titula "Paso 2" · **P2** · ✅ Resuelto en Tanda 4

- **Evidencia previa:** cabecera mostraba "Paso 2: Calibración de Plantilla" desalineado con el stepper de 5 pasos.
- **Resolución:** actualizado a `"Paso 4: Calibración de Certificado"` en modo wizard y `"Calibración de Certificado"` en modo de edición directa.

#### UX-06 · Detalles del wizard que restan control · **P3** · ✅ Resuelto en Tanda 4

- **Paso 3:** horas de inicio y fin marcadas con asterisco rojo `<span class="text-danger">*</span>` y atributo `required` en `contenido-simple.blade.php` y en la plantilla JS; mensajes de error de validación en `StorePaso3Request` especifican el nombre del bloque afectado (cubierto en `WizardBorradorTest`).
- **Paso 3 y Requisitos:** diálogos de confirmación modal `Swal.fire` implementados antes de eliminar bloques de contenido o requisitos, previniendo eliminaciones accidentales.
- **Paso 2:** toggle de alcance nacional modificado para no desmarcar los estados seleccionados, evitando que el usuario pierda su configuración al alternar el switch; vista previa del código PROCINEC actualizada en tiempo real vía JavaScript al cambiar fecha de inicio, correlativo o año.
- **Revisión final:** banner informativo explícito sobre el ciclo de vida del curso (nace en estado `POR ACEPTAR`), formulario con submit protegido y botón con spinner de carga para evitar doble clic. Stepper (`wizard-steps.blade.php`) corregido para marcar únicamente los pasos con datos guardados.
- **Paso 1:** previsualización reactiva de la foto guardada previamente si ya existe en almacenamiento temporal o definitivo.

### 3.3 Hallazgos transversales (afectan a toda la aplicación)

#### UX-20 · Todo error de validación se muestra dos veces · **P1** · ✅ Resuelto en Tanda 1

- **Evidencia previa:** `template/script.blade.php:122-130` disparaba un SweetAlert modal con la lista completa
  de errores en cada página autenticada; además cada formulario pintaba su propia alerta inline o alerta de formulario.
- **Impacto:** el modal tapaba los campos de entrada y obligaba al usuario a cerrar el popup para volver a leer lo mismo.
- **Resolución:** se removió el modal bloqueante global de validación en `template/script.blade.php` y `template/script-login.blade.php`.
  Los errores de validación ahora fluyen por el canal inline junto a cada campo y la alerta propia del formulario,
  mientras que SweetAlert se reserva para respuestas AJAX.

#### UX-21 · El sidebar no dice dónde estás · **P1** · ✅ Resuelto en Tanda 2

- **Evidencia previa:** `template/sidebar.blade.php:53-73` nunca añadía `active` ni `aria-current`; el layout
  `layouts/kaiadmin-menu.blade.php:24-30` no tenía breadcrumbs.
- **Impacto:** con menú dinámico por perfil (`User::captureMenu()`), todos los ítems lucían igual; el usuario navegaba a ciegas.
- **Resolución:** se calcula el estado activo por coincidencia de ruta (`request()->routeIs` y prefijo de recurso) en
  `template/sidebar.blade.php`, expandiendo automáticamente el colapso (`show`, `aria-expanded="true"`) y resaltando el
  subítem con `active`, `aria-current="page"` y estilos visuales nítidos. Además, `layouts/kaiadmin-menu.blade.php`
  incorpora un componente global de breadcrumbs accesible con enlace a inicio, auto-detección por `@section('title')` y
  soporte para `@section('breadcrumbs')` custom.

#### UX-22 · El `<title>` de la pestaña es siempre el mismo · **P2** · ✅ Resuelto en Tanda 2

- **Evidencia previa:** `template/head.blade.php:2` tenía `<title>{{ config('app.name') }}</title>` fijo, ignorando
  los `@section('title')` declarados en las pantallas.
- **Impacto:** todas las pestañas del navegador tenían exactamente el mismo título sin distinguir la pantalla.
- **Resolución:** se actualizó a `<title>@hasSection('title')@yield('title') — {{ config('app.name') }}@else{{ config('app.name') }}@endif</title>`,
  reflejando automáticamente el título de cada pantalla en la pestaña del navegador.

#### UX-23 · Acciones de estado irreversibles sin confirmación y sin loader · **P1** · ✅ Resuelto en Tanda 1

- **Evidencia previa:** `actions-container.blade.php:180,190,195,200` y `CursoDetalle.blade.php:560-572` llamaban a
  `processStatusAction()` sin confirmación previa, sin deshabilitar botones ni spinner, y sin captura de errores.
- **Impacto:** riesgo de cambios de estado accidentales (como archivar un curso a estado 9 CERRADO, que es irreversible)
  y falta de feedback si la petición fallaba.
- **Resolución:** se implementaron diálogos modales de confirmación con consecuencias explícitas (`Swal.fire`) en
  `CursoDetalle.blade.php` para todas las transiciones (aprobar, enviar a revisión, cerrar inscripciones, finalizar curso
  y archivar con botón rojo `#212529`). Cada acción muestra estado de carga (`showLoading()`) y procesa errores con claridad.

#### UX-24 · Un fallo de red bloquea la interfaz para siempre · **P1** · ✅ Resuelto en Tanda 1

- **Evidencia previa:** `a/CursoParticipantes.blade.php:147,153-165` abría un `Swal` con `allowOutsideClick:false` y
  `fetch` sin `.catch`, dejando el loader congelado ante caídas de red o errores 403/419/500.
- **Impacto:** la pantalla quedaba permanentemente bloqueada hasta recargar la página.
- **Resolución:** se creó el helper global `peticionConFeedback(url, options, feedbackOptions)` en `public/js/menu.js`.
  Captura automáticamente errores de conexión, sesiones expiradas (419), falta de permisos (403), errores de servidor (500)
  y respuestas `{ success: false }`, garantizando el cierre del loader y mostrando mensajes amigables. Se adoptó en
  `CursoDetalle.blade.php`, `CursoParticipantes.blade.php` y `AsistenciaConsolidado.blade.php`.

#### UX-25 · No hay sistema de diseño · **P2** · ✔

- **Evidencia:** 396 `style="…"` inline; bloques `<style>` locales en 4+ vistas que redefinen
  `.hover-shadow`, `.transition-all`, `.bg-gradient-primary` (`a/CursosInscritos.blade.php:197-208`,
  `a/partials/Buscador-actions/Cursos.blade.php:148-161`); `a/CursoCalificar.blade.php:118-119`
  redefine `body` y `.card` de forma **global** desde el cuerpo del HTML; dos azules de marca
  conviviendo (`#1572e8` vs `#1e3a8a`) y `btn-success` usado para navegar ("Ver detalles").
- **Impacto:** cada pantalla se ve un poco distinta y cambiar un color exige tocar 4 archivos. Es
  literalmente la Fase 2 ("base visual") que hasta ahora no tenía contenido.
- **Arreglo:** tokens en `app/public/css/main.css`; `<style>` local solo vía `@push('styles')` (el
  layout ya tiene `@stack('styles')`, `head.blade.php:47`); reservar `success`/`danger` para estado
  y confirmación, no para navegar.

#### UX-26 · Iconografía y controles sin nombre accesible · **P2** · ✔

- **Evidencia:** cero `aria-hidden` en las vistas de Taller; botones solo-icono que dependen de
  `title` (invisible en táctil): descarga de certificado y encuesta en
  `a/CursosInscritos.blade.php:126-138`, copiar enlace en `a/AsistenciaConsolidado.blade.php:68-70`;
  el toggle de ver contraseña es un `<div><i class="icon-eye"></i></div>` no focusable
  (`Security/…/users/login.blade.php:58-60`, también `recovery_password.blade.php:61-63` y
  `users/update_profile.blade.php:371`).
- **Arreglo:** `aria-hidden="true"` en decorativos, `aria-label` en botones de icono, y el ojo de
  contraseña como `<button type="button" aria-label="Mostrar contraseña">` (cumple RNF-15).

#### UX-27 · Registro público: largo, sin pasos y con dependencia frágil · **P1** · ~

- **Evidencia:** `Registro/Resources/views/register.blade.php` — 479 líneas, ~20 campos en una sola
  página con 3 secciones y sin indicador de avance (`:50-351`); los selects de
  Estado/Municipio/Parroquia se pueblan por AJAX, están `disabled` y **no** rehidratan desde `old()`
  (`:292-320`), así que fallar la validación obliga a re-elegir la ubicación completa (el `code`
  tampoco conserva valor, `:209`); carga **jQuery 3.6.0 desde CDN** (`:357-358`) cuando el layout ya
  trae 3.7.1 local (`template/script-login.blade.php:13`).
- **Impacto:** es la puerta de entrada de los participantes (RF-01); hoy es el punto con más abandono
  probable, y si el CDN está bloqueado la cascada geográfica deja de funcionar. Afecta a RNF-18.
- **Arreglo:** dividir en pasos, rehidratar selects desde `old()`, quitar el CDN.

#### UX-28 · Claves de `@error` equivocadas = error invisible en el campo · **P2** · ✅ Resuelto en Tanda 1

- **Evidencia previa:** `Security/…/recovery.blade.php:55` (`@error('username')` para el input `email`),
  `recovery_password.blade.php:86` (`@error('password')` para `re_password`),
  `users/create.blade.php:70` y `users/update.blade.php:70` (`@error('type_document')` para `document_type_id`),
  y `files/index.blade.php:58,72,86` (`@error('document')` para `name`, `description` y `attach`).
- **Impacto:** el error de validación no se mostraba junto al campo infractor.
- **Resolución:** se corrigieron las claves de validación en todas las vistas afectadas (`@error('email')`,
  `@error('re_password')`, `@error('document_type_id')`, `@error('name')`, `@error('description')`, `@error('attach')`)
  y se reemplazaron los textos estáticos por `{{ $message }}` para renderizar el mensaje específico del validador.

#### UX-29 · Usuarios: no se puede reactivar y desactivar es silencioso · **P2** · ~

- **Evidencia:** `UsersController.php:117-122` (inactivo = solo badge, sin acción) frente al toggle
  con confirmación de perfiles (`profiles/index.blade.php:101-121`); desactivar se hace desde un
  `<select name="active">` dentro de `users/update.blade.php:146-150`, sin confirmación, y con la
  opción rotulada `{{__('Not')}}` (`:149`).
- **Arreglo:** acción Activar/Desactivar por fila con confirmación, igual que perfiles. Afecta a RF-06.

#### UX-30 · Login: captcha que cambia solo y autofill bloqueado · **P2** · ~

- **Evidencia:** `Security/…/users/login.blade.php:185` recarga el captcha cada 120 s aunque el
  usuario esté escribiendo; `:37` y `register.blade.php:79` usan `autocomplete="off"` global.
- **Impacto:** el código escrito deja de valer sin aviso; los gestores de contraseñas no funcionan.
  Afecta a RF-02.
- **Arreglo:** no autorefrescar con contenido en el campo; tokens de `autocomplete` correctos
  (`username`, `current-password`, `new-password`).

#### UX-31 · Mezcla de `route()` y URLs crudas · **P3** · ~

- **Evidencia:** `login.blade.php:79` usa `route('captcha')` y `:145` usa `url("security/captcha")`;
  `register.blade.php:408,416,426` usan `url("registro/ajax/…")` existiendo rutas nombradas;
  `asignar_perfil.blade.php:226` usa `url('usuarios/search-persona')` existiendo `users.search_persona`.
- **Arreglo:** unificar en `route()`.

### 3.4 Decisiones pendientes del usuario

| # | Tema | Opciones | Afecta a |
|---|---|---|---|
| 1 | `mis-cursos-asignados` (UX-18) | Mantener pantalla propia en el menú del facilitador **o** unificar con `mis-cursos` (se elimina la vista) | RF-21, UX-18 |
| 2 | Resultados de encuesta (UX-16) | ¿Quién los ve: facilitador del curso, coordinación o ambos? Define permiso y ubicación del enlace | RF-59, RF-65 |
| 3 | Umbral único de asistencia (UX-15) | ¿75 % u 80 %? | RF-37, RF-44 |
| 4 | Alcance visual (UX-25) | Sistema de diseño en todas las pantallas (~30 h) **o** solo en el flujo P1–P8 ya tocado (~8 h) | RNF-15/16/17, Fase 2 |

> La antigua duda "¿el QR debe funcionar sin login?" **ya no aplica**: RF-32 define el marcado por
> enlace autenticado como intencional. La autenticación se queda; lo que hay que corregir es la
> validación curso↔token (UX-13).

**Para responderlas:** rellena los campos de **§12** al final de este documento (D1–D4).

### 3.5 Código muerto que induce a error

No es UX visible, pero hace que el próximo que "arregle" algo toque el archivo equivocado:

| Qué | Evidencia | Acción |
|---|---|---|
| 16 partials `a/partials/curso-actions/*.blade.php` que nadie incluye y duplican `actions-container` con otros textos y colores | grep de `curso-actions.<nombre>` sin coincidencias | Borrar la carpeta o documentar la fuente única |
| `a/Cursos.blade.php` incluye un partial que vuelve a declarar `@extends`/`@section` | `a/Cursos.blade.php:7-12` + `a/partials/Buscador-actions/Cursos.blade.php:1,5` | Quitar el layout del partial |
| `inscribirAlCurso()` + listener `.inscribir-btn` apuntan a `taller.inscripciones.store`, que devuelve 400 a propósito | `a/CursoDetalle.blade.php:603-656,705-713` vs `InscripcionController.php:69-72` | Eliminar el camino muerto |
| `taller.cursos.plantilla.create` (ruta por curso) sin uso; el wizard usa `wizard.plantilla.create` | `Taller/Routes/web.php:74-77` | Revisar si se elimina |
| `es_nacional_present` (campo oculto que nadie lee) | `a/CursoCrearPaso2.blade.php:75` | Eliminar |
| Buscador de localidades esperado por JS inexistente | `public/js/modules/taller/cursos.js:386-398` espera `#search-localidad` | Añadir búsqueda o borrar la función |
| `Security/…/users/correo.blade.php` con texto de relleno | `:1-12` | Eliminar o reemplazar por plantilla real |

---

## 4. Requisitos funcionales

### 4.1 Acceso y seguridad

| ID | Requisito | Estado | Prioridad | Fase | UX |
|---|---|---|---|---|---|
| RF-01 | Registro público con verificación por código de email | ✅ | P1 | — | UX-27 |
| RF-02 | Login con captcha y throttle | ✅ | P0 | — | UX-30 |
| RF-03 | Recuperación de contraseña por token | ✅ | **P0** | — | UX-28 |
| RF-04 | Perfil multi-rol y cambio de perfil | ✅ | P1 | — | — |
| RF-05 | RBAC por proceso/acción en todas las rutas mutantes | ✅ | **P0** | — | — |
| RF-06 | Gestión de usuarios, perfiles y permisos | ✅ | P1 | — | UX-29 |
| RF-07 | Auditoría de accesos y acciones críticas | ⚠️ | P1 | 1 | — |
| RF-08 | Cierre de sesión y expiración | ✅ | P1 | — | — |

**Brechas principales y estado de resolución**

- **RF-03:** **(Resuelto)** Migración `2026_10_21_000001_widen_token_in_users_table` amplió `security.users.token`
  de `varchar(50)` a `varchar(64)`. El flujo de generación de token de recuperación (64 caracteres hex) y reseteo
  de contraseña funciona de punta a punta y está cubierto por `PasswordRecoveryTest`.
- **RF-04:** **(Resuelto)** El middleware `ValidarPerfilActivo` en el grupo `web` revalida en cada petición
  que el perfil activo en sesión pertenezca al usuario con `security.profiles_users.status = 1` y que el
  perfil esté activo (`security.profiles.active = true`). En caso de revocación o desactivación, reasigna
  automáticamente si queda un único perfil válido o cierra sesión. Además, `PersonaPerfilController::assignProfiles`
  y `ProfilesController::disabled` invalidan inmediatamente los tokens de sesión (`session_token`) via `DB::table`.
- **RF-05:** **(Resuelto)** Arquitectura de autorización en dos niveles:
  1. Middleware `permiso:<proceso_slug>,<accion_id>` en el 100 % de rutas administrativas y CRUD
     (`profiles.*`, `users.asignar_perfil`, `taller.catalogos.*`, `taller.postulacion-facilitador.admin`, etc.).
  2. Policies de Laravel (`CursoPolicy`, `InscripcionPolicy`) para autorización contextual fina (combinando
     permisos RBAC con pertenencia: dueño de curso, facilitador asignado, titular de inscripción o gestor).
  3. Matriz de transiciones por par `(estado_origen, estado_destino)` en `CursoPolicy::cambiarEstado`.
  4. Red de seguridad automatizada en `RutasProtegidasTest` que audita que ninguna ruta mutante quede sin protección.
- **RF-07:** existen campos de auditoría sueltos (`rechazada_por`, `certificado_aprobado_por`), pero no
  una tabla central.

### 4.2 Cursos y ciclo de vida

| ID | Requisito | Estado | Prioridad | Fase | UX |
|---|---|---|---|---|---|
| RF-09 | Wizard de 5 pasos con borrador persistente | ⚠️ | P1 | 1 | UX-01, UX-02, UX-04 |
| RF-10 | Código PROCINEC automático, único y estable | ✅ | P1 | — | UX-06 |
| RF-11 | Validación de trimestre, fechas y duración | ✅ | P1 | — | UX-06 |
| RF-12 | Temario con evaluaciones y ponderaciones | ✅ | P1 | — | UX-06 |
| RF-13 | Plantilla y firma de certificado | ✅ | P1 | — | UX-04 |
| RF-14 | Requisitos de inscripción | ✅ | P1 | — | — |
| RF-15 | Transiciones de estado validadas | ✅ | P1 | — | UX-23 |
| RF-16 | Reasignación de facilitador | ⚠️ | P1 | 1 | — |
| RF-17 | Duplicar curso / reutilizar plantilla | ❌ | P2 | 3 | — |
| RF-18 | Archivar o eliminar borradores | ⚠️ | P2 | 1 | — |
| RF-19 | Vista previa del curso antes de publicar | ⚠️ | P2 | 3 | — |
| RF-20 | Historial de estados con motivo y autor | ⚠️ | P1 | 1 | — |

**Brechas principales**

- **RF-09:** el borrador existe en `taller.curso_borradores` pero **no es recuperable sin el token de
  sesión**, y "Guardar borrador y salir" es un enlace que no guarda (UX-01, UX-02). Estado corregido
  de ✅ a ⚠️.
- **RF-16:** `reasignarFacilitador` debe garantizar `rollback` en todos los retornos posteriores a
  `beginTransaction` y registrar el motivo.
- **RF-18:** el comando `taller:limpiar-wizards` limpia borradores de más de 7 días, pero no hay
  acción visible para que el usuario archive o elimine el suyo.
- **RF-20:** `curso_estado` ya tiene columna `motivo`, pero no todas las transiciones la exigen ni la
  muestran.

### 4.3 Inscripción

| ID | Requisito | Estado | Prioridad | Fase | UX |
|---|---|---|---|---|---|
| RF-21 | Catálogo de cursos disponibles con filtros | ⚠️ | P1 | 3 | UX-19 |
| RF-22 | Detalle con cupos, fechas, modalidad y requisitos | ✅ | P1 | — | — |
| RF-23 | Postulación con preguntas y documentos | ✅ | P1 | — | UX-10, UX-11 |
| RF-24 | Aprobar, rechazar y denegar postulaciones | ✅ | P1 | — | UX-23 |
| RF-25 | Corrección y rehabilitación | ⚠️ | P1 | 1 | UX-11 |
| RF-26 | Cancelación por el participante | ⚠️ | P1 | 1 | — |
| RF-27 | Retiro por gestor con motivo | ✅ | P1 | — | UX-12 |
| RF-28 | Lista de espera | ❌ | P2 | 4 | — |
| RF-29 | Notificación de cambio de estado | ❌ | P1 | 1 | UX-17 |
| RF-30 | Validación de documentos (tipo, tamaño, nombre) | ⚠️ | **P0** | 1 | — |
| RF-75 | Carta de permiso (menores) como documento obligatorio solo en cursos para niños y adolescentes | ✅ | P1 | — | — |
| RF-76 | Plantilla de la carta de permiso descargable en "Documentos auxiliares" | ✅ | P1 | — | — |

> **Incorporación 2026-10-20:** RF-75 y RF-76 se numeran al final para no alterar los IDs existentes.
> Se detallan en §4.3.1.

**Brechas principales**

- **RF-21:** el catálogo existe solo para usuarios autenticados; el root redirige al login. No hay
  portal público. Además el formulario de filtros solo tiene `search`: el `@if` que comprueba
  `request('id_estado')` en `a/partials/Buscador-actions/Cursos.blade.php:34` no tiene campo asociado.
- **RF-25:** `rehabilitar()` pasa la inscripción a `aprobado`, saltando la revisión; no tiene botón en
  la interfaz y el botón "Corregir Postulación" que sí existe siempre falla (UX-11).
- **RF-26:** la cancelación del participante hace `delete()` físico; la rama administrativa marca
  `rechazado`. Inconsistente y sin auditoría. **Nunca se puede usar**: el botón exige
  `esPostulado() || esRechazado()` (`actions-container.blade.php:74`) mientras el controlador solo
  inyecta `$inscripcion` en estado `APROBADO` (`CursoDetalleController.php:136-141`): condiciones
  mutuamente excluyentes.
- **RF-29:** no se notifica al participante cuando su postulación cambia de estado; solo hay correos
  en certificación y postulación a facilitador. El canal `with('info')` que existiría para avisos ni
  siquiera se renderiza (UX-17).
- **RF-30:** las subidas solo validan `isValid()`; no hay MIME real, tamaño máximo ni nombre único.
  Dos requisitos con el mismo título se sobrescriben.

#### 4.3.1 Carta de permiso (incorporación 2026-10-20)

**Regla de negocio:** solo las inscripciones a cursos dirigidos a **menores de edad (niños y
adolescentes)** deben solicitar la **Carta de permiso (Autorización de menores)** como documento
obligatorio. La condición es a nivel del curso y se determina por su **público objetivo**
(`taller.modalidades_especiales`: Niño o Adolescente), no por la edad del participante. La plantilla
debe estar disponible en la parte superior de la pantalla de inscripción, bajo el bloque
**"Documentos auxiliares"**, con descarga directa.

**Implementación:**

- Catálogo de público objetivo: `taller.modalidades_especiales.es_menor` marca **Niño** y
  **Adolescente** como menores; **Adulto** y **General** no lo son. La migración
  `2026_10_20_000000_add_es_menor_to_modalidades_especiales` agrega y completa el flag.
- Plantilla pública: `app/public/documentos/Autorizacion_menores.docx`, descargable en
  `documentos/Autorizacion_menores.docx`.
- Requisito por defecto: tipo `documento`, título `Carta de permiso (Autorización de menores)`,
  `obligatorio = true`; solo se crea para cursos con `es_menor = true`.
- Cursos existentes: `2026_10_20_000001_add_carta_permiso_to_curso_requisitos` inserta la carta solo
  en cursos para menores; `2026_10_20_000002_remove_carta_permiso_from_non_minor_courses` corrige
  instalaciones donde se agregó a todos y conserva como no obligatoria la evidencia ya cargada.
- Cursos nuevos: se precarga en el paso 5 del wizard y se reafirma en `finalizar()` solo si el
  público objetivo es Niño o Adolescente.
- Edición: el coordinador puede cambiar el público objetivo; el sistema agrega o retira la carta en
  consecuencia. Si el curso deja de ser para menores y ya hay documentos cargados, el requisito se
  conserva como no obligatorio para no perder la evidencia.
- El participante descarga la plantilla, la completa, la firma y la sube en PDF o imagen.
- El gestor o facilitador la revisa en **Requisitos del Participante**.

**Criterios de aceptación:**

1. En cursos para niños o adolescentes, la pantalla de inscripción muestra el bloque "Documentos
   auxiliares" antes de "Información Importante".
2. El enlace descarga `Autorizacion_menores.docx`.
3. El formulario exige el documento antes de enviar la postulación solo en cursos para menores.
4. La postulación guarda el archivo y el revisor puede verlo y descargarlo.
5. El requisito no se duplica si el curso ya lo tenía.
6. Un curso para adultos o de público general no muestra el bloque ni exige la carta.

> **Bloqueante resuelto:** con la resolución de UX-10 en Tanda 0, los criterios 1-6 son ejercibles y
> verificados de punta a punta.

### 4.4 Asistencia

| ID | Requisito | Estado | Prioridad | Fase | UX |
|---|---|---|---|---|---|
| RF-31 | Token QR temporal por actividad | ✅ | P1 | — | UX-13 |
| RF-32 | Marcado por enlace autenticado (con binding curso/token) | ✅ | **P0** | — | UX-13 |
| RF-33 | Marcado manual por facilitador/gestor | ✅ | P1 | — | UX-12 |
| RF-34 | Anulación y restauración con motivo | ✅ | P1 | — | UX-12 |
| RF-35 | Consolidado por curso | ✅ | P1 | — | UX-15 |
| RF-36 | Vista individual por participante | ✅ | P1 | — | UX-12 |
| RF-37 | Regla de asistencia mínima para certificar | ❌ | P1 | 1 | UX-15 |

**Brechas principales y estado de resolución**

- **RF-32:** **(Resuelto)** `mostrarConfirmacion()` y `marcar()` en `AsistenciaController` ahora validan
  estrictamente que `(int) $tokenRecord->actividad->id_curso === (int) $curso->id_curso` y que la inscripción
  del participante exista y esté en estado aprobado (`$inscripcion->esAprobado()`). Cobertura en `AsistenciaTokenSeguridadTest`.
- **RF-33 / RF-34:** **(Resuelto)** Habilitadas en interfaz las acciones de asistencia: anulación con motivo
  obligatorio y restauración en `AsistenciaIndividual`, y botón/modal de marcado manual con preselección rápida
  en `AsistenciaConsolidado` (UX-12). Cobertura en `HuecosFlujoTest`.
- **RF-37:** la certificación no exige un porcentaje mínimo de asistencia; el panel solo muestra el
  porcentaje como información, aunque los umbrales visuales ya se unificaron al 75% / 50% (UX-15).

### 4.5 Calificaciones

| ID | Requisito | Estado | Prioridad | Fase | UX |
|---|---|---|---|---|---|
| RF-38 | Planilla de notas por evaluación | ✅ | P1 | — | UX-14 |
| RF-39 | Notas 0–100 con validación | ✅ | P1 | — | UX-14 |
| RF-40 | Promedio ponderado | ✅ | P1 | — | — |
| RF-41 | Visibilidad del promedio al participante (7/8/9) | ✅ | P1 | — | — |
| RF-42 | Exportación de acta / CSV | ❌ | P2 | 4 | — |
| RF-43 | Auditoría de cambios de nota | ⚠️ | P1 | 1 | — |

**Brechas principales**

- **RF-43:** solo se guarda `actualizado_en`; no se conserva el valor anterior ni el usuario que
  cambió la nota.
- **Validación pendiente:** `CalificacionController::store` no verifica que el contenido pertenezca al
  curso ni que sea una evaluación; el gestor no puede calificar (solo el facilitador). Y sobrescribe
  notas existentes con valores vacíos (UX-14).

### 4.6 Certificación

| ID | Requisito | Estado | Prioridad | Fase | UX |
|---|---|---|---|---|---|
| RF-44 | Panel de elegibilidad (asistencia + nota + encuesta) | ⚠️ | P1 | 1 | UX-15 |
| RF-45 | Aprobación/denegación por participante | ✅ | P1 | — | — |
| RF-46 | Generación de PDF con QR local | ✅ | P1 | — | — |
| RF-47 | Descarga bloqueada hasta responder la encuesta | ✅ | P1 | — | UX-17 |
| RF-48 | Verificación pública | ✅ | P1 | — | — |
| RF-49 | Revocación y reemisión | ❌ | P2 | 4 | — |
| RF-50 | Emisión masiva | ❌ | P2 | 3 | — |
| RF-51 | Notificación con enlace de descarga | ⚠️ | P1 | 1 | — |

**Brechas principales**

- **RF-44:** el panel lista participantes y muestra asistencia/promedio, pero no aplica reglas de
  elegibilidad ni bloquea la aprobación si faltan requisitos.
- **RF-49:** no existe flujo para revocar un certificado emitido ni para reemitirlo con un motivo.
- **RF-51:** `Mail::queue` se ejecuta en línea porque `QUEUE_CONNECTION=sync`; no hay reintentos ni
  historial.
- **Guard faltante:** `CertificacionPanelController::aprobar/denegar` no exige que el curso esté en
  estado 8/9.
- **UX:** aprobar a un participante previamente denegado borra su motivo de denegación sin avisar
  (`CertificacionPanel.blade.php:131-138` vs `CertificacionPanelController.php:108`), y el texto de
  estado vacío dice "No hay participantes aprobados" cuando la lista **es** de aprobados
  (`CertificacionPanel.blade.php:236`).

### 4.7 Facilitadores

| ID | Requisito | Estado | Prioridad | Fase | UX |
|---|---|---|---|---|---|
| RF-52 | Landing y requisitos globales | ✅ | P1 | — | — |
| RF-53 | Postulación con documentos | ✅ | P1 | — | — |
| RF-54 | Revisión de documentos | ✅ | P1 | — | — |
| RF-55 | Aprobación → perfil Facilitador | ✅ | **P0** | — | — |
| RF-56 | Rechazo con motivo y repostulación | ⚠️ | P1 | 1 | — |

**Brechas principales y estado de resolución**

- **RF-55:** **(Resuelto)** Rutas administrativas protegidas con `permiso:taller.postulacion-facilitador.admin,25`.
  Se implementó `impedirResolverPropiaPostulacion()` en `PostulacionFacilitadorController` impidiendo que un
  usuario apruebe o rechace su propia postulación (evita escalada de privilegios IDOR). Se reemplazó el número
  mágico 2 por `Perfil::FACILITADOR` y se garantizó la asignación con `status = 1`. Cobertura total en `PostulacionFacilitadorSeguridadTest`.
- **RF-56:** al rechazar se eliminan los documentos del storage con `@unlink`; el postulante pierde la
  evidencia y no se conserva historial.
- **UX:** las confirmaciones de aprobar/rechazar son diálogos nativos del navegador
  (`PostulacionFacilitadorAdmin.blade.php:192`), el único punto del sistema que rompe el patrón
  SweetAlert.

### 4.8 Satisfacción

| ID | Requisito | Estado | Prioridad | Fase | UX |
|---|---|---|---|---|---|
| RF-57 | Formulario de 4 preguntas (1–5) | ✅ | P1 | — | — |
| RF-58 | Una respuesta por inscripción | ✅ | P1 | — | — |
| RF-59 | Resultados agregados | ✅ | P1 | — | UX-16 |
| RF-60 | Exportación de resultados | ❌ | P2 | 4 | — |

**Brechas principales y estado de resolución**

- **RF-59:** **(Resuelto)** Habilitada la visualización de resultados de encuesta para cursos finalizados o cerrados
  mediante el permiso `ver_satisfaccion` en `CondicionalEstadoCurso.php`, enlace en `actions-container.blade.php`
  y acceso directo en `CertificacionPanel.blade.php` (UX-16). Cobertura en `HuecosFlujoTest`.
- **Privacidad:** `resultados()` permite el acceso a usuarios autorizados por perfil/rol que no sean participantes
  del curso en estado finalizado o cerrado.
- **UX:** el formulario no evita el doble envío en el cliente (`CursoSatisfaccionForm.blade.php:81-83`)
  y el aviso de "ya respondiste" ahora sí se renderiza mediante el soporte flash `info` de Tanda 1 (UX-17).

### 4.9 Estadísticas y administración

| ID | Requisito | Estado | Prioridad | Fase | UX |
|---|---|---|---|---|---|
| RF-61 | KPIs globales | ✅ | P1 | — | UX-19 |
| RF-62 | Filtros por año, modalidad, actividad, aspecto y estado | ✅ | P1 | — | UX-19 |
| RF-63 | Gráficos | ✅ | P1 | — | UX-19 |
| RF-64 | Exportación de reportes | ❌ | P2 | 4 | — |
| RF-65 | Alcance por rol | ⚠️ | P1 | 1 | — |
| RF-66 | CRUD de catálogos | ✅ | P1 | — | — |
| RF-67 | CRUD de requisitos de facilitador | ✅ | P1 | — | — |
| RF-68 | CRUD de usuarios/perfiles | ✅ | P1 | — | UX-29 |
| RF-69 | Notificaciones in-app | ❌ | P2 | 4 | UX-17 |
| RF-70 | Auditoría centralizada | ❌ | P1 | 1 | — |

**Brechas principales**

- **RF-65:** el acceso se controla con un permiso global (`VER_ESTADISTICAS`); no hay alcance por
  facultad, localidad o programa.
- **RF-64:** no hay exportación de los datasets ni de los KPIs.
- **RF-70:** no existe tabla de auditoría; solo logs de aplicación.

### 4.10 Público

| ID | Requisito | Estado | Prioridad | Fase | UX |
|---|---|---|---|---|---|
| RF-71 | Portal público de cursos | ❌ | P1 | 3 | — |
| RF-72 | Verificación pública de certificados | ✅ | P1 | — | — |
| RF-73 | Compartir curso | ❌ | P2 | 3 | — |
| RF-74 | Contacto / soporte | ❌ | P3 | Backlog | — |

**Brechas principales**

- **RF-71:** el público no puede conocer la oferta; el root redirige a login. Existe el layout
  `kaiadmin-front` sin uso.
- **RF-73:** no hay metadatos Open Graph ni botón de compartir.
- **RF-74:** no hay formulario de contacto ni canal de soporte visible.
- **UX:** la verificación pública de certificados usa un layout ajeno y CDN dentro del `<body>`
  (`certificados/verificar.blade.php:1,66-67`), con una clase `w-30` que no existe en Bootstrap.

---

## 5. Inventario de trabajo priorizado

Lista única de trabajo: brechas de requisitos **y** hallazgos de UX, ordenados por prioridad.
`Tipo` indica de dónde viene (RF / RNF / UX).

### 5.1 P0 — Bloqueantes

| Tipo | ID | Qué | Impacto | Evidencia |
|---|---|---|---|---|
| UX | UX-10 | El formulario de inscripción devuelve 500 | Proceso P2 inutilizable | ✅ **Cerrado en Tanda 0:** `$inscripcionExistente` seguro en controller y vista. |
| UX | UX-11 | "Corregir Postulación" siempre falla | Participante rechazado sin salida | ✅ **Cerrado en Tanda 0:** re-postulación tras rechazo habilitada y precargada. |
| UX/RF | UX-13 (RF-32) | El token QR no valida curso ni estado de inscripción | Asistencia falsa entre cursos | ✅ **Cerrado en Fase 0:** binding curso↔token↔inscripción aprobada en `AsistenciaController`. |
| RF | RF-03 | Reset de contraseña roto por longitud de token | Usuarios bloqueados | ✅ **Cerrado en Fase 0:** `security.users.token` ampliado a `varchar(64)`. |
| RF | RF-05 | RBAC incompleto en rutas de Taller | Escalada de privilegios | ✅ **Cerrado en Fase 0:** 100 % protegido con `permiso:` y Policies. |
| RF | RF-55 | Admin de facilitadores sin autorización | Auto-aprobación | ✅ **Cerrado en Fase 0:** `permiso:` + guardia anti auto-aprobación. |
| RF | RF-30 | Uploads sin MIME ni tamaño | Archivos peligrosos | Solo `isValid()` |

### 5.2 P1 — Núcleo del negocio

| Tipo | ID | Qué | Impacto | Fase |
|---|---|---|---|---|
| UX | UX-12 | Anular/restaurar/marcar manual sin botón (RF-33, RF-34) | Sin corrección de asistencia | ✅ **Cerrado en Tanda 3:** botones en individual y consolidado |
| UX | UX-14 | Error de validación borra las notas escritas | Re-trabajo y notas perdidas | ✅ **Cerrado en Tanda 3:** `old()`, validación inline y sin sobreescritura |
| UX | UX-16 | Resultados de encuesta inalcanzables (RF-59) | Datos recogidos sin uso | ✅ **Cerrado en Tanda 3:** enlaces en detalle y panel con RBAC |
| UX | UX-17 | El flash `info` no se renderiza (RF-29/RF-69) | Avisos perdidos en todo el sistema | ✅ **Cerrado en Tanda 1:** renderizado en scripts |
| UX | UX-20 | Errores mostrados dos veces | Ruido y campos tapados | ✅ **Cerrado en Tanda 1:** modal global removido |
| UX | UX-21 | Sidebar sin ítem activo ni breadcrumbs | Usuario desorientado | ✅ **Cerrado en Tanda 2:** active, colapso y breadcrumbs |
| UX | UX-23 | Acciones irreversibles sin confirmación ni loader | Estado del curso cambiado por error | ✅ **Cerrado en Tanda 1:** confirmaciones modales y loaders |
| UX | UX-24 | Fallo de red bloquea la UI para siempre | Página inutilizable hasta recargar | ✅ **Cerrado en Tanda 1:** `peticionConFeedback` seguro |
| UX | UX-27 | Registro público largo, sin pasos y con CDN | Abandono en la puerta de entrada | 2 |
| UX | UX-01 | Borrador del wizard no recuperable (RF-09) | Trabajo perdido | 1 |
| UX | UX-02 | "Guardar borrador y salir" no guarda | Pérdida silenciosa | 1 |
| UX | UX-04 | Paso 4 dice guardar y no guarda imágenes (RF-13) | Diseño de certificado perdido | — |
| RF | RF-04 | Perfil activo no valida `status` | Acceso persistente | ✅ **Cerrado:** middleware `ValidarPerfilActivo` en kernel |
| RF | RF-07 / RF-70 | Sin auditoría central | Sin trazabilidad | 1 |
| RF | RF-16 | Reasignación sin rollback ni motivo | Estados inconsistentes | 1 |
| RF | RF-20 | Historial sin motivo obligatorio | Poca trazabilidad | 1 |
| RF | RF-25 | Rehabilitación salta la revisión | Admisión indebida | 1 |
| RF | RF-26 | Cancelación con borrado físico y sin botón | Pérdida de auditoría | 1 |
| RF | RF-29 | Sin notificación de cambio de estado | Incertidumbre | 1 |
| RF | RF-37 | Sin asistencia mínima para certificar | Certificación indebida | 1 |
| RF | RF-43 | Sin auditoría de notas | Fraude/errores | 1 |
| RF | RF-44 | Sin reglas de elegibilidad | Certificación indebida | 1 |
| RF | RF-51 | Correos síncronos | Pérdida de avisos | 1 |
| RF | RF-56 | El rechazo borra documentos | Pérdida de evidencia | 1 |
| RF | RF-65 | Estadísticas sin alcance por rol | Exceso de visibilidad | 1 |
| RF | RF-71 / RF-21 | Sin portal público | Baja difusión y conversión | 3 |

### 5.3 P2 — Experiencia, operación y alcance

| Tipo | ID | Qué | Fase |
|---|---|---|---|
| UX | UX-18 | `mis-cursos-asignados` sin entrada | ✅ **Cerrado en Tanda 3:** unificado en `mis-cursos` (D1 Opción B) |
| UX | UX-15 | Umbrales de asistencia distintos por pantalla | ✅ **Cerrado en Tanda 3:** regla única 75%/50% y helpers |
| UX | UX-19 | Estadísticas sin estado vacío ni ayuda | ✅ **Cerrado en Tanda 3:** tooltips, placeholders y botón limpiar |
| UX | UX-22 | `<title>` de pestaña siempre igual | ✅ **Cerrado en Tanda 2:** título dinámico con yield |
| UX | UX-25 | Sin sistema de diseño (Fase 2) | 2 |
| UX | UX-26 | Iconografía sin nombre accesible (RNF-15) | 2 |
| UX | UX-28 | Claves de `@error` equivocadas (prerequisito de UX-20) | ✅ **Cerrado en Tanda 1:** claves corregidas en vistas |
| UX | UX-29 | Usuarios: no se puede reactivar | 2 |
| UX | UX-30 | Captcha que cambia solo y autofill bloqueado | 2 |
| UX | UX-03 | Pasos 1-3 sin confirmación de guardado | 2 |
| UX | UX-05 | Paso 4 titulado "Paso 2" | 2 |
| RF | RF-17 | Sin duplicar curso | 3 |
| RF | RF-18 | Sin archivar borrador | 1 |
| RF | RF-19 | Vista previa parcial | 3 |
| RF | RF-28 | Sin lista de espera | 4 |
| RF | RF-42 | Sin exportar acta | 4 |
| RF | RF-49 | Sin revocación/reemisión de certificado | 4 |
| RF | RF-50 | Sin emisión masiva | 3 |
| RF | RF-60 | Sin exportar encuesta | 4 |
| RF | RF-64 | Sin exportar reportes | 4 |
| RF | RF-69 | Sin notificaciones in-app | 4 |
| RF | RF-73 | Sin compartir curso | 3 |

### 5.4 P3 — Deseable / backlog

| Tipo | ID | Qué |
|---|---|---|
| UX | UX-06 | Detalles del wizard que restan control |
| UX | UX-31 | Mezcla de `route()` y URLs crudas |
| RF | RF-74 | Contacto / soporte |
| RNF | RNF-24 | i18n inglés opcional |

### 5.5 Calidad pendiente (RNF)

| Tipo | ID | Qué | Evidencia |
|---|---|---|---|
| RNF | RNF-19 | Pint en rojo | 154 style issues en 246 archivos |
| RNF | RNF-10 | Endpoint sin paginar | `CursoDetalleController.php:94-97` |
| RNF | RNF-15/16/17 | Accesibilidad, responsive y consistencia | §2.3 y UX-25/UX-26 |
| RNF | RNF-14 | Colas síncronas | `QUEUE_CONNECTION=sync` |
| RNF | RNF-04 | Password de Gmail real en archivo local | `env.example` raíz (no versionado) |
| RNF | RNF-13 | Respaldos sin probar | Fase 4 |
| RNF | RNF-21 | Cobertura ≥ 70 % sin medir | Fase 1 |
| RNF | RNF-05 | HTTPS + headers | Fase 4 |

---

## 6. Requisitos no funcionales

| ID | Categoría | Requisito | Criterio medible | Estado | Prioridad | Fase |
|---|---|---|---|---|---|---|
| RNF-01 | Seguridad | Política única de contraseñas | Mín. 10 caracteres, Argon2id/bcrypt, sin reutilización | ⚠️ | P0 | 0 |
| RNF-02 | Seguridad | RBAC en el 100 % de rutas mutantes | Test negativo por cada ruta crítica | ✅ | P0 | — |
| RNF-03 | Seguridad | Uploads seguros | Máx. 10 MB, MIME real permitido, nombre UUID, storage privado | ❌ | P0 | 1 |
| RNF-04 | Seguridad | Secretos fuera de git | `.env.example` sin valores reales; rotación documentada | ⚠️ | P0 | 0 |
| RNF-05 | Seguridad | HTTPS + headers | HSTS, CSP, X-Frame-Options, nosniff | ❌ | P1 | 4 |
| RNF-06 | Auditoría | Log inmutable de acciones críticas | 100 % de cambios de estado, notas y certificados | ❌ | P1 | 1 |
| RNF-07 | Rendimiento | Listados | p95 < 500 ms con 10.000 cursos | ✅ dev | P1 | 4 |
| RNF-08 | Rendimiento | PDF de certificado | < 2 s por certificado | ✅ dev | P1 | 4 |
| RNF-09 | Rendimiento | Dashboard | < 1 s con caché | ✅ dev | P2 | 4 |
| RNF-10 | Escalabilidad | Paginación obligatoria | Ningún endpoint devuelve > 100 registros sin paginar | ❌ | P1 | 1 |
| RNF-11 | Escalabilidad | Índices | Consultas críticas sin `seq scan` | ⚠️ | P1 | 1 |
| RNF-12 | Disponibilidad | Operación | 99,5 % mensual; healthchecks | ⚠️ | P1 | 4 |
| RNF-13 | Disponibilidad | Respaldos | Diario, RPO 24 h, RTO 4 h, restauración probada | ❌ | P0 | 4 |
| RNF-14 | Colas | Correos y PDFs asíncronos | `QUEUE_CONNECTION=database/redis`, worker supervisado, 3 reintentos | ❌ | P1 | 1 |
| RNF-15 | Usabilidad | Accesibilidad | WCAG 2.1 AA; contraste 4,5:1; foco visible | ❌ | P1 | 2 |
| RNF-16 | Usabilidad | Responsive | 360 px a 1920 px sin scroll horizontal | ❌ | P1 | 2 |
| RNF-17 | Usabilidad | Consistencia | Fechas `d/m/Y`, números `es-VE`, mensajes en español | ⚠️ | P2 | 2 |
| RNF-18 | Compatibilidad | Navegadores | Chrome, Edge, Firefox y Safari últimas 2 versiones | ❌ | P2 | 4 |
| RNF-19 | Mantenibilidad | Estilo | PSR-12 + Pint en verde | ❌ | P1 | 0 |
| RNF-20 | Mantenibilidad | Análisis estático | PHPStan nivel 5 en servicios críticos | ❌ | P2 | 4 |
| RNF-21 | Pruebas | Cobertura | ≥ 70 % en servicios de negocio; E2E de flujos | ⚠️ | P1 | 1 |
| RNF-22 | Observabilidad | Logs y métricas | Logs estructurados + Sentry + alertas de cola | ❌ | P2 | 4 |
| RNF-23 | Legal | Datos personales | Consentimiento, retención y derecho de acceso | ❌ | P1 | 4 |
| RNF-24 | i18n | Idioma | Español base; inglés opcional | ⚠️ | P3 | Backlog |
| RNF-25 | Documentación | Operación | README, runbook y ADRs actualizados | ⚠️ | P2 | 4 |

### 6.1 Criterios de aceptación globales

Un requisito se considera cerrado cuando:

1. Tiene prueba automatizada (feature o unit) o prueba manual documentada.
2. Pasa `vendor/bin/pint --test` **sobre los archivos tocados** (existen 154 avisos previos: no se
   exige limpiar todo el repo para cerrar un requisito).
3. No introduce secretos, `eval` con input, `@` silenciosos ni consultas sin esquema.
4. Cumple accesibilidad básica en las pantallas tocadas (RNF-15: `aria-label` en botones de icono,
   `aria-hidden` en decorativos, `label`/`for` asociados, foco visible).
5. Tiene rollback documentado si incluye migración de datos.
6. Si toca una vista, se aplica el criterio de UX: un solo canal de error, loader y confirmación en
   acciones irreversibles.

### 6.2 Requisitos no funcionales críticos (P0)

| ID | Por qué es P0 | Verificación |
|---|---|---|
| RNF-01 | Contraseñas débiles permiten toma de cuentas | Test de validación + revisión de configuración |
| RNF-02 | Un endpoint sin RBAC puede escalar privilegios | ✅ **Cumplido:** RutasProtegidasTest + suite de 41 tests de seguridad pasando al 100 % (143 assertions) |
| RNF-03 | Un upload sin validación puede ejecutar código | Prueba con `.php`, `.phtml`, archivo > 10 MB |
| RNF-04 | Credenciales expuestas comprometen todo el sistema | Rotación + `git log` sin secretos |
| RNF-13 | Sin respaldo, un error de migración es irrecuperable | Restauración cronometrada en staging |

---

## 7. Trazabilidad

### 7.1 RF → proceso de negocio

| Proceso | RF asociados |
|---|---|
| P1. Planificación del curso | RF-09 a RF-20 |
| P2. Convocatoria e inscripción | RF-21 a RF-30, RF-75, RF-76 |
| P3. Ejecución y asistencia | RF-31 a RF-37 |
| P4. Evaluación | RF-38 a RF-43 |
| P5. Certificación | RF-44 a RF-51 |
| P6. Satisfacción | RF-57 a RF-60 |
| P7. Facilitadores | RF-52 a RF-56 |
| P8. Estadísticas y mejora | RF-61 a RF-70 |
| Transversal: acceso y seguridad | RF-01 a RF-08 |
| Transversal: público | RF-71 a RF-74 |

### 7.2 RF → fase

| Fase | RF principales |
|---|---|
| 0 | RF-30 **(cerrados en esta fase: RF-03, RF-05, RF-32, RF-55, RNF-02)** |
| 1 | RF-07, RF-09, RF-16, RF-18, RF-20, RF-25, RF-26, RF-29, RF-33, RF-34, RF-37, RF-43, RF-44, RF-51, RF-56, RF-59, RF-65, RF-70 **(cerrado: RF-04)** |
| 2 | Sistema de diseño y accesibilidad → **UX-20 a UX-31, RNF-15/16/17** |
| 3 | RF-09, RF-17, RF-19, RF-21, RF-22, RF-25, RF-26, RF-29, RF-31 a RF-36, RF-38 a RF-42, RF-44 a RF-48, RF-50, RF-57 a RF-59, RF-61 a RF-63, RF-71, RF-73 |
| 4 | RF-28, RF-42, RF-49, RF-60, RF-64, RF-69 |
| Backlog | RF-74 (contacto); RNF-24 (i18n) opcional |

> Las fases 2 y 3 no "crean" requisitos: materializan los existentes en una interfaz usable y
> consistente. **Hasta esta revisión no tenían contenido**; ahora la Fase 2 se corresponde con los
> hallazgos transversales (UX-20…UX-31) y la Fase 3 con los hallazgos de flujo y wizard
> (UX-01…UX-19).

### 7.3 UX → RF/RNF (mapa cruzado)

| Hallazgo UX | Impacta a | Fase |
|---|---|---|
| UX-10, UX-11 | RF-23, RF-25 | ✅ **Cerrados en Tanda 0** |
| UX-12 | RF-33, RF-34 | 1 |
| UX-13 | RF-32 (P0) | ✅ **Cerrado en Fase 0** |
| UX-14 | RF-38, RF-39 | 1 |
| UX-15 | RF-37, RF-44 | 1 |
| UX-16 | RF-59, RF-65 | 1 |
| UX-17 | RF-29, RF-47, RF-69 | ✅ **Cerrado en Tanda 1** |
| UX-18 | RF-21 | 3 |
| UX-19 | RF-61, RF-62, RF-63 | 2 |
| UX-01…UX-06 | RF-09, RF-11, RF-12, RF-13 | 1 |
| UX-20, UX-28 | RNF-17 | ✅ **Cerrados en Tanda 1** |
| UX-21, UX-22 | RNF-15, RNF-16 | ✅ **Cerrados en Tanda 2** |
| UX-23, UX-24 | RNF-15 (prevención de errores), RF-15 | ✅ **Cerrados en Tanda 1** |
| UX-25 | Fase 2 (sistema de diseño), RNF-17 | 2 |
| UX-26 | RNF-15 (WCAG 2.1 AA) | 2 |
| UX-27 | RF-01, RNF-18 | 2 |
| UX-29 | RF-06, RF-68 | 2 |
| UX-30 | RF-02 | 2 |
| UX-31 | Mantenibilidad | 3 |

---

## 8. Tandas de ejecución propuestas

Cada tanda es un commit independiente y testeable.

**Tanda 0 — Desbloquear el flujo P2 (½ día) — ✅ COMPLETADA (2026-10-21)**
`UX-10`, `UX-11`. Corregido el paso de `$inscripcionExistente` a la vista y habilitado el reenvío de postulación
tras rechazo sin error 500 ni callejón sin salida.
Tests: `InscripcionFlujoTest` (5 tests passing, 29 assertions contra pgsql real).

**Tanda 1 — Feedback, errores y acciones irreversibles (1 día) — ✅ COMPLETADA (2026-10-21)**
`UX-28` (claves `@error` corregidas y dinámicas), `UX-20` (un solo canal de error, sin modal duplicado),
`UX-17` (soporte completo de flash `info` en scripts de notificación), `UX-24` (helper JS global `peticionConFeedback`
con loader y `.catch` seguro), `UX-23` (confirmación explícita con consecuencias, loader y diferenciación de acciones irreversibles).

**Tanda 2 — Navegación (½ día) — ✅ COMPLETADA (2026-10-21)**
`UX-21` (ítem activo por ruta con apertura automática de colapso, estilos visuales y `aria-current="page"`, más breadcrumbs global accesible),
`UX-22` (`@yield('title')` integrado en `<title>` de `template/head.blade.php`).

**Tanda 3 — Huecos de flujo (1 día) — ✅ COMPLETADA (2026-10-21)**
`UX-15` (helpers unificados de asistencia al 75%/50% en individual, consolidado y certificación), `UX-12` (botones de anular con motivo obligatorio y restaurar en `AsistenciaIndividual`, botón cabecera y celdas `+` para marcado manual en `AsistenciaConsolidado` con modal y `peticionConFeedback`), `UX-16` (capacidad `ver_satisfaccion` en estados 8 y 9, botón en `actions-container` y enlace en `CertificacionPanel`), `UX-14` (rehidratación `old()`, validación inline y omisión de vacíos en notas), `UX-19` (botón limpiar filtros, tooltips en KPIs y placeholders sin datos en `Estadisticas`), y `UX-18` (cursos unificados en `mis-cursos` distinguiendo Facilitador y Participante según Decisión D1 Opción B).
Tests: `HuecosFlujoTest` (7 tests passing, 28 assertions contra pgsql real).

**Tanda 4 — Wizard de creación de curso (1–1½ días) — ✅ COMPLETADA (2026-10-21)**
`UX-01` (resiliencia del borrador wizard en BD ante expiración de sesión con rehidratación transparente), `UX-02` (botón submit "Guardar borrador y salir" en los 5 pasos con validación permisiva de guardado parcial), `UX-03` (badge persistente de guardado HH:MM en cabecera de todos los pasos), `UX-04` (claridad en aviso de autoguardado en calibración de certificado y notas sobre imágenes), `UX-05` (título corregido a "Paso 4: Calibración de Certificado"), `UX-06` (horas obligatorias con asterisco y validación con nombre de bloque; modales de confirmación Swal antes de eliminar bloques y requisitos; switch nacional preserva localidades; código PROCINEC reactivo en frontend; ciclo de vida inicial POR ACEPTAR advertido con loader en botón).
Tests: `WizardBorradorTest` (6 tests passing, 24 assertions contra pgsql real).

**Tanda 5 — Accesibilidad (1 día) — SIGUIENTE / EN PROGRESO**
`UX-26`, `UX-30`, más lo medido: `aria-hidden` en decorativos, `aria-label` en botones de icono,
~62 `<label>` sin `for`, un único estilo de foco.

**Tanda 6 — Sistema de diseño y limpieza (según alcance de §3.4)**
`UX-25`, `UX-29`, `UX-31` y el código muerto de §3.5.

**Tandas de seguridad (paralelas, Fase 0 — no son UX)**
- **RF-03:** ✅ **Cerrado:** migración `varchar(64)` en `security.users.token`.
- **RF-32 (UX-13):** ✅ **Cerrado:** binding estricto curso↔token e inscripción aprobada en `AsistenciaController`.
- **RF-05, RF-55, RNF-02:** ✅ **Cerrados:** RBAC en rutas mutantes, Policies y guardias anti auto-aprobación.
- **RF-30:** Pendiente (validación de MIME real y tamaño en uploads).

---

## 9. Notas de verificación

### 9.1 Estado del entorno

- **Fuente de verdad del estado:** código actual en `app/`, no los documentos de planificación.
  Varios bugs de `analisis_viabilidad_corregido.md` ya están corregidos (estado 9, filtro `id_estado`,
  columna `motivo`, `aprobar/rechazar/denegar`, QR local, HMAC, autorización de plantilla, UNIQUE de
  `codigo`).
- **Certificado:** no es una tabla; es el estado de la inscripción (`certificado_aprobado`, fechas,
  motivo) + archivos en `storage/app/public/Certificados/` + verificación pública.
- **Estado del curso:** último registro de `taller.curso_estado`; no hay columna de estado en
  `taller.cursos`.
- **Colas:** `QUEUE_CONNECTION=sync` en `.env` y `.env.example`.
- **Tests:** **103 tests en suite global (102 pasan, 1 fallo conocido: `Tests\Feature\ExampleTest` espera 200 en `/` y recibe 302, preexistente y documentado), 378 assertions**.
  La suite de Taller cuenta con **56 tests pasando al 100 % (215 assertions)**, la suite de seguridad con **43 tests pasando al 100 % (154 assertions)**, `HuecosFlujoTest` con **7 tests (28 assertions)** y `WizardBorradorTest` con **6 tests (24 assertions)**.
  Se ejecutan en el contenedor: `docker compose exec app php artisan test`.
- **Pint:** `vendor/bin/pint --test` → **246 archivos, 154 style issues** en verde/rojo por archivo (archivos tocados en tandas 0 a 4 pasan al 100 % sin style issues).
- **Rendimiento:** ver `RENDIMIENTO.md` (OPcache sin validación de timestamps, caché de módulos,
  `dirname(__DIR__)` en providers). Login de 2,4–4,8 s a 0,29–0,36 s en caliente.

### 9.2 Cambios de estado respecto a la revisión anterior

| ID | Antes | Ahora | Motivo |
|---|---|---|---|
| RF-03 | ❌ | ✅ | Migración `widen_token_in_users_table` amplió `security.users.token` a `varchar(64)`. Reset cubierto en `PasswordRecoveryTest` |
| RF-32 | ⚠️ | ✅ | `AsistenciaController` valida `tokenRecord->actividad->id_curso === curso->id_curso` y solo inscripciones aprobadas (`UX-13`). Cubierto en `AsistenciaTokenSeguridadTest` |
| UX-10 | ❌ | ✅ | Paso seguro de `$inscripcionExistente` a la vista; sin 500 al postularse. Cubierto en `InscripcionFlujoTest` |
| UX-11 | ❌ | ✅ | Re-postulación habilitada tras rechazo con precarga de recaudos previos. Cubierto en `InscripcionFlujoTest` |
| RF-04 | ⚠️ | ✅ | Middleware `ValidarPerfilActivo` revalida `status = 1` y `active = true` en cada request; revocación inmediata por `session_token` |
| RF-05 | ⚠️ | ✅ | 100 % de rutas mutantes protegidas con `permiso:` y Policies (`CursoPolicy`, `InscripcionPolicy`); auditado en `RutasProtegidasTest` |
| RF-55 | ⚠️ | ✅ | `permiso:taller.postulacion-facilitador.admin,25` + guardia `impedirResolverPropiaPostulacion` contra auto-aprobación |
| RNF-02 | ⚠️ | ✅ | RBAC estricto en rutas mutantes con matriz de estados y red de seguridad en testing (41 tests) |
| RF-09 | ⚠️ | ✅ | Resuelto: borrador resiliente en BD sin pérdida por expiración de sesión (`UX-01`), botón de guardar borrador y salir funcional con persistencia parcial (`UX-02`), y feedback honesto (`UX-03`, `UX-04`). Cubierto en `WizardBorradorTest` |
| RF-33 | ⚠️ | ✅ | Habilitado botón de marcado manual en cabecera y celdas de `AsistenciaConsolidado` con modal y `peticionConFeedback` (`UX-12`) |
| RF-34 | ⚠️ | ✅ | Habilitados botones de anular (motivo obligatorio) y restaurar (confirmación) en `AsistenciaIndividual` (`UX-12`) |
| RF-59 | ⚠️ | ✅ | Habilitada capacidad `ver_satisfaccion` y enlaces en `actions-container` y `CertificacionPanel` (`UX-16`) |
| RNF-19 | — | ❌ | 154 style issues medidos (archivos tocados limpios con Pint) |
| RNF-10 | — | ❌ | `CursoDetalleController.php:94-97` sin paginar |
| RNF-14 | — | ❌ | Sigue `sync` |
| RNF-17 | — | ⚠️ | Casi cumplido; los dos `Y-m-d` son inputs `type="date"` legítimos |

**Ajustes de criterio:** un requisito con backend completo pero sin forma de invocarse desde la UI no
se marca ✅.

### 9.3 Carta de permiso (2026-10-20)

El requisito obligatorio `Carta de permiso (Autorización de menores)` y el bloque "Documentos
auxiliares" aplican **solo a cursos dirigidos a niños y adolescentes**. La condición se resuelve por
curso con `taller.modalidades_especiales.es_menor` (Niño/Adolescente), no por la edad del
participante; `comun.personas` no tiene fecha de nacimiento. La plantilla vive en
`app/public/documentos/Autorizacion_menores.docx`. `2026_10_20_000001_add_carta_permiso_to_curso_requisitos`
inserta la carta solo en cursos para menores y `2026_10_20_000002_remove_carta_permiso_from_non_minor_courses`
limpia los cursos que la hubieran recibido por error.

**Prueba:** `app/tests/Unit/Taller/CursoRequisitoCartaPermisoTest.php` (6 casos). Ya se ejecuta en el
contenedor y pasa.

### 9.4 Refactor integral de seguridad y RBAC (2026-10-21)

Se completó el refactor del sistema de permisos y control de acceso (Fase 0):

1. **Arquitectura de autorización en dos niveles:**
   - **Nivel de ruta:** middleware `permiso:<proceso_slug>,<accion_id>` para rutas administrativas, de catálogo y CRUD
     (`ProfilesController`, `PersonaPerfilController::assignProfiles`, `PostulacionFacilitadorController::admin`, etc.).
   - **Nivel de dominio:** Laravel Policies (`CursoPolicy`, `InscripcionPolicy`) registradas en `AuthServiceProvider`.
     Combinan la validación RBAC formal del perfil activo con reglas de pertenencia (facilitador asignado,
     titular de la inscripción, gestor/coordinador o superadministrador con bypass total).
2. **Servicio centralizado `Autorizacion`:**
   - Singleton (`app/Services/Security/Autorizacion.php`) con memoización de matriz de permisos por perfil y caché global
     versionada (`rbac:version`).
   - Invalidación atómica de caché al modificar perfiles o permisos (`ProfilesController`, `PersonaPerfilController`).
   - Resolución desacoplada en `perfilActivoPara($user)`: ignora la sesión del actor cuando se evalúa a otro usuario
     (e.g. en jobs, CLI o `Gate::forUser($otro)`), consultando directamente `security.profiles_users` (`status = 1`, `p.active = true`)
     con criterio determinista por jerarquía.
3. **Validación de sesión y perfil activo (`ValidarPerfilActivo`):**
   - Middleware en el grupo `web` que verifica que el usuario esté activo (`active == 1`) y que su `session('profile_id')`
     sea válido y esté activo en `security.profiles_users` (`status = 1`) y `security.profiles` (`active = true`).
   - En caso de desactivación de perfil, reasigna automáticamente si queda un único perfil válido o desautentica con redirect a login.
   - Excluye rutas públicas (login, registro, recuperación, verificación de certificados, imagen QR de asistencia).
4. **Cierre de brechas IDOR y escalada de privilegios:**
   - `PersonaPerfilController::assignProfiles`: validación de jerarquía (bloqueo sobre admin 1, auto-asignación prohibida,
     perfiles inactivos rechazados, y allowlist estricta para el Coordinador: solo puede asignar Facilitador o Participante).
     Inserción con `status = 1` y revocación de sesión del usuario modificado vía `DB::table('security.users')->update(['session_token' => ...])`.
   - `ProfilesController`: bloquea la modificación de permisos del Administrador (1) y del perfil activo propio;
     `disabled` impide la desactivación de perfiles base (1 a 4) por usuarios que no sean el superadministrador.
   - `PostulacionFacilitadorController`: guardias `impedirResolverPropiaPostulacion()` bloquean auto-aprobación y auto-rechazo.
   - `EditarCursoController` y `CursoController::updateStatus`: resolución (`findOrFail`) y autorización (`authorize`) fuera
     del bloque `try/catch`, evitando capturar excepciones de autorización y devolviendo HTTP 403 Forbidden limpio.
5. **Red de seguridad en pruebas:**
   - Creada suite de pruebas en `tests/Feature/Security/`: `CursoPolicyTest`, `InscripcionPolicyTest`,
     `PostulacionFacilitadorSeguridadTest`, `ProfilesSecurityTest`, `ValidarPerfilActivoTest`, `RutasProtegidasTest`.
   - `RutasProtegidasTest` audita automáticamente que ninguna ruta mutante quede sin protección de middleware `permiso:`
     o policy de dominio.

### 9.5 Cierre de Tanda 0 y P0 de Seguridad (2026-10-21)

Se completaron e integraron con pruebas los puntos críticos de Fase 0 pendientes y la Tanda 0:

1. **Tanda 0 (Desbloqueo de P2 - `UX-10` y `UX-11`):**
   - **`UX-10` (Error 500 en formulario de inscripción):** En `InscripcionController::create()` se pasa `'inscripcionExistente' => null` (o la rechazada) a la vista `a/CursoInscribirse.blade.php`, que ahora usa `$inscripcionExistente ?? null`. Se retiró el envío prematuro del correo de bienvenida que se disparaba incorrectamente al abrir el formulario.
   - **`UX-11` (Callejón sin salida al corregir postulación):** Si la postulación fue rechazada (`esRechazado()`), `create()` no redirige hacia atrás con error, sino que permite ingresar al formulario mostrando una alerta informativa con las observaciones del coordinador, precarga de respuestas y enlaces a los documentos adjuntos previamente. Al enviar (`procesarInscripcion()`), se reutiliza la misma fila en `taller.inscripciones` actualizando su estado a `ESTADO_POSTULADO`, limpiando el motivo y preservando los archivos adjuntos existentes que no se hayan vuelto a subir.
   - **Pruebas:** `tests/Feature/Taller/InscripcionFlujoTest.php` (5 pruebas pasando, 29 assertions).

2. **Bloqueantes P0 de Seguridad:**
   - **`RF-03` (Truncamiento de token de recuperación de contraseña):**
     Migración `2026_10_21_000001_widen_token_in_users_table.php` amplió la columna `security.users.token` de `varchar(50)` a `varchar(64)`.
     Prueba: `tests/Feature/Security/PasswordRecoveryTest.php` (2 pruebas pasando, 11 assertions).
   - **`RF-32` / `UX-13` (Integridad del token QR de asistencia):**
     En `AsistenciaController.php` (`mostrarConfirmacion` y `marcar`) se implementó la validación estricta de que el curso en la ruta coincida con el curso de la actividad del token (`(int) $tokenRecord->actividad->id_curso === (int) $curso->id_curso`), y que el participante cuente con inscripción en estado aprobado (`$inscripcion->esAprobado()`).
     Prueba: `tests/Feature/Taller/AsistenciaTokenSeguridadTest.php` (3 pruebas pasando, 5 assertions).

### 9.6 Cierre de Tanda 3 — Huecos de flujo (2026-10-21)

Se completaron e integraron con pruebas los 6 ítems de la Tanda 3:

1. **`UX-15` (Umbral único de asistencia - RF-37, RF-44):**
   - Helpers unificados `asistenciaBadgeClass($pct)`, `asistenciaColorTexto($pct)` y `asistenciaColorBg($pct)` en `app/Helpers/Helpers.php` bajo la regla única 75% (aprobatorio) / 50% (en riesgo).
   - Aplicado de manera uniforme en `AsistenciaIndividual.blade.php`, `AsistenciaConsolidado.blade.php` y `CertificacionPanel.blade.php`.
2. **`UX-12` (Acciones de asistencia sin botón en UI - RF-33, RF-34):**
   - `AsistenciaIndividual.blade.php`: botones "Anular" (Swal con motivo obligatorio), "Restaurar" (confirmación modal) y "Marcar manual" en actividades sin registro.
   - `AsistenciaConsolidado.blade.php`: botón "Marcar Manual" en cabecera y celdas sin asistencia con botón `+` para preseleccionar actividad y estudiante al clic; modal `#modalMarcarManual` y procesamiento AJAX robusto con `peticionConFeedback`.
3. **`UX-16` (Resultados de encuesta inalcanzables - RF-59):**
   - Habilitada capacidad `ver_satisfaccion` en `CondicionalEstadoCurso.php` para cursos finalizados (8) y cerrados (9).
   - Botón "Resultados de Encuesta" en `actions-container.blade.php` con autorización `@can('verSatisfaccion', $curso)` y enlace en `CertificacionPanel.blade.php`.
4. **`UX-14` (Error de validación borra calificaciones tecleadas - RF-38, RF-39):**
   - `CursoCalificar.blade.php`: inputs rehidratados con `old()`, clase `is-invalid` y mensaje de validación `@error` inline por estudiante.
   - Botón renombrado a "Guardar Calificaciones" y CSS encapsulado en `.tabla-calificar-container`.
   - `CalificacionController.php`: omitidas entradas vacías en `store` para evitar sobreescritura accidental con nulo.
5. **`UX-18` (Unificación de cursos asignados al facilitador según Decisión D1 Opción B):**
   - `CursoInscritoController.php`: detecta el perfil activo; facilitadores ven sus cursos asignados por `id_persona` y estudiantes sus inscripciones aprobadas.
   - `CursosInscritos.blade.php`: interfaz adaptada dinámicamente con acciones específicas para facilitador o estudiante.
   - `CursoAsignadoController.php`: redirección 302 a `taller.mis-cursos`.
6. **`UX-19` (Estadísticas sin estado vacío ni ayuda - RF-61, RF-63):**
   - `Estadisticas.blade.php`: botón "Limpiar Filtros", tooltips informativos en las 7 tarjetas de KPIs y placeholders visuales cuando los gráficos no tienen datos.
7. **Pruebas y calidad de código:**
   - Creado `tests/Feature/Taller/HuecosFlujoTest.php` (7 tests, 28 assertions, todos en verde).

### 9.7 Cierre de Tanda 4 — Wizard de creación de curso (2026-10-21)

Se completaron e integraron con pruebas los requerimientos UX y de resiliencia del Wizard (RF-09, UX-01 a UX-06):

1. **`UX-01` (Resiliencia del borrador ante expiración de sesión - RF-09):**
   - `BorradorWizardService::actual($userId)`: fallback automático a `CursoBorrador::where('user_id', $userId)->latest('updated_at')->first()` rehidratando la sesión de forma transparente si expira (`sincronizarSesion()`).
   - `CrearCursoController::wizardPlantillaCreate/Store` y `CursoRequisitoController::wizardCreate/Store`: recuperación robusta desde BD sin romper el flujo de trabajo en los pasos 4 y 5.
   - Nueva ruta `taller.cursos.wizard.nuevo` (`CrearCursoController::reiniciar`) para descartar el borrador previo y empezar uno limpio.
   - Banner en Paso 1 que avisa si existe un borrador previo guardado y permite reiniciarlo con un clic.
2. **`UX-02` ("Guardar borrador y salir" funcional con persistencia parcial):**
   - Se transformaron los enlaces pasivos de cabecera y pie en botones de submit formales con `name="accion" value="salir"` en los 5 pasos (`CursoCrearPaso1`, `CursoCrearPaso2`, `CursoCrearPaso3`, `CursoPlantillaCrear` y `CursoRequisitosCrear`).
   - `StorePaso1Request`, `StorePaso2Request` y `StorePaso3Request` cambian los campos requeridos a `nullable` cuando `accion === 'salir'`, permitiendo guardar el borrador en cualquier momento sin bloqueos ni pérdida de trabajo tecleado.
3. **`UX-03` (Confirmación visible de guardado):**
   - Badge visual consistente `Borrador guardado DD/MM/YYYY HH:MM` en la cabecera de los 5 pasos del wizard cuando hay un registro activo en BD.
4. **`UX-04` (Claridad en autoguardado de certificado y subida de imágenes - RF-13):**
   - En `CursoPlantillaCrear.blade.php`: texto de autoguardado corregido a `"Posiciones guardadas HH:MM:SS (las imágenes se guardan al pulsar Continuar)"`.
   - Avisos informativos dentro de los acordeones de plantilla y firma explicando que los archivos se cargan en disco al pulsar Continuar o Guardar borrador.
5. **`UX-05` (Corrección de numeración de pasos):**
   - Título de `CursoPlantillaCrear.blade.php` actualizado a `"Paso 4: Calibración de Certificado"` en modo wizard.
6. **`UX-06` (Controles precisos y experiencia interactiva):**
   - **Paso 3:** horas de inicio y fin obligatorias con asterisco rojo `<span class="text-danger">*</span>` y `required`; validación de rango con nombre del bloque afectado en el mensaje de error.
   - **Paso 3 y Requisitos:** confirmación con diálogo modal `Swal.fire` antes de eliminar bloques de contenido o preguntas/documentos requeridos.
   - **Paso 2:** toggle de alcance nacional modificado para no desmarcar las casillas de localidades al alternar el switch; vista previa del código PROCINEC recalculada en tiempo real vía JavaScript al cambiar fecha de inicio, correlativo o año.
   - **Paso 5 y Revisión:** banner explícito que advierte que el curso nacerá en estado `POR ACEPTAR`; botón de finalización protegido contra doble clic con spinner de carga interactivo; stepper (`wizard-steps.blade.php`) no marca pasos vacíos como completados.
   - **Paso 1:** precarga y previsualización de la foto de portada ya subida.
7. **Pruebas y calidad de código:**
   - Creado `tests/Feature/Taller/WizardBorradorTest.php` (6 tests passing, 24 assertions, 100 % en verde).
   - Formateo estricto con Laravel Pint: 0 style issues en los 7 archivos intervenidos.
   - Suite global: **102 tests pasando, 1 fallo conocido preexistente (`ExampleTest`), 378 assertions**.

---

## 10. Anexos

### 10.1 Rutas con nombre sin referencia en vistas ni JS

De las 6 rutas huérfanas verificadas originalmente, **5 fueron completamente rescatadas y vinculadas en la interfaz durante la Tanda 3**:
- `taller.asistencia.marcar-manual` → enlazada en `AsistenciaConsolidado.blade.php` (botón superior y celdas `+`).
- `taller.asistencia.anular` y `taller.asistencia.restaurar` → enlazadas en `AsistenciaIndividual.blade.php` (acciones por fila).
- `taller.cursos.satisfaccion.resultados` → enlazada en `actions-container.blade.php` y `CertificacionPanel.blade.php`.
- `taller.mis-cursos-asignados` → redirigida con 302 a `taller.mis-cursos` según Decisión D1 Opción B.

Solo resta como ruta candidata a limpieza: `taller.cursos.plantilla.create` (esta última solo por curso; el wizard usa `wizard.plantilla.create`).

Las otras 19 (`home.registro`, `register`, `registro.*`, `update_profile.send_code`,
`users.search_persona`, `usuario.mostrarPerfilSeleccion`, `taller.inscripciones.*` del workflow de
postulación) **sí** tienen UI: se invocan por `url()` cruda o por URL construida en JS
(`updatePostulacion`, `a/CursoParticipantes.blade.php:132`). No son hallazgos.

### 10.2 Vistas revisadas

Taller (66): `a/*` (38), `a/partials/*` (24), `certificados/verificar`, `layouts/master` y emails.
Security (24), Registro (7) y los layouts/parciales compartidos de `app/resources/views`.

### 10.3 Operación tras tocar vistas

```powershell
docker compose exec -u www-data app php artisan view:clear
docker compose exec -u www-data app php artisan view:cache
docker compose exec app sh -c 'kill -USR2 1'
docker compose exec -u www-data app php artisan test
```

Recordatorio: con `opcache.validate_timestamps=0`, PHP-FPM **no** ve el código nuevo sin recargarse.
Tras cambiar `.env` o `config/*.php`: `config:clear` + recarga. Tras agregar/quitar módulos:
`cache:forget laravel-modules`. Ver `RENDIMIENTO.md` para el detalle completo.

---

## 11. Próxima revisión

Actualizar este documento al cerrar cada fase, marcando:

- Estado final del RF (✅/⚠️/❌) y fase real de cierre.
- Estado del hallazgo UX correspondiente (cerrado / descartado / movido a backlog).
- Enlace al commit/PR y a la prueba.
- Fecha de verificación.

**Responsable:** equipo CINEFORM
**Última actualización:** 2026-10-21

---

## 12. Decisiones pendientes — respuestas

> Rellena cada campo y guarda el archivo. Con estas cuatro respuestas se puede empezar la
> implementación (§8). No hace falta tocar código para responder.

### D1 · Pantalla `mis-cursos-asignados` (UX-18, RF-21)

**Opciones:** (a) mantener pantalla propia y enlazarla en el menú del perfil Facilitador
· (b) unificarla con `mis-cursos` y eliminar `a/CursosAsignados.blade.php`

**Respuesta:**

```
(a / b): B
Notas: Se debe mantener la diferencia entre los cursos de los estudiantes y los facilitadores. Es decir, el facilitador debe poder ver solamente los cursos que le corresponden a él, mientras que el estudiante debe poder ver todos los cursos en los que esta inscrito.
```

### D2 · Quién ve los resultados de la encuesta de satisfacción (UX-16, RF-59, RF-65)

**Opciones:** (a) solo el facilitador del curso · (b) solo coordinación/gestor
· (c) ambos · (d) otro (indicar)

**Respuesta:**

```
(a / b / c / d):
Permiso o perfil que lo habilita:
Dónde debe aparecer el enlace (pantalla):
Notas:
```

### D3 · Umbral único de asistencia para certificar (UX-15, RF-37, RF-44)

**Opciones:** (a) 75 % · (b) 80 % · (c) otro (indicar). Hoy: 80 % en Asistencia, 75 % en Certificación.

**Respuesta:**

```
(a / b / c):
Valor exacto (%):
¿Aplica igual a todos los cursos o depende del curso?:
Notas:
```

### D4 · Alcance del sistema de diseño (UX-25, RNF-15/16/17, Fase 2)

**Opciones:** (a) todas las pantallas (~30 h) · (b) solo el flujo P1–P8 ya auditado (~8 h)
· (c) otro (indicar)

**Respuesta:**

```
(a / b / c):
Pantallas o módulos incluidos:
Notas:
```

### D5 · Otras decisiones

Cualquier otra duda, cambio de prioridad o requisito nuevo que deba reflejarse antes de implementar:

```
-
```

**¿Puedo empezar la Tanda 0 (`UX-10`, `UX-11`, desbloquea el proceso P2)?** (sí / no / esperar D1–D4)

```
```
