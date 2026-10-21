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
| ✅ Implementado de punta a punta | 39 | 51,3 % |
| ⚠️ Parcial | 22 | 28,9 % |
| ❌ Faltante | 15 | 19,7 % |
| **Total RF** | **76** | **100 %** |

Cifra anterior del documento: 43 ✅ / 18 ⚠️ / 15 ❌. La diferencia son los cuatro requisitos que
estaban marcados como implementados y **no tienen entrada en la interfaz** (§9.2).

### 2.2 Totales por módulo

| Módulo | RF | ✅ | ⚠️ | ❌ |
|---|---:|---:|---:|---:|
| Acceso y seguridad | 8 | 4 | 3 | 1 |
| Cursos y ciclo de vida | 12 | 6 | 5 | 1 |
| Inscripción | 12 | 6 | 4 | 2 |
| Asistencia | 7 | 3 | 3 | 1 |
| Calificaciones | 6 | 4 | 1 | 1 |
| Certificación | 8 | 4 | 2 | 2 |
| Facilitadores | 5 | 3 | 2 | 0 |
| Satisfacción | 4 | 2 | 1 | 1 |
| Estadísticas y administración | 10 | 6 | 1 | 3 |
| Público | 4 | 1 | 0 | 3 |
| **Total** | **76** | **39** | **22** | **15** |

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
| RNF-02 / RF-05 | RBAC en el 100 % de rutas mutantes | ⚠️ | `permiso:` solo en 3 controladores |

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
| P2 Convocatoria e inscripción | `CursoDetalle` → `CursoInscribirse` | ❌ **Roto y con callejón sin salida** (UX-10, UX-11) |
| P3 Ejecución y asistencia | `AsistenciaConsolidado` / `AsistenciaIndividual` | Funciona; 3 acciones sin UI (UX-12), falta validar token (RF-32) |
| P4 Evaluación | `CursoCalificar` | Funciona; pierde lo escrito al fallar (UX-14) |
| P5 Certificación | `CertificacionPanel` + verificación pública | Funciona; umbrales y confirmaciones inconsistentes (UX-15) |
| P6 Satisfacción | `CursoSatisfaccionForm` | Formulario ok; **resultados inalcanzables** (UX-16) y aviso perdido (UX-17) |
| P7 Facilitadores | `PostulacionFacilitador*` | El flujo más completo del sistema; `mis-cursos-asignados` sin entrada (UX-18) |
| P8 Estadísticas | `Estadisticas` | Funciona; sin estado vacío ni ayuda (UX-19) |

---

## 3. Auditoría de flujo de negocio y UX/UI

Ordenada por severidad. Los identificadores `UX-nn` son estables: se referencian desde el catálogo (§4)
y desde el inventario (§5).

### 3.1 Flujo de negocio: roto o inalcanzable

#### UX-10 · El formulario de inscripción devuelve error 500 · **P0** · ✔

- **Evidencia:** `a/CursoInscribirse.blade.php:23,27,30,142` usa `$inscripcionExistente`;
  `InscripcionController.php:63` hace `view('taller::a.CursoInscribirse', compact('curso'))`.
  La variable solo existe como local (`InscripcionController.php:35`, `CursoController.php:435`) y
  nunca se comparte (no hay `View::share` para ella).
- **Impacto:** cualquier participante que pulse **"Postularme al Programa"**
  (`a/partials/curso-actions/actions-container.blade.php:68`) recibe un error en lugar del
  formulario. **Es el CTA central del proceso P2.**
- **Arreglo:** pasar `'inscripcionExistente' => null` (o la inscripción rechazada, ver UX-11) al
  `view()`, y usar `$inscripcionExistente ?? null` en la vista.

#### UX-11 · "Corregir Postulación" es un callejón sin salida · **P0** · ✔

- **Evidencia:** `actions-container.blade.php:49-55` ofrece el botón cuando `esRechazado()`;
  `InscripcionController.php:39-42` responde a **cualquier** inscripción existente con
  `redirect()->back()->with('error', ...)`. No hay ruta de corrección.
- **Impacto:** a quien la coordinación devuelve para corregir se le ofrece un botón que solo puede
  fallar. El mensaje además culpa a "un administrador" sin decir a quién escribir.
- **Arreglo:** en `create()`, si `esRechazada()`, pasar la inscripción a la vista y precargarla;
  en `procesarInscripcion()`, actualizar esa inscripción en vez de crear otra.

#### UX-12 · Tres acciones de asistencia existen en backend y no tienen botón · **P1** · ✔

- **Evidencia:** `Taller/Routes/web.php:245-250` define `anular`, `restaurar` y `marcar-manual`
  (`AsistenciaController.php:274,299,324`). Búsqueda en todas las vistas de Taller y en
  `app/public/js`: **cero referencias**.
- **Impacto:** el facilitador no puede corregir una asistencia mal marcada ni registrar a quien no
  pudo escanear el QR. `a/AsistenciaIndividual.blade.php:90-92` **muestra** asistencias "Anulada"
  sin ofrecer restaurarlas: el usuario ve el problema y no puede resolverlo. Afecta a **RF-33** y **RF-34**.
- **Arreglo:** "Anular"/"Restaurar" por fila en `AsistenciaIndividual` y "Marcar manual" por
  actividad en `AsistenciaConsolidado`.

#### UX-13 · El enlace de marcado del QR permite marcar en el curso equivocado · **P0** · ✔ (RF-32)

- **Evidencia:** `Taller/Routes/web.php:304-309` (`taller.asistencia.confirmar` y
  `confirmar-marcar`) están dentro de `->middleware('auth')` — la autenticación es **intencional**
  según RF-32 ("marcado por enlace autenticado"). El problema real es otro:
  `mostrarConfirmacion()`/`marcar()` **no comparan el curso de la URL con
  `tokenRecord->actividad->id_curso`**, y aceptan cualquier estado de inscripción, no solo `aprobado`.
- **Impacto:** un participante del curso B puede marcar asistencia con un token del curso A. Es un
  agujero de integridad de datos, ya catalogado como P0 en Fase 0 (no es una decisión de negocio
  pendiente: la autenticación se queda).
- **Arreglo:** validar curso↔token↔inscripción en el servidor. Complemento de UX: explicar en la
  pantalla que hace falta sesión iniciada para marcar.

#### UX-14 · Un error de validación borra las calificaciones ya tecleadas · **P1** · ~

- **Evidencia:** `a/CursoCalificar.blade.php:77-91` (`value="{{ $estudiante->calificacion }}"`, sin
  `old()` ni `@error`); `CalificacionController.php:86-90,116` valida y redirige con `back()`.
  Además `:96-109` hace `updateOrInsert` con el valor enviado **aunque venga vacío**, borrando una
  nota existente sin confirmación.
- **Impacto:** con 30 alumnos, una casilla fuera de rango obliga a re-teclear todo sin saber qué
  fila falló. Peor escenario del proceso P4. Afecta a RF-38/RF-39.
- **Arreglo:** `old("calificaciones.$id.nota", …)` + `is-invalid` por fila; no escribir claves vacías;
  renombrar "Guardar Cambios y Finalizar" → "Guardar calificaciones".

#### UX-15 · El mismo porcentaje de asistencia tiene colores distintos según la pantalla · **P2** · ~

- **Evidencia:** umbrales `>=80 / >=50` en `a/AsistenciaConsolidado.blade.php:155` y
  `a/AsistenciaIndividual.blade.php:54`, contra `>=75 / >=50` en `a/CertificacionPanel.blade.php:100-217`.
- **Impacto:** un 77 % se ve verde al certificar y amarillo al consolidar. Rompe la confianza en el
  dato que decide una certificación (RF-44).
- **Arreglo:** un helper único de umbrales usado por las tres vistas. **Confirmar el número** (§3.4).

#### UX-16 · Los resultados de la encuesta de satisfacción no son alcanzables · **P1** · ✔

- **Evidencia:** `Taller/Routes/web.php:146-147` define `taller.cursos.satisfaccion.resultados`;
  la vista `a/CursoSatisfaccionResultados.blade.php` existe; **ninguna vista ni menú enlaza la ruta**
  (grep global: solo aparece en el archivo de rutas). El único enlace de encuesta
  (`a/CursosInscritos.blade.php:133`, `actions-container.blade.php:97`) apunta a `satisfaccion.create`.
- **Impacto:** el proceso P6 recoge datos que nadie puede consultar por la interfaz. **RF-59** está
  marcado ✅ y no lo es.
- **Arreglo:** enlace "Ver resultados" en `actions-container.blade.php`, junto a "Panel de
  Certificación", condicionado a permiso.

#### UX-17 · El aviso "ya respondiste la encuesta" se pierde · **P1** · ✔

- **Evidencia:** `SatisfaccionCursoController.php:23-26` responde `->with('info', …)`, pero
  `template/script.blade.php:89-130` solo renderiza `success`, `warning` y `error`.
- **Impacto:** el participante es redirigido sin explicación y cree que la encuesta no se guardó.
  Afecta a **cualquier** `with('info')` del sistema, no solo a la encuesta.
- **Arreglo:** añadir el bloque `info` en el layout compartido.

#### UX-18 · "Mis Cursos Asignados" (vista del facilitador) no tiene entrada · **P2** · ✔

- **Evidencia:** `Taller/Routes/web.php:31-32` (`taller.mis-cursos-asignados`) sin ninguna
  referencia en vistas, menú ni JS. La vista `a/CursosAsignados.blade.php` existe y es completa.
- **Impacto:** pantalla muerta salvo por URL directa.
- **Arreglo:** enlazarla desde el menú del perfil Facilitador o eliminarla si duplica `mis-cursos`
  (**decisión pendiente**, §3.4).

#### UX-19 · Estadísticas sin estado vacío ni explicación de métricas · **P2** · ~

- **Evidencia:** `a/Estadisticas.blade.php:89-196` dibuja los 8 canvas siempre;
  `EstadisticasController.php:68-81` devuelve arrays vacíos sin datos; labels de KPI en `:310-318`
  sin ayuda.
- **Impacto:** con la base sin datos se ven 8 gráficos en blanco: no se distingue "no hay datos" de
  "se rompió". Y "Ocupación de Cupos" no dice si es promedio ponderado o por curso. Afecta a RF-61/RF-63.
- **Arreglo:** placeholder "Sin datos para los filtros seleccionados" + tooltip por KPI + botón
  "Limpiar filtros".

### 3.2 Wizard de creación de curso

#### UX-01 · El borrador es inalcanzable si se pierde la sesión · **P1** · ~

- **Evidencia:** `BorradorWizardService.php:20-34` busca solo por `session('wizard_curso.token')`;
  sin token, `:36-48` crea un borrador **nuevo y vacío**, aunque el docblock `:10-13` promete que
  sobrevive a la expiración de sesión. Los pasos 4 y 5 validan contra la **sesión**, no contra el
  borrador (`CrearCursoController.php:422-426,496-504`; `CursoRequisitoController.php:121-125,185-189`).
- **Impacto:** al expirar la sesión, el wizard dice "el proceso expiró" y vuelve al Paso 1; lo ya
  guardado en `taller.curso_borradores` queda huérfano. **RF-09 no está ✅.**
- **Arreglo:** fallback `CursoBorrador::where('user_id', …)->latest('updated_at')->first()` con
  opción "retomar borrador", y resolver los pasos 4-5 por borrador en vez de por sesión.

#### UX-02 · "Guardar borrador y salir" no guarda · **P1** · ✔

- **Evidencia:** `a/CursoCrearPaso1.blade.php:18-20` (y `Paso2:21-23`, `Paso3:26-28`,
  `a/CursoRequisitosCrear.blade.php:24-26`) es un `<a href="{{ route('taller.cursos.index') }}">`,
  no un submit.
- **Impacto:** el texto promete persistir y lo tecleado en el paso actual se pierde sin aviso.
- **Arreglo:** hacer submit con el autoguardado del slice actual, o quitar la promesa del texto.

#### UX-03 · Los pasos 1-3 no confirman que se guardó · **P2** · ~

- **Evidencia:** solo el Paso 4 tiene aviso de guardado (`a/CursoPlantillaCrear.blade.php:200`).
- **Arreglo:** marca "Guardado HH:MM" persistente en la barra superior de cada paso.

#### UX-04 · El Paso 4 presume de guardar imágenes que no guarda · **P1** · ~

- **Evidencia:** `a/CursoPlantillaCrear.blade.php:200,684-717` (`enviarAutoguardado` envía **solo**
  `coords`); el comentario `:506-507` confirma que plantilla y firma solo viajan en el submit final.
- **Impacto:** el usuario ve "Borrador guardado", sale, y las imágenes se perdieron. Afecta a RF-13.
- **Arreglo:** subir imagen/firma al seleccionarlas, o cambiar el aviso a "posiciones guardadas; la
  imagen aún no".

#### UX-05 · El Paso 4 se titula "Paso 2" · **P2** · ✔

- **Evidencia:** `a/CursoPlantillaCrear.blade.php:16` → "Paso 2: Calibración de Plantilla", mientras
  el stepper del mismo archivo muestra el paso 4.
- **Arreglo:** renombrar a "Paso 4: Certificado".

#### UX-06 · Detalles del wizard que restan control · **P3** · ~

- **Paso 3:** horas obligatorias sin marcar y error que no dice qué bloque
  (`StorePaso3Request.php:93-98` vs `components/taller/contenido-simple.blade.php:40-45`).
- **Paso 3 y Requisitos:** eliminar bloque/pregunta sin confirmación
  (`contenido-simple.blade.php:29`, `public/js/modules/taller/wizard-paso3.js:87-92`,
  `a/CursoRequisitosCrear.blade.php:120`).
- **Paso 2:** marcar "ALCANCE NACIONAL" borra las localidades sin avisar y no las restaura
  (`a/CursoCrearPaso2.blade.php:275-298`, `wizard-paso2.js:37-41`); la vista previa del código
  PROCINEC no se recalcula al cambiar `fecha_inicio` (`Paso2:115` vs `wizard-paso2.js:15-21`).
- **Revisión:** el paso final no dice qué pasará al pulsar "Finalizar"
  (`a/CursoRevision.blade.php:146-151`); el stepper marca pasos "completados" aunque estén vacíos
  (`components/taller/wizard-steps.blade.php:20`).
- **Paso 1:** no muestra la foto ya guardada al volver (`createPaso1` no pasa `foto_path`).

### 3.3 Hallazgos transversales (afectan a toda la aplicación)

#### UX-20 · Todo error de validación se muestra dos veces · **P1** · ✔

- **Evidencia:** `template/script.blade.php:122-130` dispara un SweetAlert con la lista completa en
  **cada** página autenticada; además cada formulario pinta su propia alerta inline
  (`a/CursoCrearPaso1.blade.php:29-37`, `Security/…/users/create.blade.php:15-24`,
  `a/CursoInscribirse.blade.php:12-20`, …).
- **Impacto:** modal que tapa los campos + alerta con el mismo texto detrás. El usuario cierra el
  modal para volver a leer lo mismo.
- **Arreglo:** un solo canal. Recomendado: inline junto al campo + SweetAlert solo para AJAX.
- **Nota de seguridad:** el bloque usa `{!! implode(…) !!}` sobre mensajes que pueden contener texto
  del usuario (`StorePaso3Request.php:88-89` inyecta el título del bloque) → hay que escaparlo.

#### UX-21 · El sidebar no dice dónde estás · **P1** · ✔

- **Evidencia:** `template/sidebar.blade.php:53-73` nunca añade `active` ni `aria-current`; el layout
  `layouts/kaiadmin-menu.blade.php:24-30` no tiene breadcrumbs.
- **Impacto:** con menú dinámico por perfil (`User::captureMenu()`), todos los ítems lucen igual; el
  usuario navega a ciegas. Incumple RNF-15/16 en su parte de orientación.
- **Arreglo:** marcar ítem activo por ruta + breadcrumb global. **Mejor retorno por esfuerzo de todo
  el informe** (dos archivos, todas las pantallas).

#### UX-22 · El `<title>` de la pestaña es siempre el mismo · **P2** · ✔

- **Evidencia:** `template/head.blade.php:2` → `<title>{{ config('app.name') }}</title>`, pero muchas
  vistas declaran `@section('title', …)` (`a/CursosAsignados.blade.php:3`,
  `a/partials/Buscador-actions/Cursos.blade.php:3`, `a/CursoPlantillaCrear.blade.php:3`, …) que nadie
  renderiza.
- **Arreglo:** `@yield('title', config('app.name'))`. Un solo cambio, arregla todas las vistas.

#### UX-23 · Acciones de estado irreversibles sin confirmación y sin loader · **P1** · ✔/~

- **Evidencia:** `actions-container.blade.php:180,190,195,200` (Aprobar propuesta, Cerrar
  inscripciones, Finalizar curso, Archivar) llaman a `processStatusAction()`
  (`a/CursoDetalle.blade.php:560-572`), que hace `fetch` **sin** confirmación, sin `disabled`, sin
  spinner y sin `.catch`.
- **Impacto:** un clic accidental en "Archivar Definitivamente" no tiene vuelta atrás (CERRADO no
  admite transiciones) y no hay feedback si la petición falla (419 por sesión expirada deja al
  usuario sin saber si pasó algo). Contraste: aprobar una postulación individual **sí** confirma
  (`a/partials/participantes/lista.blade.php:41`), o sea que el criterio es inverso al riesgo.
- **Arreglo:** `Swal.fire` con la consecuencia explícita y botón rojo para las irreversibles;
  `.catch` + `finally` en todos los `fetch` de acciones.

#### UX-24 · Un fallo de red bloquea la interfaz para siempre · **P1** · ~

- **Evidencia:** `a/CursoParticipantes.blade.php:147,153-165` abre un `Swal` con
  `allowOutsideClick:false` + `showLoading()` y el `fetch` no tiene `.catch` ni comprueba `r.ok`.
  Mismo patrón en `a/AsistenciaConsolidado.blade.php:374`, donde el `catch` solo hace `console.error`.
- **Impacto:** ante 403/419/500 o caída de red, el modal "Procesando…" **no se cierra nunca**: hay
  que recargar. Y en asistencia el facilitador concluye "no hay enlace activo" cuando en realidad
  falló la petición.
- **Arreglo:** helper JS compartido `peticionConFeedback()` con loader, `.catch` y mensaje de error.

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

#### UX-28 · Claves de `@error` equivocadas = error invisible en el campo · **P2** · ~

- **Evidencia:** `Security/…/recovery.blade.php:55` (`@error('username')` para el input `email`),
  `recovery_password.blade.php:86` (`@error('password')` para `re_password`),
  `users/create.blade.php:70` y `users/update.blade.php:70` (`@error('type_document')` para
  `document_type_id`).
- **Impacto:** el error existe pero no se pinta junto al campo; solo sale en el modal global. Es
  **prerequisito** de UX-20: si se reduce el canal global antes de corregir esto, esos campos quedan
  sin guía.
- **Arreglo:** corregir las claves **antes** de tocar UX-20.

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
| RF-03 | Recuperación de contraseña por token | ❌ | **P0** | 0 | UX-28 |
| RF-04 | Perfil multi-rol y cambio de perfil | ⚠️ | P1 | 1 | — |
| RF-05 | RBAC por proceso/acción en todas las rutas mutantes | ⚠️ | **P0** | 0 | — |
| RF-06 | Gestión de usuarios, perfiles y permisos | ✅ | P1 | — | UX-29 |
| RF-07 | Auditoría de accesos y acciones críticas | ⚠️ | P1 | 1 | — |
| RF-08 | Cierre de sesión y expiración | ✅ | P1 | — | — |

**Brechas principales**

- **RF-03:** el reset genera un token de 64 caracteres y lo guarda en `security.users.token varchar(50)`;
  PostgreSQL rechaza el `UPDATE`. El usuario no puede recuperar su contraseña.
- **RF-04:** el perfil activo vive en `session('profile_id')` y no se valida contra
  `security.profiles_users.status`; un perfil desactivado puede seguir operando hasta cerrar sesión.
- **RF-05:** solo `CatalogoController`, `UsersController` y `ProfilesController` usan el middleware
  `permiso:`. El resto de Taller valida permisos de forma ad hoc; hay endpoints sin ninguna
  verificación (administración de facilitadores).
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

> **Bloqueante conocido:** los criterios 1-6 no se pueden ejercer hoy porque la pantalla de
> inscripción devuelve 500 (UX-10). La carta está bien implementada por detrás de una puerta rota.

### 4.4 Asistencia

| ID | Requisito | Estado | Prioridad | Fase | UX |
|---|---|---|---|---|---|
| RF-31 | Token QR temporal por actividad | ✅ | P1 | — | UX-13 |
| RF-32 | Marcado por enlace autenticado (con binding curso/token) | ⚠️ | **P0** | 0 | UX-13 |
| RF-33 | Marcado manual por facilitador/gestor | ⚠️ | P1 | 1 | UX-12 |
| RF-34 | Anulación y restauración con motivo | ⚠️ | P1 | 1 | UX-12 |
| RF-35 | Consolidado por curso | ✅ | P1 | — | UX-15 |
| RF-36 | Vista individual por participante | ✅ | P1 | — | UX-12 |
| RF-37 | Regla de asistencia mínima para certificar | ❌ | P1 | 1 | UX-15 |

**Brechas principales**

- **RF-32:** `mostrarConfirmacion()` y `marcar()` no comparan el curso de la URL con
  `tokenRecord->actividad->id_curso`. Un participante del curso B puede usar un token del curso A.
  Además aceptan cualquier estado de inscripción, no solo `aprobado`. La autenticación del enlace es
  intencional y se mantiene.
- **RF-33 / RF-34:** backend completo y sin ningún punto de entrada en la interfaz (UX-12). Estados
  corregidos de ✅ a ⚠️.
- **RF-37:** la certificación no exige un porcentaje mínimo de asistencia; el panel solo muestra el
  porcentaje como información, y lo muestra con umbrales distintos según la pantalla (UX-15).

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
| RF-55 | Aprobación → perfil Facilitador | ⚠️ | **P0** | 0 | — |
| RF-56 | Rechazo con motivo y repostulación | ⚠️ | P1 | 1 | — |

**Brechas principales**

- **RF-55:** los endpoints de administración no tienen RBAC; cualquier usuario autenticado puede
  aprobar su propia postulación. El `id_rol=2` está hardcodeado y no hay restricción única que evite
  duplicados por concurrencia.
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
| RF-59 | Resultados agregados | ⚠️ | P1 | 1 | UX-16 |
| RF-60 | Exportación de resultados | ❌ | P2 | 4 | — |

**Brechas principales**

- **RF-59:** la vista y el controlador existen, pero **no hay ningún enlace** que lleve a los
  resultados (UX-16). Estado corregido de ✅ a ⚠️.
- **Privacidad:** `resultados()` permite el acceso a cualquier autenticado que no sea participante del
  curso; conviene acotarlo por rol/permiso si los resultados son sensibles.
- **UX:** el formulario no evita el doble envío en el cliente (`CursoSatisfaccionForm.blade.php:81-83`)
  y el aviso de "ya respondiste" no se renderiza (UX-17).

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
| UX | UX-10 | El formulario de inscripción devuelve 500 | Proceso P2 inutilizable | `CursoInscribirse.blade.php:23,27,30,142` vs `InscripcionController.php:63` |
| UX | UX-11 | "Corregir Postulación" siempre falla | Participante rechazado sin salida | `actions-container.blade.php:49-55` vs `InscripcionController.php:39-42` |
| UX/RF | UX-13 (RF-32) | El token QR no valida curso ni estado de inscripción | Asistencia falsa entre cursos | `AsistenciaController.php` (`mostrarConfirmacion`/`marcar`) |
| RF | RF-03 | Reset de contraseña roto por longitud de token | Usuarios bloqueados | `security.users.token varchar(50)` vs token de 64 |
| RF | RF-05 | RBAC incompleto en rutas de Taller | Escalada de privilegios | Solo 3 controladores con `permiso:` |
| RF | RF-55 | Admin de facilitadores sin autorización | Auto-aprobación | Endpoints sin RBAC |
| RF | RF-30 | Uploads sin MIME ni tamaño | Archivos peligrosos | Solo `isValid()` |

### 5.2 P1 — Núcleo del negocio

| Tipo | ID | Qué | Impacto | Fase |
|---|---|---|---|---|
| UX | UX-12 | Anular/restaurar/marcar manual sin botón (RF-33, RF-34) | Sin corrección de asistencia | 1 |
| UX | UX-14 | Error de validación borra las notas escritas | Re-trabajo y notas perdidas | — |
| UX | UX-16 | Resultados de encuesta inalcanzables (RF-59) | Datos recogidos sin uso | 1 |
| UX | UX-17 | El flash `info` no se renderiza (RF-29/RF-69) | Avisos perdidos en todo el sistema | — |
| UX | UX-20 | Errores mostrados dos veces | Ruido y campos tapados | 2 |
| UX | UX-21 | Sidebar sin ítem activo ni breadcrumbs | Usuario desorientado | 2 |
| UX | UX-23 | Acciones irreversibles sin confirmación ni loader | Estado del curso cambiado por error | 1 |
| UX | UX-24 | Fallo de red bloquea la UI para siempre | Página inutilizable hasta recargar | 1 |
| UX | UX-27 | Registro público largo, sin pasos y con CDN | Abandono en la puerta de entrada | 2 |
| UX | UX-01 | Borrador del wizard no recuperable (RF-09) | Trabajo perdido | 1 |
| UX | UX-02 | "Guardar borrador y salir" no guarda | Pérdida silenciosa | 1 |
| UX | UX-04 | Paso 4 dice guardar y no guarda imágenes (RF-13) | Diseño de certificado perdido | — |
| RF | RF-04 | Perfil activo no valida `status` | Acceso persistente | 1 |
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
| UX | UX-18 | `mis-cursos-asignados` sin entrada | — |
| UX | UX-15 | Umbrales de asistencia distintos por pantalla | 1 |
| UX | UX-19 | Estadísticas sin estado vacío ni ayuda | 2 |
| UX | UX-22 | `<title>` de pestaña siempre igual | 2 |
| UX | UX-25 | Sin sistema de diseño (Fase 2) | 2 |
| UX | UX-26 | Iconografía sin nombre accesible (RNF-15) | 2 |
| UX | UX-28 | Claves de `@error` equivocadas (prerequisito de UX-20) | 2 |
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
| RNF-02 | Seguridad | RBAC en el 100 % de rutas mutantes | Test negativo por cada ruta crítica | ⚠️ | P0 | 0 |
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
| RNF-02 | Un endpoint sin RBAC puede escalar privilegios | Matriz ruta × perfil con respuesta esperada |
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
| 0 | RF-03, RF-05, RF-32, RF-55 |
| 1 | RF-04, RF-07, RF-09, RF-16, RF-18, RF-20, RF-25, RF-26, RF-29, RF-30, RF-33, RF-34, RF-37, RF-43, RF-44, RF-51, RF-56, RF-59, RF-65, RF-70 |
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
| UX-10, UX-11 | RF-23, RF-25 | 0/1 |
| UX-12 | RF-33, RF-34 | 1 |
| UX-13 | RF-32 (P0) | 0 |
| UX-14 | RF-38, RF-39 | 1 |
| UX-15 | RF-37, RF-44 | 1 |
| UX-16 | RF-59, RF-65 | 1 |
| UX-17 | RF-29, RF-47, RF-69 | 1 |
| UX-18 | RF-21 | 3 |
| UX-19 | RF-61, RF-62, RF-63 | 2 |
| UX-01…UX-06 | RF-09, RF-11, RF-12, RF-13 | 1 |
| UX-20, UX-28 | RNF-17 | 2 |
| UX-21, UX-22 | RNF-15, RNF-16 | 2 |
| UX-23, UX-24 | RNF-15 (prevención de errores), RF-15 | 1 |
| UX-25 | Fase 2 (sistema de diseño), RNF-17 | 2 |
| UX-26 | RNF-15 (WCAG 2.1 AA) | 2 |
| UX-27 | RF-01, RNF-18 | 2 |
| UX-29 | RF-06, RF-68 | 2 |
| UX-30 | RF-02 | 2 |
| UX-31 | Mantenibilidad | 3 |

---

## 8. Tandas de ejecución propuestas

Cada tanda es un commit independiente y testeable.

**Tanda 0 — Desbloquear el flujo P2 (½ día)**
`UX-10`, `UX-11`. Sin esto, inscribirse no funciona (y con ello la carta de permiso, RF-75/RF-76).
Tests: feature de inscripción (crear → rechazar → corregir → aprobar) contra pgsql real.

**Tanda 1 — Feedback, errores y acciones irreversibles (1 día)**
`UX-28` (prerequisito), `UX-20` (un solo canal de error), `UX-17` (flash `info`), `UX-24`
(helper JS con loader y `catch`), `UX-23` (confirmación y spinner en acciones de estado).

**Tanda 2 — Navegación (½ día)**
`UX-21` (ítem activo + breadcrumbs), `UX-22` (`@yield('title')`). Dos archivos, todas las pantallas.

**Tanda 3 — Huecos de flujo (1 día)**
`UX-12`, `UX-16`, `UX-14`, `UX-15`, `UX-19` y `UX-18` según decisión de §3.4.

**Tanda 4 — Wizard (1–1½ días)**
`UX-01`, `UX-02`, `UX-04`, `UX-03`, `UX-05`, `UX-06`.

**Tanda 5 — Accesibilidad (1 día)**
`UX-26`, `UX-30`, más lo medido: `aria-hidden` en decorativos, `aria-label` en botones de icono,
~62 `<label>` sin `for`, un único estilo de foco.

**Tanda 6 — Sistema de diseño y limpieza (según alcance de §3.4)**
`UX-25`, `UX-29`, `UX-31` y el código muerto de §3.5.

**Tandas de seguridad (paralelas, Fase 0 — no son UX)**
RF-03, RF-05, RF-32, RF-55, RF-30. Recomendado atacarlas antes que la Tanda 6.

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
- **Tests:** **39 tests, 38 pasan** (antes se documentaban 23). Único fallo conocido:
  `Tests\Feature\ExampleTest` espera 200 en `/` y recibe 302. Se ejecutan **en el contenedor**:
  `docker compose exec -u www-data app php artisan test`.
- **Pint:** `vendor/bin/pint --test` → **246 archivos, 154 style issues** en verde/rojo por archivo.
- **Rendimiento:** ver `RENDIMIENTO.md` (OPcache sin validación de timestamps, caché de módulos,
  `dirname(__DIR__)` en providers). Login de 2,4–4,8 s a 0,29–0,36 s en caliente.

### 9.2 Cambios de estado respecto a la revisión anterior

| ID | Antes | Ahora | Motivo |
|---|---|---|---|
| RF-09 | ✅ | ⚠️ | El borrador no es recuperable sin el token de sesión y "Guardar borrador y salir" no guarda (UX-01, UX-02) |
| RF-33 | ✅ | ⚠️ | Backend completo, **cero** referencias en vistas/JS (UX-12) |
| RF-34 | ✅ | ⚠️ | Ídem (UX-12) |
| RF-59 | ✅ | ⚠️ | Vista y controlador existen, ningún enlace los alcanza (UX-16) |
| RNF-19 | — | ❌ | 154 style issues medidos |
| RNF-10 | — | ❌ | `CursoDetalleController.php:94-97` sin paginar |
| RNF-14 | — | ❌ | Sigue `sync` |
| RNF-17 | — | ⚠️ | Casi cumplido; los dos `Y-m-d` son inputs `type="date"` legítimos |

**Ajustes de criterio:** un requisito con backend completo pero sin forma de invocarse desde la UI no
se marca ✅. Ese criterio es el que produjo los cuatro cambios anteriores y la diferencia entre
"43 implementados" y "39 implementados de punta a punta".

### 9.3 Carta de permiso (2026-10-20)

El requisito obligatorio `Carta de permiso (Autorización de menores)` y el bloque "Documentos
auxiliares" aplican **solo a cursos dirigidos a niños y adolescentes**. La condición se resuelve por
curso con `taller.modalidades_especiales.es_menor` (Niño/Adolescente), no por la edad del
participante; `comun.personas` no tiene fecha de nacimiento. La plantilla vive en
`app/public/documentos/Autorizacion_menores.docx`. `2026_10_20_000001_add_carta_permiso_to_curso_requisitos`
inserta la carta solo en cursos para menores y `2026_10_20_000002_remove_carta_permiso_from_non_minor_courses`
limpia los cursos que la hubieran recibido por error.

**Prueba:** `app/tests/Unit/Taller/CursoRequisitoCartaPermisoTest.php` (6 casos). Ya se ejecuta en el
contenedor y pasa (la nota anterior sobre falta de `mbstring` en el host quedó obsoleta).

---

## 10. Anexos

### 10.1 Rutas con nombre sin referencia en vistas ni JS

25 de 131. Ruido esperable: rutas usadas por el ciclo de vida (login, AJAX de registro) o por JS
mediante concatenación. **Huérfanas verificadas**: `taller.asistencia.marcar-manual`,
`taller.asistencia.anular`, `taller.asistencia.restaurar`, `taller.cursos.satisfaccion.resultados`,
`taller.mis-cursos-asignados`, `taller.cursos.plantilla.create` (esta última solo por curso; el
wizard usa `wizard.plantilla.create`).

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
(a / b):
Notas:
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
