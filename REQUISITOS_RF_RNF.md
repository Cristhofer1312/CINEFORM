# CINEFORM — Auditoría de requisitos (RF/RNF) y UX/UI

> **Snapshot:** commit `753d192` (incluye Tier 1 y lote de permisos y perfiles) + E1, E2, E5, D2, D3, D5, D6, D7 y D8 sin commitear · rama `main` · 2026-10-26
> **Alcance:** módulos `Taller`, `Security`, `Registro`, `Comun` y `Parametros` (Laravel 10.49 / PHP 8.2 / PostgreSQL 17)
> **Método:** contraauditoría de la versión manual del 2026-10-22. Cada fila marcada "Resuelto" se reverificó en código; los totales se recalcularon desde las tablas; runtime en Docker (suite, Pint, `route:list`, instalación limpia en BD temporal).
> **Runtime:** `php artisan test` → **228 passed / 0 failed (892 aserciones)** sobre la BD de desarrollo con **PHP 8.5.12 y Laravel 13.39** (§9.20), tras E1, E2, E5, D2, D6, D3, D7, D8, D4, las correcciones de §9.17 y perfiles sumados + revisión de seguridad (§9.19, 27/10). `composer audit`: 0 avisos. Instalación limpia (`migrate:fresh --seed` en BD temporal) repetida el 26/10 tras D7/D8: migraciones y seeders correctos, `supervise_courses` otorgado a los perfiles 1 y 4; la suite allí da 197 / 1 por una prueba con fixture dependiente de los datos (§9.9). Pint global (medido antes del Tier 1): 149 issues en 269 archivos.
> **Fuente de verdad:** este documento. Los resúmenes (§2) se derivan de las tablas (§3–§5); si discrepan, mandan las tablas.
> **Estado:** P0 abiertos: **RNF-04** (secretos) y **RNF-13** (respaldos). E1, E2 y E5 corregidos y D2, D5 y D6 aplicados el 26/10, sin commitear. Tier 1 y lote de permisos y perfiles commiteados en `753d192`. Las decisiones D2–D8 y la regla de E5 se respondieron el 26/10 y se registran en §8 en el lote que las implementa.

---

## Contenido

1. [Cómo leer](#1-cómo-leer)
2. [Resumen ejecutivo](#2-resumen-ejecutivo)
3. [Requisitos funcionales](#3-requisitos-funcionales)
4. [Requisitos no funcionales](#4-requisitos-no-funcionales)
5. [Auditoría UX/UI](#5-auditoría-uxui)
6. [Trazabilidad](#6-trazabilidad)
7. [Backlog priorizado](#7-backlog-priorizado)
8. [Decisiones abiertas](#8-decisiones-abiertas)
9. [Changelog de la auditoría](#9-changelog-de-la-auditoría)
10. [Anexos](#10-anexos)
11. [Próxima revisión](#11-próxima-revisión)

---

## 1. Cómo leer

### 1.1 Estados

| Símbolo | Significado |
|---|---|
| ✅ | Implementado con backend y entrada UI verificables. La evidencia runtime se indica con `R`; las filas sin `T`, `M` o `R` se respaldan con inspección estática. |
| ⚠️ | Parcial: falta una regla, una entrada UI, una validación o una verificación |
| ❌ | No existe o está roto |
| 🔍 | No verificable en este entorno; no se asume correcto |
| ⛔ | Retirado del alcance por decisión del usuario. La fila se conserva para no reutilizar el ID, pero no cuenta en los totales de §2 |

**Regla aplicada:** un requisito con backend completo pero sin forma de invocarse desde la UI **no es ✅**. Una brecha que impida el uso correcto del requisito o permita un abuso directo de sus datos lo convierte en ⚠️ aunque la función exista. La deuda de endurecimiento o compatibilidad se documenta en la columna de brecha sin rebajar el estado.

**Regla añadida en esta versión:** un RNF con meta cuantitativa (cobertura, p95, % de rutas) **no es ✅** si la meta no se midió, aunque la parte cualitativa exista (p. ej., "suite verde" no equivale a "cobertura ≥ 70 %").

### 1.2 Prioridades

| Prioridad | Criterio |
|---|---|
| **P0** | Seguridad, pérdida de datos o flujo esencial roto |
| **P1** | Núcleo del negocio; debe estar para considerar el sistema funcional |
| **P2** | Mejora de experiencia, operación o alcance |
| **P3** | Deseable / backlog |

### 1.3 Fases

| Fase | Contenido |
|---|---|
| 0 | Contención: seguridad y bloqueos |
| 1 | Integridad funcional del negocio |
| 2 | Sistema de diseño y accesibilidad |
| 3 | Rediseño de pantallas y flujos públicos |
| 4 | Operación, calidad y exportaciones |

La fase indica cuándo se ejecuta la acción, no su severidad. Por eso un P0 puede planificarse en una fase posterior si depende de infraestructura (p. ej., RNF-13).

### 1.4 Evidencia

| Tipo | Uso |
|---|---|
| `T` | Prueba automatizada existente (`tests/archivo::método`) |
| `M` | Reproducción manual documentada (rol, ruta, pasos, fecha) |
| `S` | Inspección estática (`archivo:línea`) |
| `R` | Medición runtime (comando y salida) |

Para RF y UX, un ✅ requiere backend, entrada UI y evidencia `S` concluyente; `T`, `M` o `R` elevan la confianza y se indican en la fila. Para RNF, ✅ requiere medición `R` o evidencia estática concluyente. En este snapshot se ejecutaron tests, Pint, `route:list`, `migrate:fresh --seed` en BD temporal y consultas de solo lectura; no se ejecutaron PHPStan, cobertura, navegador ni axe.

### 1.5 Rutas abreviadas

Las rutas de módulos son relativas a `app/Modules/`:

- `T/` = `app/Modules/Taller/`
- `S/` = `app/Modules/Security/`
- `R/` = `app/Modules/Registro/`
- `C/` = `app/Modules/Comun/`
- `P/` = `app/Modules/Parametros/`
- `tests/` = `app/tests/`

Las vistas de Taller se abrevian como `T/Resources/views/a/...`.

### 1.6 Limitaciones de este snapshot

- **Working tree sin commitear:** todo lo marcado "resuelto" en esta versión vive solo en la copia local. Un `git checkout .` lo revierte. Hay que commitear antes de dar algo por cerrado (criterio 7 de §10.4).
- **Números de línea:** las filas reverificadas el 2026-10-23 llevan líneas actuales. Las filas no tocadas conservan las del 2026-10-22; varios archivos se reformatearon con Pint (`UsersController`, `RegisterController`, `InscripcionController`, …) y sus líneas pueden haberse desplazado unas pocas posiciones.
- **Sin navegador/axe, PHPStan ni cobertura:** accesibilidad, responsive, compatibilidad y cobertura siguen siendo estáticas o no medidas.
- **Métricas UX (§5.3):** no se volvieron a medir; son las del 2026-10-22.

---

## 2. Resumen ejecutivo

### 2.1 Totales de requisitos funcionales

| Estado | Cantidad | % |
|---|---:|---:|
| ✅ Implementado (evidencia suficiente) | 44 | 54,3 % |
| ⚠️ Parcial | 27 | 33,3 % |
| ❌ Faltante o roto | 10 | 12,3 % |
| **Total RF en alcance** | **81** | **100 %** |
| ⛔ Retirado del alcance (no suma) | 2 | — |

> La versión manual del 22/10 actualizó varias filas a ✅ (RF-15, RF-26, RF-30, RF-38, RF-39) **sin recalcular estos totales**: seguía mostrando 30/28/18. El recuento real desde sus propias tablas era 35/25/16. Tras la contraauditoría del 23/10 quedó 34/26/16; con RF-56 pasó a 35/25/16, y con el Tier 1 (RF-24, RF-33, RF-34 y RF-39 a ✅) quedó en 39/21/16 (§9.4). Con el lote de permisos (RF-77 a RF-83) y la revisión del ciclo de vida (RF-12 y RF-15 a ❌, RF-39 a ⚠️) quedó en 40 / 25 / 18 sobre 83 RF (§9.5). Con E1 y E2 corregidos (RF-12 y RF-15 a ⚠️) quedó en 40 / 27 / 16 (§9.6). Con E5 resuelto (RF-39 a ✅) quedó en 41 / 26 / 16 (§9.7). Con D5 (RF-17 y RF-28 retirados del alcance) quedó en 41 / 26 / 14 sobre 81 RF (§9.8). Con D2 (RF-60 a ✅) quedó en 42 / 26 / 13 (§9.9). Con D3 (RF-37 a ✅) quedó en 43 / 26 / 12 (§9.11). Con D7 y D8 (RF-81 a ⚠️, RF-82 a ✅) queda en 44 / 27 / 10 (§9.12).

### 2.2 Totales por módulo

| Módulo | RF | ✅ | ⚠️ | ❌ |
|---|---:|---:|---:|---:|
| Acceso y seguridad | 8 | 3 | 5 | 0 |
| Cursos y ciclo de vida | 11 | 4 | 7 | 0 |
| Inscripción | 11 | 6 | 4 | 1 |
| Asistencia | 7 | 5 | 2 | 0 |
| Calificaciones | 6 | 3 | 1 | 2 |
| Certificación | 8 | 4 | 2 | 2 |
| Facilitadores | 5 | 5 | 0 | 0 |
| Satisfacción | 4 | 4 | 0 | 0 |
| Estadísticas y administración | 10 | 5 | 3 | 2 |
| Público | 4 | 0 | 1 | 3 |
| Permisos y perfiles | 7 | 5 | 2 | 0 |
| **Total** | **81** | **44** | **27** | **10** |

### 2.3 Requisitos no funcionales

| Estado | Cantidad |
|---|---:|
| ✅ | 2 |
| ⚠️ | 10 |
| ❌ | 11 |
| 🔍 | 2 |
| **Total RNF** | **25** |

P0 abiertos: **RNF-04** (secretos: la contraseña de aplicación de Gmail sigue en `env.example` y la `APP_KEY` anterior sigue en el historial) y **RNF-13** (respaldos). **RNF-01** se cierra. **RNF-03** baja de ✅ a ⚠️ (almacenamiento en disco `public` y borrado de archivos fuera de la transacción). **RNF-21** baja de ✅ a ⚠️: la suite está verde, pero la meta es cobertura ≥ 70 % y E2E, que no se midieron. **RNF-19** se mantiene ❌: Pint no está limpio ni siquiera en los archivos tocados.

### 2.4 Hallazgos UX/UI

| Estado | Cantidad |
|---|---:|
| Cerrados | 24 |
| Abiertos | 8 |
| **Total** | **32** |

UX-32 y UX-33 estaban marcados "Cerrado" pero contados y listados como abiertos; se movieron a §5.1 tras verificarlos con prueba. Abiertos: UX-25, UX-26, UX-27, UX-29, UX-30, UX-31, UX-34 y UX-35.

### 2.5 Lectura operativa

- **Resuelto y verificado:** el P0 de IDs de estado (§6.4). Una instalación limpia deja `estados_curso` con 1–10 y la suite pasa entera sobre ella.
- **Suite verde:** 133/133 (antes 70/37). Se corrigieron pruebas obsoletas y se añadieron pruebas de uploads, IDOR de asistencia, calificaciones, política de contraseñas, reasignación de facilitador, logout, caducidad del código, retiro por gestor y conservación de documentos.
- **Afirmaciones refutadas de la versión manual:** RNF-04 "secretos fuera de git" (falso), RNF-19 "Pint limpio en archivos modificados" (falso: 6 issues), RNF-21 "cobertura ≥ 70 %" (no medida), RF-05 "CRUD de usuarios exige solo VER" (falso: hay permisos por acción), RF-16 "ruta sin llamador UI" (falso desde este working tree), RF-31 "`duracion_minutos` no se valida / `desactivarToken` sin curso" (falso).
- **Hallazgos nuevos:** RF-01 (código de registro sin caducidad ni throttle), RF-27 (retiro que permitía reingresar), RF-39 (notas editables con curso cerrado) y RF-16 (destino de reasignación no facilitador). Los cuatro están corregidos en el Tier 1, sin commitear. El script de depuración `check373.php` de la raíz se eliminó en el Tier 1.
- **No puede considerarse cerrado** mientras RNF-04 siga abierto, RF-44 no aplique nota mínima ni encuesta (la asistencia ya se exige, D3) y los cambios sigan sin commitear.

---

## 3. Requisitos funcionales

### 3.1 Acceso y seguridad

| ID | Requisito | Estado | Evidencia principal | Brecha / siguiente acción |
|---|---|---|---|---|
| RF-01 | Registro público con verificación por código de email | ⚠️ | `R/Routes/web.php:19-24` (`throttle:10,1`); `R/Http/Controllers/RegisterController.php:35` (`random_int`), `:138-139` (caducidad de 15 min en `store()`); `R/Resources/views/register.blade.php`; `tests/Feature/Security/RegistroCodigoSeguridadTest.php` (2 pruebas, `T`) | Corregido en Tier 1: el código caduca y las rutas tienen límite de intentos. Pendiente: el `catch` de `sendCode()` filtra `getMessage()`; el captcha sigue compartido con login. Sin prueba del flujo completo de registro. |
| RF-02 | Login con captcha y throttle | ⚠️ | `S/Routes/web.php:28-35` (`throttle:10,1`); `S/Resources/views/users/login.blade.php` (refresco automático del captcha eliminado en Tier 1) | Corregido: el captcha ya no se recarga solo. Pendiente: la clave de throttle es compartida entre login, recuperación y registro; sin auditoría de intentos; sin prueba. |
| RF-03 | Recuperación de contraseña por token | ✅ | `S/Http/Controllers/SecurityController.php:393-425,443-499`; `tests/Feature/Security/PasswordRecoveryTest.php` | El token se consume en el GET (`:407`); el PUT confía en sesión. El correo dice 5 min (`emails/recovery_password.blade.php:14`) y el backend acepta 30. |
| RF-04 | Perfil multi-rol y cambio de perfil | ✅ | `S/Http/Controllers/SecurityController.php:566-633`; `S/Routes/web.php:38-43`; `tests/Feature/Security/ValidarPerfilActivoTest.php` | Ruta alternativa rota: `registroHome()` sigue sin pasar `$perfiles` (`SecurityController.php:661-665`). |
| RF-05 | RBAC por proceso/acción en todas las rutas mutantes | ⚠️ | `S/Http/Middleware/CheckPermission.php`; `S/Http/Controllers/UsersController.php:18-33`; `S/Http/Middleware/CheckSecurity.php:29-50`; `tests/Feature/Security/RutasProtegidasTest.php:28-51` | 22 rutas siguen en `PENDIENTES_DE_MIGRAR`; `security.admin` (`S/Routes/web.php:70`) sin RBAC; `profiles.disabled` muta por GET (`:61`). **Refutado:** el CRUD de usuarios no exige "solo VER": el constructor exige `CREAR`, `EDITAR` y `SEGURIDAD_USUARIO` por acción, aunque las rutas (`:65-68`) no declaren `permiso:`. |
| RF-06 | Gestión de usuarios, perfiles y permisos | ⚠️ | `S/Routes/web.php:57-70`; `S/Http/Controllers/UsersController.php:140-226,306-341`; `S/Http/Controllers/ProfilesController.php` | Alta de usuario no valida ni guarda `PersonalData`; no envía correo; `password()` sigue comparando contra el hash del **actor** (`UsersController.php:331`); `disabled()` sin ruta. |
| RF-07 | Auditoría de accesos y acciones críticas | ⚠️ | `S/Database/Migrations/2026_10_23_000001_create_auditoria_table.php` (tabla `security.auditoria`, trigger que rechaza UPDATE y DELETE); `app/Services/Security/Auditoria.php`; registra permisos de perfil, activación de perfiles y asignación de perfiles; `tests/Feature/Security/PermisosPerfilesSeguridadTest.php` (`T`) | Base creada en el lote de permisos. Pendiente: registrar accesos (login), transiciones de curso, notas, certificados e inscripciones; pantalla de consulta. |
| RF-08 | Cierre de sesión y expiración | ✅ | `S/Routes/web.php:46` (`Route::post('logout')`); `S/Http/Controllers/SecurityController.php:674` (`invalidate()` y `regenerateToken()`); `navbar-header.blade.php` y `select_profile.blade.php` (formularios POST con CSRF); `tests/Feature/Security/LogoutSeguridadTest.php` (2 pruebas, `T`) | Logout por GET responde 405 (`T`). La expiración de sesión sigue en `config/session.php:34,36` sin cambios. |

### 3.2 Cursos y ciclo de vida

| ID | Requisito | Estado | Evidencia principal | Brecha / siguiente acción |
|---|---|---|---|---|
| RF-09 | Wizard de 5 pasos con borrador persistente | ✅ | `T/Routes/web.php:42-78`; `T/Http/Controllers/CrearCursoController.php:667-851`; `T/Services/BorradorWizardService.php`; `tests/Feature/Taller/WizardBorradorTest.php` (9 pruebas, `R`) | El botón "Guardar borrador" de la revisión es un enlace (`CursoRevision.blade.php:167-169`); falta prueba de persistencia del paso 4. |
| RF-10 | Código PROCINEC automático, único y estable | ✅ | `app/Observers/CursoObserver.php:13-76`; migraciones `2026_09_30_000001_add_unique_to_cursos_codigo.php` y `2026_10_08_000001_not_null_to_cursos_codigo.php` | Sin prueba directa de unicidad/estabilidad del código. |
| RF-11 | Validación de trimestre, fechas y duración | ⚠️ | `T/Http/Requests/StorePaso2Request.php`; `StorePaso3Request.php:95-137`; `WizardBorradorTest::test_paso2_rechaza_fecha_fin_igual_o_anterior_a_fecha_inicio`, `::test_paso3_rechaza_bloques_con_fecha_fuera_del_rango_del_curso` (`R`) | `duracion` y `horas` solo validan entero mínimo 1; no se cruzan con el rango real de fechas. Sin prueba de trimestre. |
| RF-12 | Temario con evaluaciones y ponderaciones | ⚠️ | **E2 corregido (26/10):** `T/Http/Controllers/EditarCursoController.php:58` define `$idPersona` desde `personalData`; el editor carga para el facilitador asignado y para el coordinador (`AceptarAsignacionYEditorTest`, 2 pruebas, `T`; fallan contra `753d192`) | **E3:** `update()` no comprueba el estado del curso (reproducido: se renombró un curso Finalizado). Sin prueba del guardado del temario. |
| RF-13 | Plantilla y firma de certificado | ✅ | `CrearCursoController.php:558-658,821-837`; `tests/Feature/Taller/CertificadoPruebaWizardTest.php` | Sin prueba del guardado real del paso 4 ni del movimiento definitivo de archivos. |
| RF-14 | Requisitos de inscripción | ✅ | `T/Http/Controllers/CursoRequisitoController.php:179-251`; `CrearCursoController.php:691-716`; `tests/Feature/Taller/CartaPermisoMenoresTest.php` | Cobertura funcional adecuada. |
| RF-15 | Transiciones de estado validadas | ⚠️ | **E1 corregido (26/10):** se quitó de `T/Http/Controllers/CursoController.php::updateStatus` el forzado a Rechazado; "Aceptar asignación" deja el curso En Edición con su facilitador, y un destino no permitido desde Por aceptar se rechaza sin cambiar el estado (`AceptarAsignacionYEditorTest`, 2 pruebas, `T`; fallan contra `753d192`). Seeder de estados correcto (`R`, instalación limpia 1–10) | **E4:** en Declinado se ofrece "Enviar a revisión" pero el enum no permite Declinado → Aprobación (422). **E8:** desde Inscripción no hay forma de cancelar el curso. |
| RF-16 | Reasignación de facilitador | ⚠️ | `T/Routes/web.php:166-167`; `T/Http/Controllers/CursoController.php:609-615` (el destino debe tener perfil Facilitador, 422); llamador UI `CursoDetalle.blade.php:908`; `tests/Feature/Taller/IntegridadFlujosTest.php::test_no_se_reasigna_a_persona_sin_perfil_facilitador`; `FacilitadorRechazoYReasignacionTest` (4 pruebas, `T`) | Corregido en Tier 1 la validación del destino. Pendiente: motivo obligatorio; auditoría con autor; la migración `down()` falla si hay cursos con `id_persona` nulo. |
| RF-17 | Duplicar curso / reutilizar plantilla | ⛔ | Retirado del alcance (D5, 26/10). Verificado: no hay ruta, controlador, servicio ni botón que retirar | — |
| RF-18 | Archivar o eliminar borradores | ⚠️ | `T/Services/BorradorWizardService.php:49-55`; `T/Routes/web.php:45-46` (`GET /crear-curso/nuevo`) | No hay listado, archivo ni borrado individual; el GET borra el borrador sin CSRF. |
| RF-19 | Vista previa del curso antes de publicar | ⚠️ | `T/Routes/web.php:67-68`; `CursoRevision.blade.php` | Solo es un resumen del wizard; no hay preview real. Sin prueba. |
| RF-20 | Historial de estados con motivo y autor | ⚠️ | `T/Entities/Curso.php:283-306`; `CursoDetalle.blade.php` | Confirmado: el insert en `curso_estado` (`Curso.php:300-306`) no guarda autor; `updateStatus` no pasa motivo; no hay timeline. |

### 3.3 Inscripción

| ID | Requisito | Estado | Evidencia principal | Brecha / siguiente acción |
|---|---|---|---|---|
| RF-21 | Catálogo de cursos disponibles con filtros | ⚠️ | `T/Http/Controllers/CursoController.php:122-229`; `Cursos.blade.php:20-41` | La UI del participante solo expone búsqueda; no hay portal público; la raíz redirige a login. |
| RF-22 | Detalle con cupos, fechas, modalidad y requisitos | ⚠️ | `CursoDetalle.blade.php`; `T/Http/Controllers/CursoDetalleController.php:151-160` | No carga ni muestra los requisitos en el detalle; solo aparecen al abrir el formulario. |
| RF-23 | Postulación con preguntas y documentos | ✅ | `InscripcionController.php:21-64,79-230`; `CursoInscribirse.blade.php`; `InscripcionFlujoTest::test_participante_puede_enviar_postulacion_y_queda_en_estado_postulado` (`R`) | Cubierto también por las pruebas de documentos de RF-30. |
| RF-24 | Aprobar, rechazar y denegar postulaciones | ✅ | `T/Routes/web.php:131-136`; `T/Http/Controllers/InscripcionController.php:419-445` (`rechazar()` devuelve 400 si la inscripción está aprobada, `:433`); `denegar()` ya bloqueaba aprobadas (`:472`); `tests/Feature/Taller/IntegridadFlujosTest.php::test_rechazar_no_puede_revertir_inscripcion_aprobada` (`T`) | Sin brecha abierta en esta fila. **D6:** la denegación es definitiva para el participante pero revertible por quien gestiona la postulación (vuelve a `postulado`, `RevertirInscripcionTest`); rechazar para corrección ya no reabre una denegación o un retiro. La notificación al participante es RF-29; la rehabilitación con revisión es RF-25. |
| RF-25 | Corrección y rehabilitación | ⚠️ | `InscripcionController.php:97-130,333-370`; `InscripcionFlujoTest` (corrección, `R`) | `rehabilitar()` pasa directo a `aprobado` (`:357`) saltando revisión; sin botón UI. |
| RF-26 | Cancelación por el participante | ✅ | `InscripcionController.php:290-327`; `partials/curso-actions/actions-container.blade.php`; `InscripcionFlujoTest::test_participante_postulado_puede_ver_estado_y_retirar_postulacion` (`R`) | Funciona solo en estado Inscripción. Hace `delete()` físico (`:319`) sin conservar evidencia, y los archivos se borran antes que el registro y fuera de una transacción (`:313-317`). |
| RF-27 | Retiro por gestor con motivo | ⚠️ | `InscripcionController::destroy()` (motivo obligatorio; **D6:** estado `retirado`, distinto de `denegado`); `InscripcionController::revertir()` (vuelve a `aprobado` si hay cupo, con motivo y auditoría); pestaña "Retirados" con botón "Revertir" en `CursoParticipantes.blade.php`; `IntegridadFlujosTest::test_retiro_de_gestor_queda_retirado_y_no_recuperable`; `RetiroGestorMotivoTest`; `RevertirInscripcionTest` (`T`) | El participante retirado no puede volver a postularse. Pendiente: sin UI para **retirar** (el único `DELETE` en la vista es el de cancelar del participante, `CursoDetalle.blade.php:828`); revertir ya tiene UI. |
| RF-28 | Lista de espera | ⛔ | Retirado del alcance (D5, 26/10). Verificado: no hay ruta, modelo, tabla ni UI que retirar | — |
| RF-29 | Notificación de cambio de estado | ❌ | `InscripcionController.php` no usa `Mail` ni `notify` | No se avisa al participante al aprobar, rechazar, denegar, rehabilitar o retirar. |
| RF-30 | Validación de documentos (tipo, tamaño, nombre) | ✅ | `InscripcionController.php:171-213` (`mimes:pdf,jpg,jpeg,png,doc,docx`, `max:5120`, UUID); `InscripcionFlujoTest::test_subida_documento_rechaza_archivo_peligroso_y_archivo_pesado`, `::test_subida_documento_valido_guarda_con_nombre_uuid_y_limpia_previo` (`R`) | Verificado. Endurecimiento pendiente en RNF-03: disco `public`, y el archivo previo se borra (`:197-199`) antes del `commit`, así que un fallo posterior lo pierde. |
| RF-75 | Carta de permiso para menores como documento obligatorio | ✅ | `T/Entities/CursoRequisito.php`; `CursoRequisitoController.php`; `tests/Feature/Taller/CartaPermisoMenoresTest.php` (`R`) | Sin brecha funcional detectada. |
| RF-76 | Plantilla de carta de permiso descargable | ✅ | `CursoInscribirse.blade.php:43-66`; `app/public/documentos/Autorizacion_menores.docx` | Sin prueba automatizada de descarga/visibilidad condicional. |

### 3.4 Asistencia

| ID | Requisito | Estado | Evidencia principal | Brecha / siguiente acción |
|---|---|---|---|---|
| RF-31 | Token QR temporal por actividad | ⚠️ | `T/Http/Controllers/AsistenciaController.php:102` (`duracion_minutos` 1–1440); `qrImage()` sirve solo tokens activos y no expirados (`:253-254`); `tests/Feature/Taller/IntegridadFlujosTest.php::test_qr_no_se_genera_para_token_expirado` (`T`) | Corregido en Tier 1: el QR ya no se sirve para tokens inactivos o expirados. Pendiente: sin prueba del caso válido (requiere Imagick en el contenedor). El marcado por enlace ya valida la expiración. |
| RF-32 | Marcado por enlace autenticado con binding curso/token | ✅ | `AsistenciaController.php:394-518`; `tests/Feature/Taller/AsistenciaTokenSeguridadTest.php` (`R`) | Cobertura sólida del binding y del estado aprobado. |
| RF-33 | Marcado manual por facilitador/gestor | ✅ | `T/Http/Controllers/AsistenciaController.php` `marcarManual()` exige inscripción `aprobado` (mensaje en `:363`); `tests/Feature/Taller/HuecosFlujoTest.php::test_facilitador_puede_marcar_asistencia_manual`; `IntegridadFlujosTest::test_marcado_manual_rechaza_participante_no_aprobado` (`T`) | Sin brecha abierta en esta fila. |
| RF-34 | Anulación y restauración con motivo | ✅ | `T/Http/Controllers/AsistenciaController.php` `anular()` exige `motivo` (`:282`) y acota la búsqueda al curso; `restaurar()` acotada igual; `IntegridadFlujosTest::test_no_se_puede_anular_asistencia_sin_motivo`; `HuecosFlujoTest::test_no_se_puede_anular_ni_restaurar_asistencia_de_otro_curso` (`T`) | Sin brecha abierta en esta fila. La UI ya envía `motivo` en el cuerpo JSON (`AsistenciaIndividual.blade.php`, `anularAsistencia`). |
| RF-35 | Consolidado por curso | ⚠️ | `AsistenciaController.php:22-55` | Confirmado: carga `inscripciones.persona` sin filtrar (`:30`), así que lista postulados, rechazados y denegados como participantes. Sin prueba. |
| RF-36 | Vista individual por participante | ✅ | `AsistenciaController.php:60-95`; `AsistenciaIndividual.blade.php` | No valida inscripción aprobada. |
| RF-37 | Regla de asistencia mínima para certificar | ✅ | **D3 (26/10):** `T/Services/CumplimientoAsistencia.php`; `CertificacionPanelController::aprobar()` rechaza con faltas sin justificar; `AsistenciaController::justificarFalta()` / `quitarJustificacion()`; tabla `taller.asistencia_justificaciones`; `CumplimientoAsistenciaTest` (9, `T`) | Se exige el 100 % de las clases (no evaluaciones); cada falta justificada con motivo cuenta. `descargar()` sigue confiando en la aprobación: no recalcula la asistencia (§9.11). |

### 3.5 Calificaciones

| ID | Requisito | Estado | Evidencia principal | Brecha / siguiente acción |
|---|---|---|---|---|
| RF-38 | Planilla de notas por evaluación | ✅ | `T/Http/Controllers/CalificacionController.php:17-67`; `CursoCalificar.blade.php`; `HuecosFlujoTest` (`R`) | Verificado: filtra inscripciones aprobadas y exige `es_evaluacion` (`:27`). |
| RF-39 | Notas 0–100 con validación | ✅ | **E5 resuelto (26/10):** `T/Http/Controllers/CalificacionController.php::notasCerradas()`; en Finalizado se califica y se corrige, y solo Cerrado bloquea el guardado (403). La planilla en Cerrado es de solo lectura, con aviso y sin botón de guardar (`CursoCalificar.blade.php`), y el botón de la evaluación dice "Ver calificaciones de…" (`CursoContenido.blade.php`). `CalificacionPorEstadoTest` (5 pruebas, `T`; 3 fallan contra `753d192`); `IntegridadFlujosTest::test_no_se_modifican_calificaciones_con_curso_cerrado` (`T`) | No hay bloqueo de notas antes de En Curso; queda dentro de E6 (tabla acción × estado). |
| RF-40 | Promedio ponderado | ⚠️ | `CursoDetalleController.php`; `CertificacionPanelController.php:63-77` | Renormaliza sobre la ponderación evaluada y no exige que las ponderaciones sumen 100. Sin prueba. |
| RF-41 | Visibilidad del promedio en estados 7/8/9 | ✅ | `CursoDetalleController.php`; `CursoDetalle.blade.php:47-54` | Sin prueba específica. |
| RF-42 | Exportación de acta / CSV | ❌ | `T/Routes/web.php:169-173` solo `index` y `store` | No existe ruta, acción ni generación CSV/acta. |
| RF-43 | Auditoría de cambios de nota | ❌ | `CalificacionController.php` sobrescribe con `updateOrInsert` | No conserva valor anterior ni actor por cambio (solo el último `calificado_por`). |

### 3.6 Certificación

| ID | Requisito | Estado | Evidencia principal | Brecha / siguiente acción |
|---|---|---|---|---|
| RF-44 | Panel de elegibilidad (asistencia + nota + encuesta) | ⚠️ | `CertificacionPanelController.php`; `CertificacionPanel.blade.php`; `CumplimientoAsistenciaTest::test_el_panel_muestra_la_falta_y_el_boton_justificar` (`T`) | **Asistencia aplicada (D3):** muestra asistidas, justificadas y faltas sin justificar, permite justificar o quitar cada falta y deshabilita Aprobar sin el 100 %. Sigue sin aplicar nota mínima ni encuesta. |
| RF-45 | Aprobación/denegación por participante | ✅ | `CertificacionPanelController.php:94-166`; `CertificacionPanel.blade.php:135-151` | Confirmado: `aprobar()` no comprueba el estado del curso, así que se puede aprobar antes de Finalizado. Sin prueba positiva. |
| RF-46 | Generación de PDF con QR local | ✅ | `T/Services/CertificadoService.php`; `T/Services/BaconQrGenerator.php` | Sin prueba de PDF/QR. |
| RF-47 | Descarga bloqueada hasta responder la encuesta | ✅ | `T/Http/Controllers/CertificadoController.php:33-83`, bloqueo en `:64-69` | Sin prueba de descarga bloqueada/desbloqueada. El `catch` (`:80-82`) filtra `getMessage()` al usuario. |
| RF-48 | Verificación pública | ✅ | `CertificadoController.php:379-419`; `T/Services/CertificadoCodigo.php`; `tests/Unit/Taller/CertificadoCodigoTest.php` (`R`) | Acepta formato legado sin HMAC (`CertificadoCodigo.php:61-68`). |
| RF-49 | Revocación y reemisión | ❌ | `T/Routes/web.php:297-302` solo panel/aprobar/denegar | No existe flujo de revocación ni reemisión con motivo. |
| RF-50 | Emisión masiva | ❌ | `CertificacionPanelController.php:94-166` procesa una inscripción por petición | No hay selección múltiple. |
| RF-51 | Notificación con enlace de descarga | ⚠️ | `CertificacionPanelController.php:111-124`; `emails/certificacion_aprobada.blade.php` | El correo lleva a login, no a descarga directa; `Mail::queue` es síncrono con `QUEUE_CONNECTION=sync`; sin reintentos. |

### 3.7 Facilitadores

| ID | Requisito | Estado | Evidencia principal | Brecha / siguiente acción |
|---|---|---|---|---|
| RF-52 | Landing y requisitos globales | ✅ | `T/Http/Controllers/PostulacionFacilitadorController.php:21-43`; `T/Routes/web.php:265-270` | Sin prueba positiva. |
| RF-53 | Postulación con documentos | ✅ | `T/Http/Requests/PostulacionFacilitadorRequest.php:28-56` (`mimes:pdf,jpg,jpeg,png`, `max:5120`); `PostulacionFacilitadorController.php:80-160` (UUID `:142`) | Sin prueba positiva. |
| RF-54 | Revisión de documentos | ✅ | `PostulacionFacilitadorController.php:384-394`; `T/Routes/web.php:291-294` | Sin prueba de acceso del postulante/coordinador. |
| RF-55 | Aprobación → perfil Facilitador | ✅ | `PostulacionFacilitadorController.php:246-318`; grupo `permiso:` en `T/Routes/web.php:275-290`; `tests/Feature/Security/PostulacionFacilitadorSeguridadTest.php` (`R`) | Los 2 fallos RBAC del 22/10 (panel 200, `aprobar` 404) están corregidos: ahora 403. Sin prueba positiva de asignación de perfil. |
| RF-56 | Rechazo con motivo y repostulación | ✅ | `PostulacionFacilitadorController.php:330-375` (rechazo sin borrado de archivos); `tests/Feature/Taller/PostulacionFacilitadorRechazoTest.php` (`T`) | Corregido 2026-10-23: el rechazo ya no borra documentos ni deja respuestas huérfanas; la prueba verifica estado, motivo, respuesta en BD y archivo en disco. Repostulación sin prueba específica. |

### 3.8 Satisfacción

| ID | Requisito | Estado | Evidencia principal | Brecha / siguiente acción |
|---|---|---|---|---|
| RF-57 | Formulario de 4 preguntas (1–5) | ✅ | `T/Services/SatisfaccionCursoService.php:14-29`; `T/Http/Controllers/SatisfaccionCursoController.php` | Sin prueba específica. |
| RF-58 | Una respuesta por inscripción | ✅ | Migración `2026_09_25_000000_create_resenas_satisfaccion_table.php`; `SatisfaccionCursoService.php:39-64` | Sin prueba de doble envío/unique. |
| RF-59 | Resultados agregados | ✅ | `SatisfaccionCursoService.php:67-120`; `CursoPolicy::verSatisfaccion` (coordinador o facilitador asignado, D2); `HuecosFlujoTest::test_facilitador_puede_ver_resultados_de_satisfaccion` (`R`); `ReporteSatisfaccionTest::test_un_facilitador_de_otro_curso_no_consulta_los_resultados` (`T`) | — |
| RF-60 | Exportación de resultados | ✅ | **D2 (26/10):** `SatisfaccionCursoController::reporte()` + `Modules/Taller/Exports/ReporteSatisfaccionExport.php` (Excel); permiso `export_satisfaction` (Coordinador y Administrador); botón en `CursoSatisfaccionResultados.blade.php`; `ReporteSatisfaccionTest` (6 pruebas, `T`) | Solo Excel; sin PDF. |

### 3.9 Estadísticas y administración

| ID | Requisito | Estado | Evidencia principal | Brecha / siguiente acción |
|---|---|---|---|---|
| RF-61 | KPIs globales | ✅ | `T/Http/Controllers/EstadisticasController.php:23-46`; `T/Services/EstadisticasService.php:55-100` | Sin prueba específica. |
| RF-62 | Filtros por año, modalidad, actividad, aspecto y estado | ✅ | `EstadisticasController.php:86-95`; `EstadisticasService.php:16-36` | Sin prueba de filtros combinados. |
| RF-63 | Gráficos | ✅ | `EstadisticasService.php:106-270`; `Estadisticas.blade.php:100-194` | Sin prueba de datasets. |
| RF-64 | Exportación de reportes | ❌ | `T/Routes/web.php:305-309` solo `index` y `datos` | No existen CSV/PDF. |
| RF-65 | Alcance por rol | ⚠️ | `T/Routes/web.php:305` solo `VER_ESTADISTICAS` | Permiso global, sin alcance por facilitador, localidad o programa. |
| RF-66 | CRUD de catálogos | ✅ | `T/Http/Controllers/CatalogoController.php`; `T/Routes/web.php:222-240` | Sin prueba de crear/editar/toggle. |
| RF-67 | CRUD de requisitos de facilitador | ✅ | `PostulacionFacilitadorController.php:194-240`; `T/Routes/web.php:280-285` | Sin prueba CRUD. |
| RF-68 | CRUD de usuarios/perfiles | ⚠️ | `UsersController.php:140-341`; `ProfilesController.php`; `S/Routes/web.php:57-68` | `UsersController::disabled` (`:343`) sin ruta; el alta no crea `PersonalData`; desactivación sin confirmación. |
| RF-69 | Notificaciones in-app | ❌ | Sin modelo, tabla ni rutas | No hay campana ni bandeja in-app. |
| RF-70 | Auditoría centralizada | ⚠️ | Tabla única `security.auditoria` (inmutable) y servicio `Auditoria::registrar()`, ver RF-07 | Falta cubrir los procesos de cursos y una pantalla de consulta con filtros. |

### 3.10 Público

| ID | Requisito | Estado | Evidencia principal | Brecha / siguiente acción |
|---|---|---|---|---|
| RF-71 | Portal público de cursos | ❌ | `app/routes/web.php:23-26` (raíz → login); `tests/Feature/ExampleTest::test_la_raiz_redirige_al_login_para_invitados` (`R`, 302) | No hay catálogo público. |
| RF-72 | Verificación pública de certificados | ⚠️ | `T/Routes/web.php:217-219`; `CertificadoController.php:379-419`; `certificados/verificar.blade.php` | Layout con CDN dentro del body; `w-30` no existe; formato legado sin HMAC permite enumeración. |
| RF-73 | Compartir curso | ❌ | Sin ruta de compartir | No hay Open Graph ni botón de compartir. |
| RF-74 | Contacto / soporte | ❌ | `footer.blade.php` solo enlaza a `cnac.gob.ve` | No hay canal de soporte. |

### 3.11 Permisos y perfiles

| ID | Requisito | Estado | Evidencia principal | Brecha / siguiente acción |
|---|---|---|---|---|
| RF-77 | Edición de permisos sin escalada de privilegios | ✅ | `app/Services/Security/Autorizacion.php:38-42` (`PERMISOS_SOLO_RAIZ`), `:175-200` (`puedeOtorgar`, `motivoNoOtorgable`); `S/Http/Controllers/ProfilesController.php:186-300` (diferencia en transacción; rechaza permisos no otorgables; conserva los que el actor no controla); `profiles/permissions.blade.php` (casillas bloqueadas con motivo); `PermisosPerfilesSeguridadTest` (5 pruebas, `T`; fallan contra HEAD) | Solo el raíz otorga `profiles:permissions`, `users.asignar_perfil:assign` y `users:security`. |
| RF-78 | Asignación de perfiles sin quitar los que el actor no puede otorgar | ✅ | `R/Http/Controllers/PersonaPerfilController.php:151-230` (diferencia; conserva perfiles no controlados; ignora los reenviados que ya tiene; auditado); `PermisosPerfilesSeguridadTest` (2 pruebas, `T`; fallan contra HEAD) | **D8 (26/10):** los perfiles que el actor no posee no se pueden asignar (403) ni quitar (se conservan); la pantalla los muestra deshabilitados. Ver RF-82 y §9.12. |
| RF-79 | Catálogos: ver separado de crear y editar | ✅ | `T/Routes/web.php:222-247` (`VER` / `CREAR` / `EDITAR`); migración `database/migrations/2026_10_23_000002_separa_permisos_catalogos.php` (otorga los nuevos permisos a quien ya tenía `view`); seeders actualizados; `CatalogoController::index` y `Catalogos.blade.php` ocultan botones sin permiso; `PermisosPerfilesSeguridadTest` (2 pruebas, `T`) | Admin y Coordinador conservan crear y editar; se pueden retirar por separado. |
| RF-80 | Elegibilidad única como facilitador | ✅ | `Autorizacion::consultaElegiblesFacilitador()` y `esElegibleFacilitador()` (`:207-227`): cuenta activa + perfil activo con asignación activa + `EDITAR_CURSO`; usada en `EditarCursoController`, `CursoDetalleController` y `CursoController::reasignarFacilitador`; `PermisosPerfilesSeguridadTest`, `IntegridadFlujosTest` (`T`) | Coordinador y Administrador también son elegibles porque tienen `EDITAR_CURSO` en la BD actual. |
| RF-81 | Autorización por acción específica y alcance, sin roles encubiertos | ⚠️ | **D7 (26/10):** permiso `supervise_courses` (SecurityAction 28) como alcance global; `ResuelveRolesTaller::supervisaCursos()`, `alcanzaCurso()`; `esGestor()` = `GESTIONAR_CURSO` **y** `SUPERVISAR_CURSOS`; `manage_course` ya no es comodín de ver participantes, asistencia, certificación ni rehabilitar; helpers `supervisaTodosLosCursos()` / `gestionaTodosLosCursos()` en los controladores; `SupervisarCursosTest` (5, `T`) | Queda `esFacilitador()` = `EDITAR_CURSO`, que también decide la elegibilidad (RF-80), y las acciones de gestión siguen agrupadas en `manage_course` (editar, estados, requisitos, reasignar). Separarlas es parte del rediseño del ciclo de vida. |
| RF-82 | Delegación de perfiles configurable por permiso | ✅ | **D8 (26/10):** `R/Http/Controllers/PersonaPerfilController.php::perfilesAsignables()` (perfiles activos con asignación activa del actor; el raíz, todos) y `assignProfiles()`; `asignar_perfil.blade.php` deshabilita los no asignables; `DelegacionPerfilesTest` (5, `T`) | La regla es por pertenencia, no por lista configurable: un perfil personalizado lo asigna quien lo tenga. En una instalación limpia el coordinador sembrado no tiene el perfil Facilitador y por tanto no puede asignarlo (§9.12). |
| RF-83 | Catálogo único de acciones y procesos por dominio | ⚠️ | La pantalla de permisos ya lee las acciones de `security.permissions` (`Helpers.php`, `showActions`) en vez de `processes.actions` | Siguen existiendo `processes.actions` (sin uso en la pantalla), los 5 métodos de `SecurityAction` y un único proceso de cursos ligado al nombre de ruta `taller.cursos.index`. |

---

## 4. Requisitos no funcionales

| ID | Categoría | Requisito | Estado | Evidencia / medición | Brecha |
|---|---|---|---|---|---|
| RNF-01 | Seguridad | Política única de contraseñas | ✅ | `app/app/Providers/AppServiceProvider.php:28-30` (`min(10)->mixedCase()->numbers()->symbols()->max(72)`); usada en `RegisterController.php:99`, `SecurityController.php:331,478,780`, `UsersController.php:153,327`; `tests/Feature/Security/PoliticaContrasenaTest.php` (`R`) | Verificado. Sin historial de contraseñas (lo pedía §4.1 anterior); se reclasifica como P2. |
| RNF-02 | Seguridad | RBAC en el 100 % de rutas mutantes | ⚠️ | `tests/Feature/Security/RutasProtegidasTest.php:28-51` (22 en `PENDIENTES_DE_MIGRAR`, 2 en `AUTORIZADAS_EN_CONTROLADOR`); `T/Routes/web.php:275-290` | Meta no cumplida: 22 rutas dependen de comprobaciones en el controlador; `security.admin` sin ninguna. |
| RNF-03 | Seguridad | Uploads seguros | ⚠️ | `InscripcionController.php:171-213`; `PostulacionFacilitadorRequest.php:41-43`; `PostulacionFacilitadorController.php:142-145`; `InscripcionFlujoTest` (`R`) | **Baja de ✅:** tipo, tamaño y nombre UUID resueltos, pero ambos flujos guardan en el disco `public` (`storage/app/public`, `visibility=public`), no en almacenamiento privado; si se ejecuta `storage:link` los documentos personales quedan servidos por URL. El borrado del archivo previo ocurre antes del `commit`. |
| RNF-04 | Seguridad | Secretos fuera de git | ❌ | 27/10: `MAIL_PASSWORD` vaciado en `env.example` (working tree), pero sigue en el historial y sin revocar (§9.19). Antes: `env.example:74` (`MAIL_PASSWORD` con formato de contraseña de aplicación de Gmail, 16 caracteres + espacios); `APP_KEY` real en el historial (`git log -S` → `8e8036d`); `docker-compose.yml:43` (`POSTGRES_PASSWORD: 123456`) | **Refutado "resuelto":** solo se vació `APP_KEY` en el working tree. La credencial de correo sigue versionada y ninguna se rotó. Acción: revocar la contraseña de aplicación de Gmail, rotar `APP_KEY` en todos los entornos, vaciar `MAIL_PASSWORD` en `env.example`, mover `POSTGRES_PASSWORD` a `.env` y valorar purgar el historial. |
| RNF-05 | Seguridad | HTTPS + headers | ❌ | `nginx/nginx.conf:2` (`listen 80`); sin `add_header` | Sin TLS, HSTS, CSP, `X-Frame-Options` ni `nosniff`. |
| RNF-06 | Auditoría | Log inmutable de acciones críticas | ⚠️ | `security.auditoria` con trigger `auditoria_sin_cambios` que rechaza UPDATE y DELETE (`R`: probado en instalación limpia y en `PermisosPerfilesSeguridadTest::test_auditoria_no_admite_modificaciones`) | Inmutabilidad resuelta; cobertura parcial (solo permisos y perfiles). |
| RNF-07 | Rendimiento | Listados p95 < 500 ms | ⚠️ | `RENDIMIENTO.md:10-14`: cursos 0,46–0,88 s | Sin benchmark con volumen ni p95; el máximo documentado supera 500 ms. |
| RNF-08 | Rendimiento | PDF de certificado < 2 s | 🔍 | `CertificadoController.php:72-79`; FPDF síncrono | Sin medición reproducible. |
| RNF-09 | Rendimiento | Dashboard < 1 s | ✅ | `RENDIMIENTO.md:11` (~0,41 s) | Cumple en dev; sin p95 ni caché explícita. |
| RNF-10 | Escalabilidad | Paginación obligatoria | ❌ | `UsersController.php:46-64` (`limit(500)` sin paginar); `AsistenciaController.php:22-55`; `CertificacionPanelController.php:33-50` | Varios endpoints devuelven >100 registros sin paginar. |
| RNF-11 | Escalabilidad | Índices | ⚠️ | Migración `2024_01_01_000004_create_taller_tables.php` sin `index()` en FKs; `UsersController.php:58-63` usa `ILIKE '%...%'` | FKs sin índice; sin `EXPLAIN`. |
| RNF-12 | Disponibilidad | 99,5 % mensual; healthchecks | ⚠️ | `docker-compose.yml:17-21,49-53`; webserver sin healthcheck | Sin monitorización ni alertas. |
| RNF-13 | Disponibilidad | Respaldos diarios, RPO 24 h, RTO 4 h | ❌ | Sin scripts `pg_dump`/`pg_restore` ni cron | Sin respaldo automático ni restauración probada. |
| RNF-14 | Colas | Correos y PDFs asíncronos | ❌ | `env.example:44` y `app/.env.example:35` (`QUEUE_CONNECTION=sync`) | Todo corre en línea; sin worker ni reintentos. |
| RNF-15 | Usabilidad | WCAG 2.1 AA; contraste 4,5:1; foco visible | ❌ | §5.3 (medición del 22/10) | Foco poco visible, labels sin asociar, iconos sin nombre accesible. |
| RNF-16 | Usabilidad | Responsive 360–1920 sin scroll horizontal | ❌ | `S/Resources/views/partials/style.blade.php:15`; `CursoParticipantes.blade.php:119-121` | Tablas fuerzan ancho; sin pruebas de viewport. |
| RNF-17 | Usabilidad | Fechas `d/m/Y`, números `es-VE`, mensajes en español | ⚠️ | `number_format()` en-US en `CertificacionPanel.blade.php` y `CursoSatisfaccionResultados.blade.php` | Números no están en es-VE; mensajes mezclados (claves `__()` en inglés en Security). |
| RNF-18 | Compatibilidad | Chrome, Edge, Firefox y Safari últimas 2 versiones | 🔍 | Sin `browserslist`, CI ni Dusk/Playwright | Sin evidencia. |
| RNF-19 | Mantenibilidad | PSR-12 + Pint en verde | ❌ | `R`: `pint --test` → **149 issues / 269 archivos**; sobre los 35 archivos tocados → **6 issues** (`BaseController`, `CursoController`, `EditarCursoController`, `CondicionalEditarCurso`, `EstadoCurso`, `CursoPolicyTest`) | **Refutado "Pint limpio en archivos modificados".** Ejecutar `pint` sobre lo tocado antes de commitear; deuda global aparte. |
| RNF-20 | Mantenibilidad | PHPStan nivel 5 | ❌ | `app/composer.json` sin `phpstan`/`larastan`; sin `phpstan.neon` | Sin análisis estático. |
| RNF-21 | Pruebas | Cobertura ≥ 70 % y E2E | ⚠️ | `R`: **120 passed / 0 failed (488 aserciones)**; 24 archivos de test | **Baja de ✅:** suite verde, pero cobertura no medida (no hay Xdebug/PCOV configurado en la suite) y no existen pruebas E2E. Módulos sin pruebas: estadísticas, satisfacción (salvo RF-59), certificación PDF. |
| RNF-22 | Observabilidad | Logs estructurados + Sentry + alertas | ❌ | `config/logging.php` canal `single`; sin Sentry | Sin observabilidad. |
| RNF-23 | Legal | Consentimiento, retención y derecho de acceso | ❌ | `register.blade.php` sin consentimiento | Sin base legal ni procedimientos. |
| RNF-24 | i18n | Español base; inglés opcional | ⚠️ | `app/resources/lang/es.json`; sin `en` | i18n incompleta. |
| RNF-25 | Documentación | README, runbook y ADRs | ⚠️ | `app/README.md`; `RENDIMIENTO.md`; sin `RUNBOOK.md` ni ADRs | Falta documentación operativa. |

### 4.1 RNF críticos (P0)

| ID | Por qué es P0 | Verificación pendiente |
|---|---|---|
| RNF-04 | Una credencial de correo versionada permite enviar correo como la institución; la `APP_KEY` del historial descifra cookies e IDs cifrados de cualquier entorno que la reutilice | Revocar la contraseña de aplicación, rotar `APP_KEY`, vaciar `env.example`, comprobar con `git grep` y `git log -S` |
| RNF-13 | Sin respaldo, un error de migración es irrecuperable | Restauración cronometrada en staging |

RNF-01 sale de esta tabla (resuelto). RNF-03 baja a P1: tipo y tamaño ya están validados; queda el almacenamiento privado.

---

## 5. Auditoría UX/UI

### 5.1 Hallazgos cerrados

| ID | Hallazgo | Evidencia de cierre |
|---|---|---|
| UX-01 | Borrador irrecuperable sin sesión | `T/Services/BorradorWizardService.php`; `WizardBorradorTest::test_recupera_borrador_en_bd_si_la_sesion_ha_expirado_ux_01` (`R`) |
| UX-02 | "Guardar borrador y salir" no guardaba | `StorePaso1Request.php:19`; `WizardBorradorTest` pasos 1–3 (`R`) |
| UX-03 | Pasos 1–3 sin confirmación de guardado | Badge "Borrador guardado" en los 5 pasos |
| UX-04 | Paso 4 presumía de guardar imágenes | `CursoPlantillaCrear.blade.php:729,752` |
| UX-05 | Paso 4 titulado "Paso 2" | `CursoPlantillaCrear.blade.php:3,15,46` |
| UX-06 | Detalles del wizard sin control | `wizard-paso3.js`; `WizardBorradorTest::test_validacion_de_rango_de_horas_identifica_bloque_con_error_ux_06` (`R`) |
| UX-10 | Formulario de inscripción devolvía 500 | `InscripcionFlujoTest::test_pantalla_inscribirse_retorna_200_para_participante_sin_postulacion` (`R`) |
| UX-11 | "Corregir postulación" era callejón sin salida | `InscripcionController.php:97-130`; `InscripcionFlujoTest` (`R`) |
| UX-12 | Acciones de asistencia sin botón | `AsistenciaIndividual.blade.php`; `AsistenciaConsolidado.blade.php` |
| UX-13 | QR permitía marcar en curso equivocado | `AsistenciaTokenSeguridadTest` (`R`) |
| UX-14 | Error de validación borraba notas | `CalificacionController.php`; `CursoCalificar.blade.php` |
| UX-15 | Umbrales distintos por pantalla | `app/Helpers/Helpers.php`; `HuecosFlujoTest::test_helpers_umbral_asistencia_unificado` (`R`) |
| UX-16 | Resultados de encuesta inalcanzables | `CondicionalEstadoCurso.php`; `CursoPolicy`; `HuecosFlujoTest` (`R`) |
| UX-17 | Aviso "ya respondiste" no se renderizaba | `template/script.blade.php:89-131` |
| UX-18 | `mis-cursos-asignados` sin entrada | `CursoInscritoController.php:25-56`; `HuecosFlujoTest` (`R`) |
| UX-19 | Estadísticas sin estado vacío ni ayuda | `Estadisticas.blade.php` |
| UX-20 | Error de validación mostrado dos veces | Modal global removido; residuo en 9 vistas (UX-34) |
| UX-21 | Sidebar sin ítem activo ni breadcrumbs | `template/sidebar.blade.php`; `layouts/kaiadmin-menu.blade.php` |
| UX-22 | `<title>` siempre igual | `template/head.blade.php:2` |
| UX-23 | Acciones irreversibles sin confirmación/loader | `CursoDetalle.blade.php`; `public/js/menu.js` |
| UX-24 | Fallo de red bloqueaba la UI | `public/js/menu.js` (`peticionConFeedback`) |
| UX-28 | Claves `@error` equivocadas | `recovery_password.blade.php`; `users/create.blade.php` |
| UX-32 | IDOR en anulación/restauración de asistencia | `AsistenciaController.php:279,305` (`whereHas('actividad', …id_curso)`); `HuecosFlujoTest::test_no_se_puede_anular_ni_restaurar_asistencia_de_otro_curso` (`R`) |
| UX-33 | `CalificacionController::store` no validaba contenido↔curso/inscrito | `CalificacionController.php:77-102`; `HuecosFlujoTest::test_calificacion_rechaza_persona_no_inscrita_y_contenido_de_otro_curso` (`R`) |

### 5.2 Hallazgos abiertos

#### UX-25 · No hay sistema de diseño · P2 · Abierto, con residuo (D4 aplicada en todos los módulos, §9.13–§9.17)

- **Evidencia:** 419 `style="` en 97 vistas; 35 bloques `<style>`; dos azules de marca; `btn-success` usado para navegar.
- **Impacto:** cada pantalla se ve distinta y cambiar un color exige tocar varios archivos.
- **Acción:** tokens en `app/public/css/main.css`; `<style>` local solo con `@push('styles')`.
- **Avance (26/10):** sistema documentado en `DESIGN.md` y aplicado a Taller, Security, Registro y vistas generales: sin colores fuera de paleta (salvo correos y certificados PDF), utilidades únicas, colores de estado con 4.5:1, foco visible. **Residuo para cerrar:** unos 380 `style=` de tamaño o de estado JS y bloques `<style>` locales que no van por `@push('styles')`; el `padding: 0 !important` global de las celdas de tabla.

#### UX-26 · Iconografía y controles sin nombre accesible · P2 · Abierto

- **Evidencia:** 64 labels sin `for`; 17 botones solo-icono, 0 con `aria-label`.
- **Impacto:** lectores de pantalla y navegación táctil no identifican controles.
- **Acción:** `aria-label` en botones de icono, `aria-hidden` en decorativos, labels asociados y foco visible.

#### UX-27 · Registro público largo, sin pasos y con CDN · P1 · Abierto

- **Evidencia:** `R/Resources/views/register.blade.php` (~20 campos en una página); jQuery 3.6.0 por CDN; selects `disabled` sin rehidratar `old()`; `autocomplete="off"`.
- **Impacto:** abandono en la puerta de entrada y fallo si el CDN está bloqueado.
- **Acción:** dividir en pasos, rehidratar selects, quitar CDN, corregir `autocomplete`.

#### UX-29 · Usuarios: no se puede reactivar y desactivar es silencioso · P2 · Abierto

- **Evidencia:** `UsersController::disabled()` (`:343`) sin ruta; inactivo = solo badge.
- **Acción:** acción Activar/Desactivar por fila con confirmación, como perfiles.

#### UX-30 · Captcha que cambia solo y autofill bloqueado · P2 · Abierto

- **Evidencia:** `users/login.blade.php` (el `setInterval` de 120 s se eliminó en el Tier 1); `register.blade.php:79` con `autocomplete="off"`.
- **Acción:** no autorefrescar; tokens `username`, `current-password`, `new-password`.

#### UX-31 · Mezcla de `route()` y URLs crudas · P3 · Abierto

- **Evidencia:** `users/login.blade.php`; `register.blade.php`; `asignar_perfil.blade.php`; `CursoDetalle.blade.php:908` (reasignación con `url('taller/cursos')` + concatenación, nueva).
- **Acción:** unificar en `route()`.

#### UX-34 · Doble aviso de validación residual · P2 · Abierto

- **Evidencia:** 9 vistas con resumen `$errors->any()` y `@error` inline.
- **Acción:** un solo canal por formulario.

#### UX-35 · Ojo de contraseña no accesible · P2 · Abierto

- **Evidencia:** `<div class="show-password">` no focusable en `users/login.blade.php`, `recovery_password.blade.php`, `update_profile.blade.php`, `password.blade.php`.
- **Acción:** `<button type="button" aria-label="Mostrar contraseña">`.

### 5.3 Métricas UX (medición del 2026-10-22, no repetida)

| Métrica | Valor |
|---|---:|
| `style="` | 419 |
| Bloques `<style>` | 35 |
| `<label>` / con `for` / sin asociar | 156 / 92 / 64 |
| `aria-hidden` / `aria-label` | 5 / 28 |
| Botones solo-icono / con `title` / con `aria-label` | 17 / 9 / 0 |
| `@error` | 59 |
| `Swal.fire` | 38 |
| `peticionConFeedback` | 7 |

---

## 6. Trazabilidad

### 6.1 RF → proceso de negocio

| Proceso | RF |
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

### 6.2 Cobertura de pruebas

| Grupo | Con prueba directa | Sin prueba específica |
|---|---|---|
| Seguridad | RF-01 (código y throttle), RF-03, RF-04, RF-05 (parcial), RF-07 (parcial), RF-08, RF-77, RF-78, RF-79, RF-80 | RF-02, RF-06, RF-81, RF-82, RF-83 |
| Cursos | RF-09, RF-11 (fechas), RF-12 (carga del editor), RF-13 (parcial), RF-14, RF-15 (incluida la aceptación), RF-16 | RF-10, RF-18, RF-19, RF-20 |
| Inscripción | RF-23, RF-24, RF-25 (corrección), RF-26, RF-27, RF-30, RF-75 | RF-21, RF-22, RF-29, RF-76 |
| Asistencia | RF-31 (negativo), RF-32, RF-33, RF-34 | RF-35, RF-36, RF-37 |
| Calificaciones | RF-38, RF-39 | RF-40, RF-41, RF-42, RF-43 |
| Certificación | RF-45 (permiso), RF-48 (unitario) | RF-44, RF-46, RF-47, RF-49, RF-50, RF-51 |
| Facilitadores | RF-55 (negativo), RF-56 | RF-52, RF-53, RF-54 |
| Satisfacción | RF-59 (incluida la negativa), RF-60 | RF-57, RF-58 |
| Estadísticas | — | RF-61 a RF-70 |
| Público | RF-71 (redirección 302) | RF-72, RF-73, RF-74 |

**Ejecución runtime (2026-10-23):** `php artisan test` → **145 passed / 0 failed (565 aserciones)** tras el lote de permisos. Las pruebas nuevas del lote se ejecutaron también contra la versión de HEAD de los archivos corregidos: 8 de 12 fallan, como se espera.

Los 37 fallos del 22/10 quedaron así: 18 por IDs de estado → resueltos por el seeder; 17 por pruebas obsoletas → corregidas (`CursoPolicyTest`, `InscripcionPolicyTest`, `ExampleTest` ahora espera 302); 2 de RBAC → resueltos con el grupo `permiso:` de `T/Routes/web.php:275-290`.

### 6.3 Rutas huérfanas y código muerto

| Ruta | Evidencia | Acción |
|---|---|---|
| `taller.cursos.plantilla.create` | `T/Routes/web.php:82-83`; sin referencia UI | Eliminar o documentar como alias |
| `taller.mis-cursos-asignados` | `T/Routes/web.php:31`; redirige a `mis-cursos` | Mantener como alias o eliminar |
| `home.registro` | `R/Routes/web.php:30`; sin referencia | Revisar |

| Qué | Evidencia | Acción |
|---|---|---|
| `check373.php` (raíz) | Eliminado en el Tier 1 | Cerrado |
| Partials `a/partials/curso-actions/*.blade.php` duplicados de `actions-container` | Sin inclusiones | Borrar o documentar fuente única |
| `a/Cursos.blade.php` incluye partial con `@extends`/`@section` | `Cursos.blade.php:7-12` | Quitar layout del partial |
| `inscribirAlCurso()` y `.inscribir-btn` apuntan a ruta que devuelve 400 | `CursoDetalle.blade.php`; `InscripcionController.php:71-74` | Eliminar camino muerto |
| `UsersController::disabled()` sin ruta | `UsersController.php:343` | Registrar ruta o eliminar |
| `R/Resources/views/index.blade.php` "Hello World" | Vista sin uso | Eliminar |
| `S/Resources/views/users/correo.blade.php` texto dummy | `:1-12` | Eliminar o reemplazar |
| `app/routes/web.php` `/elecciones` → ruta inexistente | `app/routes/web.php:28-31` | Eliminar |

### 6.4 Hallazgo runtime P0 · IDs de estados desfasados — **Resuelto (verificado 2026-10-23)**

**Causa (22/10):** la migración `2026_09_01_000001_add_borrador_estado_to_estados_curso.php` inserta `Borrador=10` y adelanta la secuencia; el seeder insertaba los otros nueve sin ID y recibían 11–19, mientras `App\Enums\EstadoCurso` espera 1–10.

**Corrección:** `EstadosCursoSeeder.php:15-30` hace `upsert` con IDs explícitos 1–9 y resincroniza la secuencia con `setval(… MAX(id_estado))`.

**Verificación:**

```text
CREATE DATABASE cineform_audit;  migrate:fresh --seed (DB_DATABASE=cineform_audit)
→ 1:Por_aceptar, 2:Rechazado, 3:Declinado, 4:Edicion, 5:Aprobacion,
  6:Inscripcion, 7:En_curso, 8:Finalizado, 9:Cerrado, 10:Borrador
php artisan test (DB_DATABASE=cineform_audit) → 120 passed (489 assertions)
DROP DATABASE cineform_audit;
```

**Pendiente menor:** la BD de desarrollo ya tenía 1–10, pero conviene revisar que ningún entorno desplegado conserve 10–19 de una instalación anterior; ahí el upsert insertaría 1–9 y quedarían duplicados por nombre con IDs 11–19 referenciados por cursos existentes. Hace falta una migración de datos que remapee `curso_estado.id_estado` antes de desplegar.

---

## 7. Backlog priorizado

### 7.1 P0 — Bloqueantes

| ID | Acción | Impacto | Fase |
|---|---|---|---|
| RNF-04 | Revocar la contraseña de aplicación de Gmail; rotar `APP_KEY`; vaciar `MAIL_PASSWORD` en `env.example`; mover `POSTGRES_PASSWORD` a `.env`; valorar purga del historial | Envío de correo suplantando a la institución; descifrado de IDs/cookies | 0 |
| ~~—~~ | ~~Commitear el working tree (tras Pint)~~ — **Resuelto:** commiteado en `753d192` | — | 0 |
| ~~E1 (RF-15)~~ | ~~Quitar el forzado a Rechazado en `updateStatus`~~ — **Resuelto 26/10** (§9.6) | — | 0 |
| ~~E2 (RF-12)~~ | ~~Definir `$idPersona` en `EditarCursoController::edit()`~~ — **Resuelto 26/10** (§9.6) | — | 0 |
| RNF-13 | Respaldo diario y restauración probada (RPO 24 h / RTO 4 h) | Pérdida irreversible de datos | 4 |

### 7.2 P1 — Núcleo del negocio

| ID | Acción | Impacto | Fase |
|---|---|---|---|
| E3 / E4 / E6 | Una sola tabla acción × estado consultada por UI y backend: `update()` con control de estado, Declinado → revisión coherente, estado del curso en asistencia, certificación y postulaciones | Cambios en cursos cerrados; botones que fallan | 1 |
| ~~E5 (RF-39)~~ | ~~Decidir si se califica en Finalizado y alinear UI y backend~~ — **Resuelto 26/10** (§9.7) | — | 1 |
| E7 / E8 | Resolver postulaciones pendientes al iniciar el curso; estado Cancelado | Postulaciones huérfanas; cursos sin salida | 1 |
| RF-81 | Separar las acciones agrupadas en `manage_course` y `esFacilitador()` (con el rediseño del ciclo; el alcance global ya es explícito, D7 §9.12) | Permisos agrupados | 1 |
| ~~RF-82~~ | ~~Delegación de perfiles configurable~~ — **Resuelto 26/10** (D8, §9.12) | — | 2 |
| RF-83 | Retirar `processes.actions`, unificar `SecurityAction`, procesos por dominio | Deriva entre fuentes del catálogo | 2 |
| RF-27 | UI de retiro por gestor (D6 resuelto 26/10, §9.10) | Retiro sin interfaz | 1 |
| RF-44 / RF-45 | Aplicar nota mínima, encuesta y estado Finalizado antes de certificar (asistencia al 100 % ya aplicada, D3 §9.11) | Certificación indebida | 1 |
| RNF-02 / RF-05 | Vaciar `PENDIENTES_DE_MIGRAR`; RBAC en `security.admin`; `profiles.disabled` solo POST | Escalada de privilegios | 1 |
| RNF-03 | Mover documentos a disco privado y borrar archivos solo tras `commit` | Exposición de documentos personales; pérdida de archivos | 1 |
| RF-07 / RF-43 / RF-70 / RF-20 | Auditoría central de estados (con autor), notas y certificados | Sin trazabilidad | 1 |
| RF-29 / RF-51 / RNF-14 | Notificar cambios de estado y usar colas con worker | Avisos perdidos | 1 |
| RF-16 | Motivo obligatorio y auditoría con autor en la reasignación; rollback seguro de la migración nullable | Estados sin trazabilidad | 1 |
| RF-25 / RF-26 | Rehabilitación a `postulado`; cancelación con borrado lógico | Pérdida de evidencia | 1 |
| ~~RF-56~~ | ~~Preservar documentos al rechazar facilitador~~ — **Resuelto 2026-10-23** (prueba `PostulacionFacilitadorRechazoTest`) | ~~Pérdida de evidencia~~ | 1 |
| RF-31 / RF-35 | Prueba del QR válido; consolidado solo aprobados | Asistencia falsa | 1 |
| RF-40 | Exigir que ponderaciones sumen 100 | Promedios incorrectos | 1 |
| RF-06 / RF-68 | Alta de usuario con `PersonalData`; `password()` contra el usuario objetivo; activar/desactivar | Usuarios inconsistentes | 1 |
| RF-02 | Throttle separado por flujo y auditoría de intentos | Abuso de login | 1 |
| RF-65 | Alcance de estadísticas por rol | Exceso de visibilidad | 1 |
| RF-72 | Corregir layout/CDN y formato legado | Enumeración y UI rota | 1 |
| RF-21 / RF-22 | Filtros y requisitos en catálogo/detalle | Conversión | 3 |
| UX-27 | Registro por pasos, sin CDN, con rehidratación | Abandono en registro | 2 |
| UX-26 / RNF-15 | Accesibilidad básica | Exclusión | 2 |
| RNF-10 | Paginar endpoints >100 registros | Timeouts | 1 |
| RNF-19 | Pint sobre archivos tocados antes de cada commit | CI en rojo | 1 |
| RNF-21 | Medir cobertura (PCOV) y añadir un E2E del flujo P1–P5 | Falsa confianza | 4 |

### 7.3 P2 — Experiencia y alcance

| ID | Acción | Fase |
|---|---|---|
| RNF-01 | Historial de contraseñas (no reutilizar las últimas N) | 2 |
| UX-25 | Sistema de diseño con tokens | 2 |
| UX-29 | Activar/desactivar usuarios con confirmación | 2 |
| UX-30 | Captcha sin autorefresco y `autocomplete` correcto | 2 |
| UX-34 | Eliminar doble aviso de validación | 2 |
| UX-35 | Ojo de contraseña accesible | 2 |
| ~~RF-17~~ | ~~Duplicar curso o retirar del alcance~~ — **Retirado del alcance** (D5) | 3 |
| RF-18 | Archivar/eliminar borradores individualmente (por POST) | 1 |
| RF-19 | Vista previa real antes de publicar | 3 |
| ~~RF-28~~ | ~~Lista de espera~~ — **Retirado del alcance** (D5) | 4 |
| RF-42 / RF-64 | Exportaciones CSV/PDF/acta (RF-60 resuelto en D2, §9.9) | 4 |
| RF-49 / RF-50 | Revocación/reemisión y emisión masiva | 4 |
| RF-69 | Notificaciones in-app | 4 |
| RNF-11 / RNF-12 | Índices y disponibilidad | 1 |
| RNF-17 | Formato es-VE | 2 |
| RNF-05 | TLS y cabeceras de seguridad | 4 |
| RNF-20 / RNF-22 / RNF-23 | PHPStan, observabilidad, datos personales | 4 |

### 7.4 P3 — Backlog

| ID | Acción |
|---|---|
| RF-74 | Canal de contacto/soporte |
| RNF-24 | Completar i18n |
| UX-31 | Unificar `route()` |
| — | Eliminar código muerto de §6.3 |

---

## 8. Decisiones abiertas

### D1 · `mis-cursos-asignados` (UX-18) — **Respondida**

Opción B: unificar con `mis-cursos`, manteniendo la diferencia de datos. Implementado en `CursoInscritoController.php:25-56` y cubierto por `HuecosFlujoTest`.

### D2 · Quién ve los resultados de satisfacción (UX-16, RF-59, RF-65) — **Respondida (26/10)**

Opción (c), ambos: el facilitador asignado solo consulta; el coordinador consulta y genera el reporte. El reporte depende del permiso `export_satisfaction`, no del perfil (§9.9). RF-65 (alcance de las estadísticas generales) no forma parte de esta decisión y sigue abierto.

### D3 · Umbral único de asistencia para certificar (UX-15, RF-37, RF-44) — **Respondida 26/10**

Opción (c): asistencia perfecta (100 % de las clases). El facilitador puede aprobar igualmente si justifica **cada falta** con un motivo; la falta justificada cuenta como cumplida. Los colores de asistencia pasan a verde solo con el 100 % (ámbar desde el 75 %). El 80 de promedio académico no forma parte de esta decisión (nota mínima, RF-44). Implementado en §9.11.

### D4 · Alcance del sistema de diseño (UX-25, RNF-15/16/17) — **Respondida (26/10)**

Opción (a), todas las pantallas, con respaldo previo. Dirección: unificar la paleta actual con un acabado más profesional y uniforme; forma amable y redondeada (botones en píldora, tarjetas de 16px); fondo de cine solo en entrada y estados vacíos. Orden: primero la base y luego un lote por módulo, cada uno aprobado por el usuario. Lote 1 en §9.13.

### D5 · Alcance de RF-17 y RF-28 — **Respondida (26/10)**

Se retiran del alcance: no se implementan duplicar curso ni lista de espera. Las filas quedan como ⛔ y salen de los totales (§9.8). No había código de ninguna de las dos que eliminar.

### D6 · Estado de una inscripción retirada por el gestor (RF-27) — **Respondida (26/10)**

Rechazar para corrección y rechazar (denegar) son acciones distintas y se mantienen las dos. La denegación y el retiro impiden que el participante vuelva a postularse, pero **son revertibles** por quien gestiona: la denegación vuelve a revisión (`postulado`) y el retiro, que pasa a tener estado propio `retirado` (opción (a)), vuelve a inscrito (`aprobado`) si hay cupo. Implementado en §9.10.

### D7 · Cómo modelar el alcance global de las acciones (RF-81) — **Respondida 26/10**

Opción (b): el Coordinador supervisa todos los cursos, así que un único permiso "Supervisar todos los cursos" (`supervise_courses`) extiende a cualquier curso las acciones que el usuario ya tiene. Por sí solo no concede ninguna acción. Implementado en §9.12.

### D8 · Delegación de perfiles (RF-82) — **Respondida 26/10**

Quien tiene el permiso de asignar perfiles solo asigna (y quita) los perfiles que él mismo posee: regla de pertenencia, no de subconjunto de permisos ni lista configurable. Implementado en §9.12.

---

## 9. Changelog de la auditoría

### 9.1 Cambio de totales

| Dimensión | 21/10 | 22/10 (manual, publicado) | 22/10 (recuento real de sus tablas) | 23/10 (tras lote de permisos) | 26/10 (E1, E2, E5, D5, D2, D6, D3, D7 y D8) |
|---|---|---|---|---|---|
| RF | 48 / 14 / 14 | 30 / 28 / 18 | 35 / 25 / 16 | 40 / 25 / 18 (83 RF) | **44 / 27 / 10** (81 RF en alcance + 2 ⛔) |
| RNF | 4 / 9 / 9 / 1 | 1 / 7 / 15 / 2 | 5 / 8 / 10 / 2 | 2 / 10 / 11 / 2 | **2 / 10 / 11 / 2** |
| UX | 22 cerrados / 6 abiertos | 22 / 10 | 24 / 8 | 24 / 8 | **24 / 8** |

> La versión manual cambió filas sin recalcular §2, y su encabezado (116/462) contradecía §1.6, §2.3, §2.5, §6.2 y §10 (70/37, Pint 160, IDs 10–19).

### 9.2 Afirmaciones de la versión manual: confirmadas y refutadas

| ID | Afirmación 22/10 | Veredicto 23/10 | Evidencia |
|---|---|---|---|
| Encabezado | "116 passed / 462 aserciones" | **Desactualizada** | `R`: 120 / 488 |
| Encabezado | "Pint ejecutado y limpio en archivos modificados" | **Refutada** | `R`: 6 issues en 35 archivos |
| RF-15 / §6.4 | Seeder con IDs canónicos; tests verdes | **Confirmada** | `R`: instalación limpia 1–10; 120/120 |
| RF-26 | Resuelto | **Confirmada** con brecha | Borrado físico, archivos fuera de transacción |
| RF-30 | Resuelto | **Confirmada** | `InscripcionFlujoTest` (`R`) |
| RF-38 | Resuelto | **Confirmada** | `HuecosFlujoTest` (`R`) |
| RF-39 | Resuelto (✅) | **Rebajada a ⚠️** | Notas editables con curso cerrado |
| RF-05 | "CRUD de usuarios exige solo VER" | **Refutada** | `UsersController.php:18-33` |
| RF-16 | "Ruta dedicada sin llamador UI" | **Refutada** (por cambios nuevos) | `CursoDetalle.blade.php:908`; 4 pruebas |
| RF-31 | "`duracion_minutos` no se valida; `desactivarToken()` no verifica curso" | **Refutada** | `AsistenciaController.php:102,195` |
| RF-34 | "Posible IDOR" (contradecía UX-32 cerrado) | **IDOR cerrado**; queda motivo opcional | `:279,305,286` |
| RF-55 / RNF-02 | Panel admin de facilitadores accesible (200) | **Resuelta** | Grupo `permiso:`; prueba en verde |
| RNF-01 | Resuelto | **Confirmada** | `AppServiceProvider.php:28-30`; 6 usos |
| RNF-03 | Resuelto (✅) | **Rebajada a ⚠️** | Disco `public`; borrado previo al `commit` |
| RNF-04 | "`env.example` saneado, eliminando credenciales de Gmail" | **Refutada → ❌** | `env.example:74` conserva `MAIL_PASSWORD`; `APP_KEY` en historial |
| RNF-19 | ⚠️ "Pint limpio en archivos intervenidos" | **Refutada → ❌** | 149 issues globales; 6 en archivos tocados |
| RNF-21 | ✅ "Suite al 100 %" | **Rebajada a ⚠️** | La meta es cobertura ≥ 70 % y E2E; no medidas |
| UX-32 / UX-33 | "Cerrado" pero contados como abiertos | **Movidos a cerrados** | Pruebas en verde |

### 9.3 Hallazgos nuevos de esta versión

| ID | Hallazgo | Prioridad |
|---|---|---|
| RF-01 | Código de registro sin caducidad ni límite de intentos; rutas de registro sin throttle | P1 (fase 0) |
| RF-27 | Retiro por gestor → `rechazado` → el participante puede volver a postularse | P1 |
| RF-39 | Notas modificables en Finalizado/Cerrado | P1 |
| RF-16 | No verifica perfil Facilitador del destino; `down()` de migración nullable no es seguro | P1 |
| RNF-04 | `POSTGRES_PASSWORD` en `docker-compose.yml` | P0 (junto a RNF-04) |
| §6.3 | `check373.php` de depuración en la raíz | P0 (antes de commitear) |
| §6.4 | Entornos con IDs 11–19 previos necesitan migración de datos, no solo el seeder | P1 |

### 9.4 Cambios de estado tras el Tier 1 (23/10)

Totales RF: 35 / 25 / 16 → **39 / 21 / 16**. Los cambios de código del Tier 1 no están commiteados.

| ID | Antes | Ahora | Motivo |
|---|---|---|---|
| RF-24 | ⚠️ | ✅ | `rechazar()` bloquea inscripciones aprobadas; prueba en `IntegridadFlujosTest` |
| RF-33 | ⚠️ | ✅ | `marcarManual()` exige inscripción aprobada; prueba en `IntegridadFlujosTest` |
| RF-34 | ⚠️ | ✅ | `anular()` exige motivo en backend; prueba en `IntegridadFlujosTest` |
| RF-39 | ⚠️ | ✅ | Edición de notas bloqueada con curso Finalizado o Cerrado; prueba en `IntegridadFlujosTest` |
| RF-56 | ⚠️ | ✅ | Documentos conservados al rechazar postulación; prueba en `PostulacionFacilitadorRechazoTest` |
| RF-27 | ⚠️ | ⚠️ | Estado `denegado` y motivo obligatorio; falta la UI de retiro por gestor (D6) |
| RF-08 | ✅ | ✅ | Logout por POST con invalidación de sesión; prueba en `LogoutSeguridadTest` |
| RF-01 | ⚠️ | ⚠️ | Caducidad y throttle; falta la prueba del flujo completo |
| RF-02 | ⚠️ | ⚠️ | Refresco automático del captcha eliminado; falta throttle separado |
| RF-16 | ⚠️ | ⚠️ | Destino validado como facilitador; faltan motivo y auditoría |
| RF-31 | ⚠️ | ⚠️ | QR solo para tokens válidos; falta la prueba del caso válido |
| UX-30 | Abierto | Abierto | `setInterval` eliminado; falta `autocomplete` correcto |

### 9.5 Lote de permisos y perfiles, y revisión del ciclo de vida (23/10)

Totales RF: 39 / 21 / 16 (76) → **40 / 25 / 18** (83). RNF: 2 / 9 / 12 / 2 → **2 / 10 / 11 / 2**. Nada commiteado.

**Correcciones de permisos y perfiles (P1–P6):**

| Hallazgo | Corrección | Prueba |
|---|---|---|
| P1 · escalada al editar permisos | Solo se otorga lo que tiene el perfil activo; permisos reservados al raíz; lo no controlado se conserva; pantalla con casillas bloqueadas | 5 pruebas, fallan contra HEAD |
| P2 · ver catálogos permitía modificarlos | Rutas separadas por VER / CREAR / EDITAR; migración que conserva el acceso actual | 2 pruebas |
| P3 · guardado de permisos no atómico | Diferencia (añadir/quitar) dentro de una transacción | Cubierto por P1 |
| P4 · sin auditoría | Tabla `security.auditoria` inmutable + servicio; permisos, activación y asignación de perfiles | 2 pruebas |
| P5 · raíz con resultado distinto según el camino | Helpers con el mismo bypass que el middleware; comprobaciones de raíz centralizadas en `esRaiz()` | 1 prueba |
| P6 · asignar perfiles borraba los que el actor no puede otorgar | Diferencia que conserva los perfiles no controlados | 2 pruebas, fallan contra HEAD |
| Elegibilidad de facilitador decidida de 3 formas | Una sola consulta en `Autorizacion` | 1 prueba + ajuste en `IntegridadFlujosTest` |
| Números mágicos y textos de permisos | Constantes en rutas de Security; etiquetas sin "Rol …"; descripción de `APROBAR_CURSO` corregida | — |

**Errores del ciclo de vida del curso (revisión del 23/10):**

| ID | Error | Gravedad | Fila |
|---|---|---|---|
| E1 | Aceptar asignación deja el curso Rechazado y sin facilitador (reproducido; working tree) — **Corregido 26/10** | Crítica | RF-15 |
| E2 | El editor de cursos devuelve 500 por `$idPersona` indefinida (reproducido; también en HEAD) — **Corregido 26/10** | Crítica | RF-12 |
| E3 | `update()` no comprueba el estado del curso (reproducido en Finalizado) | Alta | RF-12 |
| E4 | "Enviar a revisión" en Declinado, transición prohibida por el enum | Alta | RF-15 |
| E5 | Bloqueo de notas en Finalizado contradice la UI (introducido en Tier 1) — **Resuelto 26/10** | Alta | RF-39 |
| E6 | Asistencia, certificación y postulaciones sin control de estado del curso en backend | Alta | RF-31–RF-45 |
| E7 | Postulaciones pendientes quedan huérfanas al iniciar el curso | Media | RF-24 |
| E8 | Sin cancelación de curso desde Inscripción | Media | RF-15 |

El diseño objetivo del ciclo (13 estados, catálogo de 33 acciones con alcance Asignado / Titular / Global, reasignación como acción propia) se discutió en conversación y no forma parte de este documento todavía.

### 9.6 Corrección de E1 y E2 (26/10)

Totales RF: 40 / 25 / 18 → **40 / 27 / 16** (83). RF-12 y RF-15 pasan de ❌ a ⚠️: queda E3 en RF-12, y E4 y E8 en RF-15.

| Error | Corrección | Prueba |
|---|---|---|
| E1 | Se quitó el bloque de `CursoController::updateStatus` que convertía en Rechazado cualquier destino desde Por aceptar. La transición la valida el enum (`puedeTransicionarA`) y la Policy, como en el resto de estados | `AceptarAsignacionYEditorTest`: aceptar deja el curso En Edición con su facilitador; un destino no permitido no cambia el estado |
| E2 | `EditarCursoController::edit()` define `$idPersona` desde `personalData`, con el mismo patrón que `ResuelveRolesTaller::personaId()` | `AceptarAsignacionYEditorTest`: el editor carga (200) para el facilitador asignado y para el coordinador |

Las 4 pruebas nuevas fallan contra `753d192` y pasan con la corrección. Suite: **155 passed / 0 failed (587 aserciones)**. Pint: la prueba nueva pasa; el único issue de los dos controladores (`function_declaration`, `EditarCursoController.php:62`) es heredado.

### 9.7 E5: calificar en Finalizado (26/10)

**Decisión del usuario:** se califica y se corrigen notas en Finalizado; solo en Cerrado no se pueden modificar. Totales RF: 40 / 27 / 16 → **41 / 26 / 16**. RF-39 pasa a ✅.

| Cambio | Archivo | Prueba |
|---|---|---|
| El guardado solo se bloquea en Cerrado (403, "el curso ya está Cerrado") | `CalificacionController::store()` y `notasCerradas()` | `CalificacionPorEstadoTest`: califica y corrige en Finalizado; no modifica en Cerrado |
| Planilla en Cerrado de solo lectura: aviso, campos deshabilitados y sin botón de guardar | `CalificacionController::index()`, `CursoCalificar.blade.php` | `CalificacionPorEstadoTest`: planilla editable en Finalizado y de solo lectura en Cerrado |
| Botón de la evaluación: "Calificar…" hasta Finalizado, "Ver calificaciones de…" en Cerrado | `CursoContenido.blade.php` | `CalificacionPorEstadoTest::test_el_boton_de_la_evaluacion_cambia_segun_el_estado` |
| La prueba anterior exigía el bloqueo en Finalizado; ahora prueba Cerrado | `IntegridadFlujosTest::test_no_se_modifican_calificaciones_con_curso_cerrado` | — |

Contra `753d192`, 3 de las pruebas nuevas fallan. La de "planilla editable en Finalizado" pasa también allí, porque la planilla ya se mostraba editable; lo que fallaba era el guardado. Suite: **160 passed / 0 failed (606 aserciones)**. Pint limpio en los archivos PHP tocados.

### 9.8 D5: RF-17 y RF-28 fuera del alcance (26/10)

**Decisión del usuario:** se retiran duplicar curso (RF-17) y lista de espera (RF-28). Se añadió el estado ⛔ (§1.1): la fila se conserva para no reutilizar el ID y no suma en los totales. Totales RF: 41 / 26 / 16 sobre 83 → **41 / 26 / 14 sobre 81** en alcance, más 2 ⛔.

Antes de retirarlos se buscó código relacionado (rutas, controladores, modelos, migraciones, vistas y textos como "duplicar" o "lista de espera"): no hay nada, así que el cambio es solo del documento. Sin cambios de código ni de pruebas.

### 9.9 D2: consulta y reporte de satisfacción (26/10)

**Decisión del usuario:** ambos ven los resultados; el facilitador solo consulta y el coordinador consulta y genera el reporte. Totales RF: 41 / 26 / 14 → **42 / 26 / 13** sobre 81. RF-60 pasa de ❌ a ✅.

La consulta ya cumplía la decisión (`CursoPolicy::verSatisfaccion`: gestor o facilitador asignado). Lo nuevo es el reporte, controlado por permiso y no por perfil, en línea con D7:

| Cambio | Archivo |
|---|---|
| Acción `REPORTE_SATISFACCION` (27, slug `export_satisfaction`, "Generar reporte de satisfacción") con descripción en el catálogo único | `SecurityAction.php`, `database/data/permisos_descripciones.php` |
| Migración que crea el permiso en el proceso de cursos y lo otorga a los perfiles con `manage_course` (en la BD de desarrollo: Administrador y Coordinador) | `2026_10_26_000001_permiso_reporte_satisfaccion.php` |
| Seeders de instalación limpia con el permiso para perfiles 1 y 4 | `PermissionsSeeder.php`, `ProfilePermissionsSeeder.php` |
| Policy `reporteSatisfaccion` = puede consultar el curso **y** tiene el permiso | `CursoPolicy.php` |
| Ruta `taller.cursos.satisfaccion.reporte` que descarga un Excel con curso, total, índice general y, por pregunta, promedio, favorables, % y distribución 1–5. Usa las mismas estadísticas que la pantalla. Sin respuestas, vuelve a resultados con aviso | `SatisfaccionCursoController::reporte()`, `Modules/Taller/Exports/ReporteSatisfaccionExport.php`, `T/Routes/web.php` |
| Botón "Descargar reporte (Excel)" en resultados, solo con el permiso y con respuestas | `CursoSatisfaccionResultados.blade.php` |

**Pruebas** (`ReporteSatisfaccionTest`, 6): el coordinador descarga el reporte con los mismos valores de la pantalla y ve el botón; el facilitador asignado consulta pero no ve el botón y recibe 403 en el reporte; un facilitador de otro curso recibe 403 en ambos (la prueba negativa que faltaba en RF-59); sin el permiso, el coordinador consulta pero no genera el reporte; sin respuestas no hay descarga. Suite: **166 passed / 0 failed (623 aserciones)**. Pint: los archivos nuevos pasan; quedan dos issues heredados (`class_attributes_separation` en `SecurityAction.php`, `not_operator_with_successor_space` en `SatisfaccionCursoController.php`), presentes antes del cambio.

**Instalación limpia** (`migrate:fresh --seed` en BD temporal): el permiso queda creado, con descripción y asignado a los perfiles 1 y 4. La suite sobre esa BD da 165 / 1: falla `IntegridadFlujosTest::test_no_se_reasigna_a_persona_no_elegible_como_facilitador`. **Hallazgo nuevo, previo a este lote** (falla igual sin los cambios de seeders): la prueba elige como "participante" al primer usuario con ese perfil, que en una instalación limpia es el usuario 2, también Coordinador; en la BD de desarrollo es otro. Es un problema de fixture, no del código de reasignación. Pendiente: fijar el participante de la prueba a un usuario que solo tenga el perfil Participante.

### 9.10 D6: rechazo definitivo y revertible (26/10)

**Decisión del usuario:** "rechazar para corrección" (el participante corrige y reenvía) y "rechazar" (definitivo, en el código `denegar`) son distintos y se mantienen. Lo definitivo impide volver a postularse, pero debe poder revertirse; al revertir, vuelve a revisión. Totales sin cambios: RF-24 sigue ✅ y RF-27 sigue ⚠️ por la falta de UI para retirar.

| Estado final | Quién lo revierte | Queda en | Archivo |
|---|---|---|---|
| `denegado` (rechazo en revisión) | Quien gestiona postulaciones del curso (`InscripcionPolicy::gestionar`: coordinador o facilitador asignado) | `postulado`, para revisarla de nuevo | `InscripcionController::revertir()` |
| `retirado` (**nuevo**, retiro del gestor durante el curso) | Quien puede retirar (`InscripcionPolicy::rehabilitar`) | `aprobado`, si hay cupo (409 si no) | `InscripcionController::destroy()` y `revertir()` |

Reglas añadidas:
- Revertir exige motivo, solo procede con el curso en Inscripción o En Curso y queda en `security.auditoria` (`inscripcion.revertir`, con el estado y el motivo anteriores) dentro de la misma transacción.
- `rechazar()` ya no acepta una inscripción denegada o retirada (pasarla a `rechazado` le permitiría volver a postularse), y `denegar()` no acepta una retirada; ambas responden 400 indicando que se use revertir.
- El participante retirado ve "Retirado del Curso" y no puede volver a postularse.
- `Inscripcion::ESTADO_RETIRADO`, `esRetirado()`, `esRevertible()`; `esRechazada()` incluye `retirado`. La columna `estado` es `varchar(20)` sin restricción, así que no hace falta migración. No hay inscripciones antiguas que reclasificar: los retiros previos quedaron como `denegado` y se revierten igual, a `postulado`.
- UI: pestaña "Retirados" y botón "Revertir" en denegados y retirados, visibles solo para quien puede revertir; los diálogos de denegar ya no dicen "irreversible".
- Ruta `taller.inscripciones.revertir`, autorizada en el controlador y catalogada en `RutasProtegidasTest::AUTORIZADAS_EN_CONTROLADOR`.
- Vocabulario de la UI, pedido por el usuario: el rechazo para corrección se llama **"Subsanar"** (botón, diálogo "¿Enviar a subsanar?", pestaña "Por subsanar", mensaje "Postulación enviada a subsanar.") y el definitivo, **"Rechazar definitivamente"** (pestaña "Rechazados definitivamente"). En el código siguen siendo `rechazar` / `rechazado` y `denegar` / `denegado`. Del lado del participante se mantienen "Postulación con Observaciones" y "Corregir Postulación".
- Revisado en Edge con el administrador sobre el curso de prueba 1261 (QA-D6-2692), que ahora tiene requisitos de ejemplo (2 documentos PDF y 1 pregunta) en sus 4 postulaciones: "Ver Requisitos" abre la página de respuestas con "Ver Documento" y "Descargar"; el PDF se sirve como `application/pdf` (200).
- **Fallo previo corregido en la revisión:** "Ver Documento" descargaba el archivo en vez de mostrar la vista previa, porque `InscripcionController::verDocumento()` usaba `Storage::download()` (`Content-Disposition: attachment`) y el visor lo carga en un iframe. Ahora usa `Storage::response()` (`inline`); "Descargar" sigue forzando la descarga. `VerDocumentoRequisitoTest` (2 pruebas; la de vista previa falla sin el cambio).
- `InscripcionPolicyTest` reutilizaba `Curso::first()` (sin orden) de la BD y fallaba con violación de unicidad cuando ese curso ya tenía inscrito al titular (pasó con el curso 1261). Ahora crea su propio curso. Suite: **179 passed / 0 failed (669 aserciones)**.

**Pruebas** (`RevertirInscripcionTest`, 11): revertir una denegación (a `postulado`, auditada); retiro del gestor a `retirado` y revertirlo (a `aprobado`); el facilitador no revierte un retiro; el participante no revierte su denegación; sin cupo, 409; sin motivo, 422; solo se revierten denegaciones y retiros; con el curso Finalizado no se revierte; rechazar para corrección no reabre una denegación ni un retiro; el retirado no puede volver a postularse; la lista muestra retirados con el botón. Se ajustó `IntegridadFlujosTest` (el retiro queda `retirado`). Suite: **177 passed / 0 failed (664 aserciones)**. Pint: la prueba nueva, `InscripcionController.php` y `CursoDetalleController.php` pasan; los issues de `Inscripcion.php` y `RutasProtegidasTest.php` ya existían antes del cambio.

**Fuera de este lote:** la UI para retirar (RF-27) y que `rehabilitar()` de una postulación rechazada pase por revisión (RF-25).

### 9.11 D3: asistencia al 100 % con faltas justificadas (26/10)

**Decisión del usuario:** la asistencia debe ser perfecta; el facilitador puede aprobar igualmente si justifica cada falta. Totales RF: 42 / 26 / 13 → **43 / 26 / 12**. RF-37 pasa de ❌ a ✅; RF-44 sigue ⚠️ (falta nota mínima y encuesta).

- **Regla** (`T/Services/CumplimientoAsistencia.php`): cuentan las clases del curso (contenidos con `es_evaluacion = false`). Cumple quien tiene, en cada clase, asistencia activa o una falta justificada. Porcentaje = (clases − faltas sin justificar) / clases. Un curso sin clases cumple (no hay asistencia que exigir).
- **Datos:** tabla nueva `taller.asistencia_justificaciones` (una fila por clase e inscripción, con `motivo` y `justificada_por`), migración `2026_10_26_000002`, ya aplicada en la BD de desarrollo.
- **Acciones** (`AsistenciaController::justificarFalta()` y `quitarJustificacion()`, rutas `taller.asistencia.justificar` y `taller.asistencia.quitar-justificacion`): misma autorización que el resto de la asistencia (`CursoPolicy::gestionarAsistencia`), catalogadas en `RutasProtegidasTest::AUTORIZADAS_EN_CONTROLADOR`. Exigen motivo, inscripción aprobada y una clase del curso; no se justifica una clase asistida (422) ni con el curso Cerrado (403, igual que E5); no se quita una justificación con la certificación ya aprobada (422). Ambas quedan en `security.auditoria` (`asistencia.justificar`, `asistencia.quitar_justificacion`).
- **Certificación:** `CertificacionPanelController::aprobar()` rechaza con "…tiene N falta(s) sin justificar. Se exige el 100 % de asistencia; justifique cada falta para aprobar."
- **UI** (`CertificacionPanel.blade.php`): la columna Asistencia muestra asistidas, justificadas y "N faltas sin justificar"; el detalle marca cada clase como Asistió / Justificada (motivo en el título, botón para quitar) / Falta (botón Justificar con modal y motivo obligatorio); Aprobar queda deshabilitado sin el 100 %.
- **UX-15:** los helpers de color de asistencia pasan a verde solo con el 100 % y ámbar desde el 75 % (antes 75 / 50); se actualizó `HuecosFlujoTest::test_helpers_umbral_asistencia_unificado`. Afecta también a las vistas de asistencia consolidada e individual, que siguen mostrando el porcentaje sin justificadas.

**Pruebas** (`CumplimientoAsistenciaTest`, 9): no se aprueba con una falta sin justificar; con la falta justificada se aprueba y queda auditada; motivo obligatorio; no se justifica una clase asistida; quitar la justificación vuelve a bloquear; no se quita con la certificación aprobada; con el curso Cerrado no se justifica; el participante no justifica; el panel muestra la falta y luego la justificada. Suite: **188 passed / 0 failed (711 aserciones)**. Pint: los archivos nuevos pasan; los issues de `AsistenciaController.php`, `CertificacionPanelController.php` y `Helpers.php` ya existían. Revisado en Edge sobre el curso 1261: la falta se justifica desde el modal, el participante pasa al 100 % y Aprobar se habilita.

**Fuera de este lote (D3):** `CertificadoController::descargar()` no recalcula la asistencia (confía en la aprobación, así que las certificaciones aprobadas antes de D3 siguen valiendo); la vista individual de asistencia no muestra las justificadas; nota mínima y encuesta (RF-44) y estado Finalizado para aprobar (RF-45).

### 9.12 D7 y D8: supervisión explícita y delegación por pertenencia (26/10)

**Decisiones del usuario:** D7, el coordinador supervisa todos los cursos (opción (b): un permiso de supervisión); D8, quien asigna perfiles solo asigna los que posee. Respaldo previo: tag `respaldo/2026-10-26-antes-d7-d8`. Totales RF: 43 / 26 / 12 → **44 / 27 / 10**. RF-81 pasa de ❌ a ⚠️ y RF-82 de ❌ a ✅.

**D7, alcance global sin rol escondido:**
- Permiso nuevo `supervise_courses` ("Supervisar todos los cursos", `SecurityAction::SUPERVISAR_CURSOS = 28`): extiende a cualquier curso las acciones que el usuario ya tiene; por sí solo no concede ninguna. Migración `2026_10_26_000003` (aplicada en desarrollo): lo otorga a los perfiles que tenían `manage_course` (1 y 4), renombra `manage_course` a "Gestionar cursos" con descripción nueva y actualiza la de `assign`. Seeders actualizados.
- `ResuelveRolesTaller`: `supervisaCursos()`, `alcanzaCurso()` (supervisa o es el facilitador) y `esGestor()` = `GESTIONAR_CURSO` y `SUPERVISAR_CURSOS`.
- `manage_course` ya no es comodín: `verParticipantes`, `gestionarAsistencia` y `certificar` exigen su permiso propio (`view_participants`, `manage_attendance`) con alcance; `rehabilitar` y el retiro por gestor exigen `cancel_enrollment` con supervisión. Ver contenido y resultados de satisfacción de cualquier curso dependen de `supervise_courses`. Los perfiles 1 y 4 ya tenían esos permisos propios, así que nadie pierde acceso (verificado en desarrollo y en instalación limpia).
- Controladores: los 11 usos de `hasPermissionRoute(..., GESTIONAR_CURSO)` como "ve o gestiona todos los cursos" pasan a `supervisaTodosLosCursos()` (listado, filtro de estados, acceso al curso, vista previa del certificado) o `gestionaTodosLosCursos()` (vista de coordinador, reasignar, plantilla y coordenadas institucionales del certificado).

**D8, delegación por pertenencia** (`PersonaPerfilController`):
- `perfilesAsignables()`: perfiles activos que el actor tiene con asignación activa; el raíz, todos.
- `assignProfiles()`: pedir un perfil que el actor no posee responde 403 ("Solo puede asignar perfiles que usted posee. No puede asignar: …"); los perfiles del usuario que el actor no controla se conservan aunque no vengan en la petición.
- Pantalla: los perfiles no asignables aparecen deshabilitados con "No puede asignarlo ni quitarlo: usted no tiene este perfil."

**Consecuencia a decidir:** en una instalación limpia el coordinador sembrado (usuario 2) tiene Participante y Coordinador, pero no Facilitador, así que no puede asignar Facilitador. En la BD de desarrollo sí lo tiene. Si el coordinador debe poder hacer facilitadores, hay que darle ese perfil (y entonces también sería elegible como facilitador, RF-80).

**Pruebas:** `SupervisarCursosTest` (5): el coordinador conserva el alcance; sin `supervise_courses` no alcanza cursos ajenos aunque tenga `manage_course`; supervisar no concede acciones que no tiene; `manage_course` ya no basta para ver participantes; sin `manage_course` supervisa pero no gestiona. `DelegacionPerfilesTest` (5): no asigna un perfil que no posee; asigna los que posee; conserva el perfil ajeno; el raíz asigna cualquiera; la pantalla los bloquea. Se actualizaron `ProfilesSecurityTest` y `PermisosPerfilesSeguridadTest` (afirmaban la regla anterior de "cualquier perfil") y `PostulacionFacilitadorSeguridadTest` (no pasaba el `session_token` y fallaba en cuanto el usuario elegido tenía sesión abierta). Suite: **198 passed / 0 failed (752 aserciones)**; instalación limpia 197 / 1 (la falla conocida de §9.9). Pint: los archivos nuevos pasan; los 6 issues restantes ya estaban en HEAD.

**Fuera de este lote:** separar las acciones agrupadas en `manage_course` y en `esFacilitador()` / `edit_course` (RF-81, con el rediseño del ciclo de vida). `calificar` sigue siendo exclusivo del facilitador asignado: la supervisión no lo extiende. La pantalla de asignación vista por un coordinador quedó cubierta por prueba, no en el navegador (el inicio de sesión tiene captcha).

### 9.13 D4, lote 1: base del sistema de diseño (26/10)

**Decisión del usuario:** D4 opción (a), con respaldo previo (tag `respaldo/2026-10-26-antes-d4`). Este lote solo toca la base; las pantallas se ajustan en lotes por módulo.

- **`DESIGN.md`** (raíz) y **`.impeccable/design.json`**: documentan la paleta conservada (navy #1e3a8a, azul de acción #1572e8, cian #00c0ef como acento, ámbar), neutros slate, tipografía, radios, sombras y reglas.
- **`app/public/css/main.css`** reorganizado: tokens `--cf-*`; fondo de la aplicación #f1f5f9 (el fondo de cine queda en `body.login` y en `.cf-entrada`, añadida a la selección de perfil); tarjetas y modales de 16px; botones en píldora; campos de 10px.
- **Contraste:** el encabezado de tarjeta forzado a #00c0ef con texto blanco (2,15:1) pasa a blanco con texto tinta; los que piden `text-white` o `bg-gradient-primary` reciben banda navy. Los colores de estado de KaiAdmin con texto blanco (#31ce36 2,1:1, #ffad46 1,86:1, #f25961 3,29:1) se sustituyen por #198754, ámbar con texto tinta y #dc2626; `btn-info`/`bg-info` usan #0e7490.
- **Foco visible:** contorno azul de 2px en controles y anillo en campos; se elimina el fondo amarillo #ffff99 al enfocar. Se añade `prefers-reduced-motion`.
- **Utilidades únicas:** `shadow-card`, `shadow-xs`, `bg-*-soft`, `badge-soft-*`, `border-light-2`, `btn-circle`, `transition-hover`, `hover-shadow`, `hvr-push`, `custom-scrollbar`, `uppercase` y `cursor-pointer` se definen solo en `main.css`; se quitaron 78 definiciones duplicadas (con valores distintos) de 18 archivos: 17 vistas y el parcial `Security/partials/style.blade.php`.
- **Caché:** `template/head.blade.php` añade `?v=<filemtime>` a `main.css`, porque el navegador seguía sirviendo la hoja anterior.

**Verificación:** revisado en Edge como administrador (ficha y participantes del curso 1261, listado de cursos, panel de certificación, usuarios, perfiles, asignar perfil, paso 1 del asistente, página 404), sin errores de consola. El inicio de sesión no se revisó en el navegador (exige captcha). Detector de impeccable sobre los archivos base: sin hallazgos. Suite: **198 passed / 0 failed (752 aserciones)**.

**Fuera de este lote:** 453 atributos `style=` y los `<style>` locales restantes; el `padding: 0 !important` global de las celdas de tabla; los estados vacíos con fondo de cine; RNF-15 (labels y `aria-label`) y RNF-16. Hallazgo aparte: `GET /taller/cursos/crear` responde 500 porque `/taller/cursos/{id}` no restringe el parámetro a números.
### 9.14 D4, lote 2: módulo Taller (26/10)

- **Colores:** en `<style>` y `style=""` los colores fuera de paleta pasan a tokens (`var(--cf-*)`); en JavaScript (SweetAlert, etc.) a su hex de paleta. Azules #0d6efd, #2563eb, #3b82f6, #4e73df → azul de acción; #0891b2, #36b9cc, #5bb4f3 → cian legible; rojos #dc3545, #e74a3b, #d33, #ef4444 → #dc2626; verdes #28a745, #1cc88a, #10b981, #20c997 → #198754; grises #6c757d, #6b7280, #999, #94a3b8 → #64748b. 25 vistas.
- **Degradados:** las bandas navy→azul usan `--cf-gradient-brand`; `.bg-gradient-primary` se define una vez en `main.css` (quitado de 3 vistas). La pantalla de enlace expirado usa #334155→#64748b (antes #94a3b8 con texto blanco).
- **Forma y texto:** radios de 1rem / 15px / 1.5rem pasan a `--cf-radius-card`; se quitaron los redundantes en tarjetas y modales. Tamaños de 0.6–0.7rem suben a 0.75rem.
- **Estadísticas:** paleta categórica nueva de 8 colores en orden fijo, validada con el skill dataviz (lightness, croma, daltonismo y visión normal pasan); desde la 9.ª categoría se agrupa en "Otros" y las categorías en cero no se dibujan. Las barras de una sola serie usan un solo color (antes, cada barra uno distinto). Ejes y rejilla en grises de la paleta. Icono `fa-chair` (no existe en la versión de Font Awesome incluida) → `fa-percent`.
- **Base (`main.css`):** `bg-*` respeta `bg-opacity-*` (KaiAdmin lo anulaba y los iconos de los KPI quedaban en un círculo sólido del mismo color); `btn-outline-info` y `btn-outline-warning` con texto legible.
- **Detector de impeccable:** se corrigieron el borde lateral grueso de `AsistenciaConsolidado` y la fuente Inter del modal de `CursoDetalle`. Quedan avisos falsos de `<img src="">` (vistas previas que rellena JavaScript) y Arial en el editor de plantilla, que imita la fuente del PDF.

**Verificación:** Edge (estadísticas, tipos de actividad, ficha, contenido, asistencia consolidada e individual, calificación, plantilla, mis cursos); suite **198 passed / 0 failed**. Atributos `style=` en Taller: 367 → 343; los restantes son tamaños y estados de JavaScript (`display:none`).

**Hallazgos previos, sin corregir:** `public/js/modules/taller/wizard-paso2.js` tiene un error de sintaxis en la línea 260 (también en HEAD), así que el JavaScript del paso 2 del asistente no se ejecuta; `CursoCrearPaso1.blade.php:90` llama a `ocultarPreviewFotoError()` antes de definirla.
### 9.15 D4, lote 3: módulo Security (26/10)

- **Foco:** se quitaron los `<style>` locales que ponían fondo amarillo #ffff99 al enfocar (`home`, `perfiles`, `recovery_password`) y los de foco azul propio (#3f67f0) del login y la recuperación; ahora rige el foco global. La etiqueta flotante del login usa `--cf-blue`.
- **Mi perfil (`update_profile`):** cabecera con `--cf-gradient-brand` (antes #008be3→#00c6ff con texto blanco); fichas, iconos e insignias con tokens; el botón cian #38b6ff con texto blanco (2,4:1) pasa a cian legible; el morado #6f42c1 a #6861ce.
- **Otros:** selección de perfil (grises y punto verde a tokens), colores de SweetAlert en perfiles, radio de la tarjeta de bienvenida a `--cf-radius-card`. Security queda sin colores fuera de paleta.

**Verificación:** Edge (perfiles, crear usuario, mi perfil, selección de perfil con fondo de cine); el login y la recuperación no se vieron (captcha). Detector: un aviso falso (`<img src="#">` de la vista previa de la foto, oculta hasta elegir archivo). Suite **198 passed / 0 failed**.
### 9.16 D4, lote 4: módulo Registro (26/10)

- **Registro público (`register`):** se quitó el foco propio (#3f67f0 y fondo #f7faff); la etiqueta flotante usa `--cf-blue`.
- **Selección de perfil (`personas/home`):** se quitó el foco amarillo #ffff99 y una etiqueta `<style>` anidada sin cerrar que invalidaba la primera regla de la página (ahora se aplica); fondo a `--cf-app-bg`.
- **Asignar perfil:** colores a tokens; en la confirmación, "Cancelar" pasa de rojo (#d33) a gris y "Confirmar" a #198754. Registro queda sin colores fuera de paleta (salvo los correos).

**Verificación:** Edge (asignar perfil con búsqueda real); el registro público no se vio (requiere cerrar sesión, y volver a entrar exige captcha). Detector sin hallazgos. Suite **198 passed / 0 failed**. `/home` sigue respondiendo 500 ("Undefined variable $perfiles", hallazgo previo, no causado por D4).
### 9.17 D4, lote 5 (vistas generales) y corrección de errores (26/10)

**Vistas generales (`resources/views`):** barra superior, logo, menú lateral y pie usan los tokens nuevos `--cf-chrome` (#1e293b) y `--cf-chrome-text`; activo del menú con `--cf-blue`. Componentes de contenido con tokens (el índigo #4f46e5 del material pasa a #6861ce). En SweetAlert, aviso #f0a500 con texto blanco (2,2:1) → #b45309 y peligro → #dc2626. Páginas de error (401, 404, 419, 503): título en blanco sobre el fondo de cine (antes gris oscuro) y botón de volver claro con texto azul en vez de `btn-success`. Detector sin hallazgos.

**Errores corregidos (pedido del usuario):**
- `public/js/modules/taller/wizard-paso2.js`: faltaba el cierre `};` de `toggleLocalidades`, así que el archivo no cargaba (también en HEAD) y el paso 2 del asistente no calculaba trimestre ni rango de fechas ni buscaba facilitadores. Además, la validación de envío usaba `inputInicio`, que no existe en ese ámbito y habría lanzado `ReferenceError`; ahora usa `inputFecha`. Verificado en Edge: sin errores de consola, `fecha_inicio` 15/11/2026 da 4.º trimestre y `fecha_fin` mínima 16/11/2026.
- `CursoCrearPaso1.blade.php`: el `onerror` de la vista previa llamaba a una función definida más abajo; ahora el manejo va en línea.
- `/home` (y `/security/usuario/perfil/seleccionar`): `SecurityController::registroHome()` renderizaba `registro::personas.home` sin `$perfiles` (500). Ahora muestra la misma selección de perfil que el login, o lleva al inicio si el usuario tiene un solo perfil.
- `/taller/cursos/{texto}` (p. ej. `/taller/cursos/crear`): `CursoDetalleController::show()` pasaba el texto a Postgres (500); ahora responde 404.

**Pruebas:** `RutasSinError500Test` (3): 404 para id no numérico (falla con 500 sin el arreglo), selección de perfil en `/home` y redirección con un solo perfil. Suite **206 passed / 0 failed (804 aserciones)**. Pint: archivos tocados sin issues nuevos.
### 9.18 Acciones del curso por estado (26/10)

**Reporte del usuario:** no se veían los botones para aceptar el curso. Reproducido en el curso 373 (Por Aceptar, facilitador asignado = usuario 1): el panel solo mostraba "Reasignar Facilitador" y "Ajustar Certificado".

- **Causa:** `CursoDetalleController::show()` ponía `$esOperativo = false` a todo el que gestiona todos los cursos, aunque fuera el facilitador asignado. El servidor sí lo permitía (`CursoPolicy::cambiarEstado` → `responderAsignacion`); solo faltaban los botones. Ahora el gestor conserva las acciones de facilitador en sus propios cursos; sigue sin actuar como participante.
- **Capacidades sin botón:** `gestionar_asistencia` (En Curso) y `consultar_asistencia` (Finalizado) se calculaban pero `actions-container` no las dibujaba. Ahora enlazan a la asistencia consolidada (con `@can('gestionarAsistencia')`) y también se ofrecen a la coordinación. `gestionar` y `marcar_asistencia` siguen sin botón: la primera no es una acción y la segunda se hace por QR.

**Matriz verificada (`AccionesPorEstadoCursoTest`):**

| Estado | Facilitador asignado | Coordinación |
|---|---|---|
| 1 Por Aceptar | Aceptar / Rechazar asignación | Reasignar facilitador, ajustar certificado |
| 2 Rechazado | Ver observaciones | Reasignar, ver observaciones |
| 3 Declinado | Ver observaciones, editar, enviar a revisión | Reasignar, ajustar certificado |
| 4 En Edición | Editar, enviar a revisión | Reasignar, ajustar certificado |
| 5 En Aprobación | Aviso "en evaluación" | Aprobar, declinar, reasignar |
| 6 En Inscripción | Contenidos, participantes | Cerrar inscripciones, reasignar, participantes |
| 7 En Curso | Contenidos, participantes | Finalizar curso, gestionar asistencia, reasignar |
| 8 Finalizado | Panel de certificación, encuesta | Archivar, consultar asistencia, certificación, encuesta |
| 9 Cerrado | Panel de certificación, encuesta | Panel de certificación, encuesta |

Participante: postularse y retirar postulación (6), contenidos (6 si aprobado, 7, 8), certificado (8, 9). Borrador (10) no tiene panel: se continúa en el asistente.

**Pruebas:** `AccionesPorEstadoCursoTest` (4; la del administrador facilitador falla sin el arreglo) y `AprobarPostulacionFacilitadorTest` (1: aprobar una postulación asigna el perfil Facilitador activo). Suite **206 passed / 0 failed (804 aserciones)**. Verificado en Edge: el curso 373 muestra Aceptar y Rechazar asignación; el 1261 (Finalizado) muestra Consultar asistencia. No se pulsó Aceptar.

**A decidir:** un gestor que también es facilitador del curso ve "Aprobar propuesta" sobre su propio curso en En Aprobación (5). La política lo permite hoy. → **Resuelto en §9.19** (conflicto de interés).

### 9.19 Perfiles sumados, conflicto de interés y revisión de seguridad (26–27/10)

**Decisión del usuario:** eliminar la selección de perfil; quien tiene varios perfiles dispone a la vez de todas sus acciones, sin mezclar las de facilitador con las administrativas. Respaldo previo: tag `respaldo/2026-10-26-antes-perfiles-sumados`.

**Perfiles sumados**
- `Autorizacion::puede()` / `puedeEnProceso()` evalúan la unión de `perfilesActivosPara()` (asignación `status = 1` y perfil activo, leída de la BD en cada petición). El `profile_id` de sesión queda solo como dato informativo (`perfilPrincipalPara()`: Administrador > Coordinador > Facilitador > Participante).
- Menú (`User::captureMenu`) = unión de procesos de todos los perfiles.
- Login sin pantalla de selección; `usuario.cambiar_perfil`, `usuario.set_perfil` y `/home` redirigen al inicio; se quitó "Cambiar perfil" del navbar. `ValidarPerfilActivo` solo exige al menos un perfil activo.
- "Mis cursos": quien puede facilitar ve pestañas **Asignados / Inscritos** (antes dependía de `profile_id === 2`).
- Anti-autopromoción en permisos de perfiles: a un perfil propio solo se le puede otorgar lo que el usuario ya tiene por la suma, y no se puede desactivar un perfil propio.
- Se invalida la caché RBAC al asignar perfiles (`UsersController::update`, aprobar postulación de facilitador).

**Conflicto de interés (`CursoPolicy`)**
- Nueva habilidad `revisarComoCoordinacion` = gestor **y no** facilitador asignado. La usan reasignar facilitador, devolver/declinar/rechazar desde En Aprobación y aprobar (5→6, también exige no ser el facilitador).
- La coordinación no puede asignarse a sí misma (`reasignarFacilitador` → 422; el modal ya no la lista).
- **Hallazgo previo corregido:** el facilitador asignado podía cambiar `id_persona` desde *Editar programa* y reasignar su curso sin pasar por la regla de reasignación ni la de elegibilidad. Ahora exige `reasignarFacilitador`, elegibilidad y no asignarse a sí mismo.
- Las comprobaciones que usaban `can('reasignarFacilitador')` como sinónimo de "es coordinador" pasan a `can('gestionar')`.
- El usuario raíz (id 1) sigue con bypass total (`Gate::before`).

**Panel del curso por bloques:** *Coordinación* y *Como facilitador* (`capsCoordinacion` / `capsFacilitador`). Cada botón se vuelve a filtrar con la misma regla de la Policy (`boton-gestion.blade.php`), así que el panel no muestra acciones que el servidor rechaza. Si la persona es coordinación y facilitador del curso, el bloque de coordinación explica que revisarlo, aprobarlo o reasignarlo corresponde a otra persona.

**Revisión de seguridad (checklist de `security-and-profile-manager`)**

| Punto | Resultado | Cambio |
|---|---|---|
| IDOR en cuenta propia (`update_profile`) | ✅ Sin hallazgo: usa `Auth::user()` y lista blanca de campos | — |
| IDOR en `users-*` | ✅ Protegido por `permiso:users,…` en el constructor | — |
| Escalada por asignación de perfil (D8) en `users-create` / `users-update` | ❌ → ✅ Se podía asignar cualquier perfil, incluido Administrador | `Autorizacion::perfilesAsignables()` (regla única con `PersonaPerfilController`); 403 si no se posee |
| Autoedición desde administración | ❌ → ✅ Un usuario podía cambiarse el perfil por `users-update/{su id}` | Bloqueado; también el cambio de contraseña propia desde administración |
| Sesiones al cambiar contraseña o correo | ❌ → ✅ No se regeneraba la sesión ni se cerraban las demás | `rotarSesiones()`: `session()->regenerate()` + nuevo `session_token` |
| Restablecer contraseña de otro | ❌ → ✅ No cerraba sus sesiones; comparaba con la contraseña del actor | Rota el `session_token` del afectado y compara con su contraseña |
| Reautenticación al cambiar correo | ❌ → ✅ | Exige la contraseña actual si el correo cambia |
| Código de verificación | ❌ → ✅ No caducaba | Válido 15 minutos |
| `session_token` en serialización | ❌ → ✅ | Añadido a `User::$hidden` |
| Mensajes de excepción al cliente | ❌ → ✅ 7 puntos devolvían `getMessage()` (SQL, SMTP) | Solo con `APP_DEBUG`; `report($e)` donde no se registraba |
| PII en logs | ⚠️ → ✅ DNI completo en `searchByDni`; URL completa en `ADMIN-BYPASS` | DNI enmascarado; solo la ruta |
| `show_pdf` / `file_admin` (heredados de la plantilla) | ❌ → ⚠️ Parámetro de URL usado en ruta de disco y nombre de archivo de Imagick, sin permiso | Restricción `[A-Za-z0-9_-]+`, página entera, 404 si no existe. **Pendiente:** retirar las rutas si no se usan |
| Hashing | ✅ bcrypt cost 12 | — |
| Cookies de sesión | ⚠️ `http_only`, `same_site=lax`, sesión cifrada; `secure` depende de `SESSION_SECURE_COOKIE` (false en desarrollo) | Producción: `SESSION_SECURE_COOKIE=true` junto con TLS (RNF-05) |
| RNF-04 | ❌ `env.example` (versionado) conservaba `MAIL_PASSWORD` | Vaciado en el working tree. **Falta:** revocar la contraseña de aplicación de Gmail y rotar `APP_KEY` (siguen en el historial) |

**Pruebas nuevas:** `PerfilesSumadosTest` (9), `ConflictoInteresCursoTest` (5), `SeguridadCuentaTest` (8). Pruebas existentes ajustadas: los fixtures elegían como "facilitador", "coordinador" o "participante" al mismo usuario 2 (Coordinador + Facilitador + Participante en desarrollo); con perfiles sumados cada actor se aísla con `TestCase::soloConPerfiles()`. Esto también corrige la fragilidad de `IntegridadFlujosTest` en instalación limpia (§9.9). Suite **228 passed / 0 failed (892 aserciones)**. Pint: archivos nuevos limpios; los issues que quedan en archivos tocados son heredados.

**No verificado:** revisión visual en Edge (la extensión no estaba conectada).

### 9.20 Actualización de infraestructura y dependencias (27/10)

**Motivo:** `composer audit` reportaba 59 avisos (2 críticos en PhpSpreadsheet, 1 crítico y 1 alto en Laravel 10, sin soporte desde feb/2025) y Composer 2.10 ya bloqueaba instalar esas versiones. Respaldo previo: tag `respaldo/2026-10-27-antes-actualizacion-dependencias`.

| Componente | Antes | Ahora |
|---|---|---|
| PHP (imagen `Dockerfile`) | 8.2.35 (`php:8.2-fpm`) | **8.5.12** (`php:8.5.12-fpm`, fijada) con Imagick, GD, pdo_pgsql, zip, OPcache |
| Laravel | 10.49.1 | **13.39.0** (estructura heredada de `app/Http/Kernel.php`, soportada) |
| `nwidart/laravel-modules` | 10.0 | 13.2 (`config/modules.php` regenerado; `app_folder` = `''` porque los módulos tienen las clases en la raíz) |
| `yajra/laravel-datatables-oracle` | 10.11 | 13.3 |
| `maatwebsite/excel` / PhpSpreadsheet | 3.1.58 (fijada) / 1.30.0 | 4.x / 5.x |
| `intervention/image` | 3.11 | 4.4 |
| `bacon/bacon-qr-code` | 2.0 | 3.1 |
| `laravel/sanctum` / `tinker` | 3.3 / 2.10 | 4.3 / 3.0 |
| PHPUnit / Collision | 10.5 / 7 | 13.4 / 8.9 |
| `biscolab/laravel-recaptcha` | 6.1 (abandonado, sin uso) | Eliminado, con `config/recaptcha.php` |
| PostgreSQL / nginx / pgAdmin | 17.11 / 1.31.8 | Último parche (sin cambio de versión mayor) |
| JS DataTables (+ Responsive) | 1.10.16 (XSS, *prototype pollution*) | 1.13.11 + Responsive 2.5.1, misma integración Bootstrap |
| JS jQuery Validation | 1.17.0 (2 ReDoS) | 1.22.1 (+ `messages_es`) |
| JS Bootstrap (KaiAdmin y CDN) | 5.3.2 / 5.3.0 | 5.3.8 |
| JS Chart.js | 4.4.4 | 4.6.1 |
| CDN jQuery (registro) / Font Awesome / Inputmask | 3.6.0 / 6.0.0 / 5.0.8 | 3.7.1 / 6.7.2 / 5.0.10 |

**Resultado:** `composer audit` → **0 avisos**.

**Cambios de código por compatibilidad:**
- `AppServiceProvider`: se quitó `Sanctum::ignoreMigrations()`, que ya no existe en Sanctum 4.
- `ImagenCertificadoService`: con Intervention 4, `read()` pasa a `decodePath()` y `pad()` a `containDown()` (mismo comportamiento: no agranda la imagen y la centra sobre blanco), con codificadores `PngEncoder` / `JpegEncoder(85)`. Ninguna prueba lo cubría; se detectó en una prueba de humo.
- Captcha (`SecurityController::captcha`):
  - La fuente pasa a ruta absoluta, porque `imagettftext()` en PHP 8.5 ya no resuelve rutas relativas y el captcha daba **500**, lo que bloqueaba el login.
  - Antes, la imagen salía con `Content-Type: text/html`; ahora se captura y se envía como `image/jpeg` sin caché.
- `composer.json`: se autoriza `wikimedia/composer-merge-plugin` (dependencia de `nwidart/laravel-modules` 13).

**Verificación:**
- Suite **228 passed / 0 failed (892 aserciones)** en PHP 8.5.12 con PHPUnit 13, sin *deprecations*, *warnings* ni *notices*.
- Prueba de humo real: QR (300×300, 8 bits), reencodeo y relleno de la imagen del certificado, `.xlsx` generado y vuelto a leer, PDF con FPDF y lectura con PdfParser.
- En Edge: el login carga sin errores de consola ni recursos fallidos y el captcha se muestra.
- En Edge, con sesión de administrador, cargan sin errores de consola:
  - Usuarios (DataTables 1.13: orden, búsqueda y paginación) y Perfiles.
  - Estadísticas (7 gráficos de Chart.js 4.6).
  - Cursos, Mis cursos, detalle de curso finalizado, Panel de certificación, Asistencia y Resultados de encuesta.
  - Requisitos de facilitador y wizard paso 1.
  - Formularios con jQuery Validation 1.22 (mensajes en español).
  - Verificación pública de certificado y registro.

**Defectos anteriores a la actualización, encontrados en la revisión y corregidos:**
- `users/create`, `users/update` y `users/password`: el `errorPlacement` ponía el mensaje fuera de la columna, el mensaje ocupaba una fila completa del grid y apilaba todos los campos en una sola columna. Ahora el mensaje va dentro de su columna.
- `public/js/menu.js` `getCamerasPC()`: al abrir *Mi perfil* pedía la cámara sin capturar el error, así que en equipos sin cámara salía `NotFoundError` en consola. Se añadió `.catch`.
- `register.blade.php`: la máscara de teléfono **nunca se aplicaba**, porque el layout vuelve a cargar jQuery después del plugin y borraba `$.fn.inputmask`. Ahora usa la versión de Inputmask que no depende de jQuery, con `removeMaskOnSubmit`: se ve `(0424) 123-4567` y se guarda solo con dígitos, igual que los teléfonos existentes en `comun.personas`.

**No actualizado a la última versión mayor (a propósito):**
- jQuery 4, DataTables 3 y Font Awesome 7: la plantilla KaiAdmin y sus plugins dependen de jQuery 3 y de DataTables 1.x. Se instaló la última versión compatible de cada una, que ya corrige las vulnerabilidades conocidas.
- ~~PostgreSQL 18: requiere volcado y restauración de la BD.~~ Hecho, ver abajo.

**PostgreSQL 17.11 → 18.6 (27/10):**
- Procedimiento:
  1. Se detuvieron `app`, `webserver` y `pgadmin`.
  2. Se hizo un `pg_dumpall` completo.
  3. Se creó el volumen nuevo `pgdata18` y se restauró.
- `docker-compose.yml`:
  - imagen fijada en `postgres:18.6`;
  - el volumen se monta en `/var/lib/postgresql`, porque la imagen 18 cambió la ruta y no arranca con el montaje antiguo en `/data`.
- Restauración: solo dos errores, ambos esperados: *role/database already exists*, porque los crea la imagen.
- Verificación:
  - conteo de filas idéntico en las 47 tablas (2.016 filas) y 46 secuencias;
  - suite **228 passed**;
  - en Edge, Estadísticas y Usuarios muestran los mismos datos.
- El volumen `pgdata` (PostgreSQL 17) **se conserva sin tocar** como respaldo. Para volver atrás: restaurar `image: postgres:17` y `pgdata:/var/lib/postgresql/data`.
---

## 10. Anexos

### 10.1 Snapshot y comandos

```powershell
git rev-parse --short HEAD          # 86f140a
git status --short                  # 49 entradas: modificados y sin seguimiento
```

Comandos ejecutados el 2026-10-23:

```powershell
docker compose exec -T app php artisan test --colors=never
# Tests: 133 passed (518 assertions)

docker compose exec -T -u www-data app vendor/bin/pint --test
# FAIL 269 files, 149 style issues

docker compose exec -T -u www-data app vendor/bin/pint --test <35 archivos tocados>
# FAIL 35 files, 6 style issues

docker compose exec -T app php artisan route:list --json
# 143 rutas, 134 con nombre

docker compose exec -T db psql -U laravel -d cineform -A -t \
  -c "SELECT id_estado, nombre FROM taller.estados_curso ORDER BY id_estado;"
# 1|Por_aceptar … 9|Cerrado, 10|Borrador

# Instalación limpia en BD temporal (la BD de desarrollo no se tocó)
docker compose exec -T db psql -U laravel -d cineform -c "CREATE DATABASE cineform_audit;"
docker compose exec -T -e DB_DATABASE=cineform_audit app php artisan migrate:fresh --seed --force
docker compose exec -T -e DB_DATABASE=cineform_audit app php artisan test
# 120 passed (489 assertions)
docker compose exec -T db psql -U laravel -d cineform -c "DROP DATABASE cineform_audit;"
```

### 10.2 Inventario

| Elemento | Cantidad |
|---|---:|
| Vistas Blade | 101 |
| Rutas registradas (`route:list --json`) | 143 |
| Rutas con nombre | 134 |
| Archivos de test | 30 |
| Métodos de test ejecutados | 145 |
| Rutas en `PENDIENTES_DE_MIGRAR` | 22 |

### 10.3 Limitaciones de la ejecución

- **Entorno:** Docker Desktop, PostgreSQL 17, `vendor` instalado.
- **No ejecutado:** PHPStan, cobertura, benchmarks, navegador, axe, Playwright, Dusk.
- **Secretos:** se caracterizaron por longitud y formato sin reproducir sus valores.

### 10.4 Criterios de cierre

Un requisito se considera cerrado cuando:

1. Tiene prueba automatizada o reproducción manual documentada.
2. La UI permite invocarlo con el rol correcto.
3. No introduce secretos, `eval` con input, `@` silenciosos ni consultas sin esquema.
4. Cumple accesibilidad básica en las pantallas tocadas.
5. Tiene rollback documentado si incluye migración de datos.
6. Pasa Pint sobre los archivos tocados y no aumenta la deuda del repositorio.
7. **Está commiteado**, y la fila correspondiente de este documento se actualiza con estado, evidencia y fecha.
8. **Los totales de §2 se recalculan en el mismo cambio** que modifica una fila.

---

## 11. Próxima revisión

- Cerrar RNF-04 (rotación) y commitear el working tree tras Pint.
- Completar la UI de retiro por gestor (RF-27).
- D4 aplicada en todos los módulos (§9.13–§9.17); cerrar UX-25 cuando se limpie el residuo de estilos en línea. Decidir si el coordinador sembrado recibe el perfil Facilitador (§9.12).
- Fijar el participante de `IntegridadFlujosTest` para que la suite pase también en una instalación limpia (§9.9).
- Reejecutar tests, Pint y `route:list`; medir cobertura; repetir las métricas UX de §5.3.
- Mantener los IDs existentes; añadir nuevos al final (`RF-77+`, `RNF-26+`, `UX-36+`).

**Responsable:** equipo CINEFORM
**Última actualización:** 2026-10-23
