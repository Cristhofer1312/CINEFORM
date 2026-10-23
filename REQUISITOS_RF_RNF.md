# CINEFORM — Auditoría de requisitos (RF/RNF) y UX/UI

> **Snapshot:** commit `86f140a` + working tree sin commitear (66 entradas en `git status`: Tier 1, lote de permisos y perfiles, y trabajo previo) · rama `main` · 2026-10-23
> **Alcance:** módulos `Taller`, `Security`, `Registro`, `Comun` y `Parametros` (Laravel 10.49 / PHP 8.2 / PostgreSQL 17)
> **Método:** contraauditoría de la versión manual del 2026-10-22. Cada fila marcada "Resuelto" se reverificó en código; los totales se recalcularon desde las tablas; runtime en Docker (suite, Pint, `route:list`, instalación limpia en BD temporal).
> **Runtime:** `php artisan test` → **145 passed / 0 failed (565 aserciones)** sobre la BD de desarrollo, tras el lote de permisos. Instalación limpia (`migrate:fresh --seed` en BD temporal) verificada tras el lote: migraciones, seeders, trigger de auditoría y permisos de catálogos correctos. Pint global (medido antes del Tier 1): 149 issues en 269 archivos.
> **Fuente de verdad:** este documento. Los resúmenes (§2) se derivan de las tablas (§3–§5); si discrepan, mandan las tablas.
> **Estado:** P0 abiertos: **E1** (aceptar asignación deja el curso Rechazado; viene del working tree, no commitear sin corregirlo), **E2** (editor de cursos 500), **RNF-04** (secretos) y **RNF-13** (respaldos). Tier 1 y lote de permisos y perfiles implementados y verificados, sin commitear. Pendientes de decisión: D3, D6, D7, D8 y la regla de E5.

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
| ✅ Implementado (evidencia suficiente) | 40 | 48,2 % |
| ⚠️ Parcial | 25 | 30,1 % |
| ❌ Faltante o roto | 18 | 21,7 % |
| **Total RF** | **83** | **100 %** |

> La versión manual del 22/10 actualizó varias filas a ✅ (RF-15, RF-26, RF-30, RF-38, RF-39) **sin recalcular estos totales**: seguía mostrando 30/28/18. El recuento real desde sus propias tablas era 35/25/16. Tras la contraauditoría del 23/10 quedó 34/26/16; con RF-56 pasó a 35/25/16, y con el Tier 1 (RF-24, RF-33, RF-34 y RF-39 a ✅) quedó en 39/21/16 (§9.4). Con el lote de permisos (RF-77 a RF-83) y la revisión del ciclo de vida (RF-12 y RF-15 a ❌, RF-39 a ⚠️) queda en 40 / 25 / 18 sobre 83 RF (§9.5).

### 2.2 Totales por módulo

| Módulo | RF | ✅ | ⚠️ | ❌ |
|---|---:|---:|---:|---:|
| Acceso y seguridad | 8 | 3 | 5 | 0 |
| Cursos y ciclo de vida | 12 | 4 | 5 | 3 |
| Inscripción | 12 | 6 | 4 | 2 |
| Asistencia | 7 | 4 | 2 | 1 |
| Calificaciones | 6 | 2 | 2 | 2 |
| Certificación | 8 | 4 | 2 | 2 |
| Facilitadores | 5 | 5 | 0 | 0 |
| Satisfacción | 4 | 3 | 0 | 1 |
| Estadísticas y administración | 10 | 5 | 3 | 2 |
| Público | 4 | 0 | 1 | 3 |
| Permisos y perfiles | 7 | 4 | 1 | 2 |
| **Total** | **83** | **40** | **25** | **18** |

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
- **No puede considerarse cerrado** mientras RNF-04 siga abierto, RF-37/RF-44 no apliquen reglas de elegibilidad y los cambios sigan sin commitear.

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
| RF-12 | Temario con evaluaciones y ponderaciones | ❌ | **E2:** `T/Http/Controllers/EditarCursoController.php:58` usa `$idPersona` sin definir; `edit()` devuelve 500 (`ErrorException: Undefined variable $idPersona`), reproducido en runtime (`R`). También en HEAD | Nadie puede abrir el editor de cursos, así que el temario no se puede editar tras crear el curso. Además, **E3:** `update()` no comprueba el estado del curso (reproducido: se renombró un curso Finalizado). |
| RF-13 | Plantilla y firma de certificado | ✅ | `CrearCursoController.php:558-658,821-837`; `tests/Feature/Taller/CertificadoPruebaWizardTest.php` | Sin prueba del guardado real del paso 4 ni del movimiento definitivo de archivos. |
| RF-14 | Requisitos de inscripción | ✅ | `T/Http/Controllers/CursoRequisitoController.php:179-251`; `CrearCursoController.php:691-716`; `tests/Feature/Taller/CartaPermisoMenoresTest.php` | Cobertura funcional adecuada. |
| RF-15 | Transiciones de estado validadas | ❌ | **E1:** `T/Http/Controllers/CursoController.php:76-79` convierte en Rechazado cualquier destino desde Por aceptar que no sea Rechazado o Declinado; "Aceptar asignación" (`CursoDetalle.blade.php:709`, `id_estado: 4`) deja el curso Rechazado y sin facilitador con HTTP 200 (`R`). Introducido en el working tree, no en HEAD. Seeder de estados correcto (`R`, instalación limpia 1–10) | Bloquea el ciclo en el paso 2. **E4:** en Declinado se ofrece "Enviar a revisión" pero el enum no permite Declinado → Aprobación (422). **E8:** desde Inscripción no hay forma de cancelar el curso. Sin prueba de la aceptación. |
| RF-16 | Reasignación de facilitador | ⚠️ | `T/Routes/web.php:166-167`; `T/Http/Controllers/CursoController.php:609-615` (el destino debe tener perfil Facilitador, 422); llamador UI `CursoDetalle.blade.php:908`; `tests/Feature/Taller/IntegridadFlujosTest.php::test_no_se_reasigna_a_persona_sin_perfil_facilitador`; `FacilitadorRechazoYReasignacionTest` (4 pruebas, `T`) | Corregido en Tier 1 la validación del destino. Pendiente: motivo obligatorio; auditoría con autor; la migración `down()` falla si hay cursos con `id_persona` nulo. |
| RF-17 | Duplicar curso / reutilizar plantilla | ❌ | No hay ruta, controlador ni servicio | Implementar o retirar formalmente del alcance (D5). |
| RF-18 | Archivar o eliminar borradores | ⚠️ | `T/Services/BorradorWizardService.php:49-55`; `T/Routes/web.php:45-46` (`GET /crear-curso/nuevo`) | No hay listado, archivo ni borrado individual; el GET borra el borrador sin CSRF. |
| RF-19 | Vista previa del curso antes de publicar | ⚠️ | `T/Routes/web.php:67-68`; `CursoRevision.blade.php` | Solo es un resumen del wizard; no hay preview real. Sin prueba. |
| RF-20 | Historial de estados con motivo y autor | ⚠️ | `T/Entities/Curso.php:283-306`; `CursoDetalle.blade.php` | Confirmado: el insert en `curso_estado` (`Curso.php:300-306`) no guarda autor; `updateStatus` no pasa motivo; no hay timeline. |

### 3.3 Inscripción

| ID | Requisito | Estado | Evidencia principal | Brecha / siguiente acción |
|---|---|---|---|---|
| RF-21 | Catálogo de cursos disponibles con filtros | ⚠️ | `T/Http/Controllers/CursoController.php:122-229`; `Cursos.blade.php:20-41` | La UI del participante solo expone búsqueda; no hay portal público; la raíz redirige a login. |
| RF-22 | Detalle con cupos, fechas, modalidad y requisitos | ⚠️ | `CursoDetalle.blade.php`; `T/Http/Controllers/CursoDetalleController.php:151-160` | No carga ni muestra los requisitos en el detalle; solo aparecen al abrir el formulario. |
| RF-23 | Postulación con preguntas y documentos | ✅ | `InscripcionController.php:21-64,79-230`; `CursoInscribirse.blade.php`; `InscripcionFlujoTest::test_participante_puede_enviar_postulacion_y_queda_en_estado_postulado` (`R`) | Cubierto también por las pruebas de documentos de RF-30. |
| RF-24 | Aprobar, rechazar y denegar postulaciones | ✅ | `T/Routes/web.php:131-136`; `T/Http/Controllers/InscripcionController.php:419-445` (`rechazar()` devuelve 400 si la inscripción está aprobada, `:433`); `denegar()` ya bloqueaba aprobadas (`:472`); `tests/Feature/Taller/IntegridadFlujosTest.php::test_rechazar_no_puede_revertir_inscripcion_aprobada` (`T`) | Sin brecha abierta en esta fila. La notificación al participante es RF-29; la rehabilitación con revisión es RF-25. |
| RF-25 | Corrección y rehabilitación | ⚠️ | `InscripcionController.php:97-130,333-370`; `InscripcionFlujoTest` (corrección, `R`) | `rehabilitar()` pasa directo a `aprobado` (`:357`) saltando revisión; sin botón UI. |
| RF-26 | Cancelación por el participante | ✅ | `InscripcionController.php:290-327`; `partials/curso-actions/actions-container.blade.php`; `InscripcionFlujoTest::test_participante_postulado_puede_ver_estado_y_retirar_postulacion` (`R`) | Funciona solo en estado Inscripción. Hace `delete()` físico (`:319`) sin conservar evidencia, y los archivos se borran antes que el registro y fuera de una transacción (`:313-317`). |
| RF-27 | Retiro por gestor con motivo | ⚠️ | `InscripcionController.php:253-296` (motivo obligatorio `:273-277`, estado `ESTADO_DENEGADO` `:280`); test `IntegridadFlujosTest::test_retiro_de_gestor_queda_denegado_y_no_recuperable`; `RetiroGestorMotivoTest` | Estado corregido a `denegado` (no reentrega, bloqueado en `:103-106`), motivo obligatorio (422 sin motivo). Pendiente: sin UI de retiro por gestor (el único `DELETE` en la vista es el de cancelar del participante, `CursoDetalle.blade.php:828`) y decidir D6 (`denegado` vs `retirado`). |
| RF-28 | Lista de espera | ❌ | Sin ruta, modelo, tabla ni UI | Implementar o retirar del alcance (D5). |
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
| RF-37 | Regla de asistencia mínima para certificar | ❌ | `CertificacionPanelController.php:94-127`; `CertificadoController.php:33-83`; `app/Helpers/Helpers.php` | Confirmado: ni `aprobar()` ni `descargar()` consultan el porcentaje de asistencia. Umbral pendiente (D3). |

### 3.5 Calificaciones

| ID | Requisito | Estado | Evidencia principal | Brecha / siguiente acción |
|---|---|---|---|---|
| RF-38 | Planilla de notas por evaluación | ✅ | `T/Http/Controllers/CalificacionController.php:17-67`; `CursoCalificar.blade.php`; `HuecosFlujoTest` (`R`) | Verificado: filtra inscripciones aprobadas y exige `es_evaluacion` (`:27`). |
| RF-39 | Notas 0–100 con validación | ⚠️ | `T/Http/Controllers/CalificacionController.php:80-81` (bloquea Finalizado y Cerrado, 403); `IntegridadFlujosTest::test_no_se_modifican_calificaciones_con_curso_finalizado` (`T`) | **E5:** el bloqueo en Finalizado contradice la UI: el diálogo de finalizar dice "se habilita la calificación" y el botón "Calificar" se muestra siempre (`CursoContenido.blade.php:129-134`); el facilitador escribe las notas y recibe 403 al guardar. Decidir si se califica en Finalizado (propuesta: permitir en Finalizado, bloquear en Cerrado). |
| RF-40 | Promedio ponderado | ⚠️ | `CursoDetalleController.php`; `CertificacionPanelController.php:63-77` | Renormaliza sobre la ponderación evaluada y no exige que las ponderaciones sumen 100. Sin prueba. |
| RF-41 | Visibilidad del promedio en estados 7/8/9 | ✅ | `CursoDetalleController.php`; `CursoDetalle.blade.php:47-54` | Sin prueba específica. |
| RF-42 | Exportación de acta / CSV | ❌ | `T/Routes/web.php:169-173` solo `index` y `store` | No existe ruta, acción ni generación CSV/acta. |
| RF-43 | Auditoría de cambios de nota | ❌ | `CalificacionController.php` sobrescribe con `updateOrInsert` | No conserva valor anterior ni actor por cambio (solo el último `calificado_por`). |

### 3.6 Certificación

| ID | Requisito | Estado | Evidencia principal | Brecha / siguiente acción |
|---|---|---|---|---|
| RF-44 | Panel de elegibilidad (asistencia + nota + encuesta) | ⚠️ | `CertificacionPanelController.php:15-90`; `CertificacionPanel.blade.php` | Panel informativo: no aplica umbral, nota mínima ni encuesta. |
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
| RF-59 | Resultados agregados | ✅ | `SatisfaccionCursoService.php:67-120`; `HuecosFlujoTest::test_facilitador_puede_ver_resultados_de_satisfaccion` (`R`) | Falta prueba de autorización negativa. Quién debe verlos sigue pendiente (D2). |
| RF-60 | Exportación de resultados | ❌ | `T/Routes/web.php` solo `resultados` | No existe CSV/PDF de resultados. |

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
| RF-78 | Asignación de perfiles sin quitar los que el actor no puede otorgar | ✅ | `R/Http/Controllers/PersonaPerfilController.php:151-230` (diferencia; conserva perfiles no controlados; ignora los reenviados que ya tiene; auditado); `PermisosPerfilesSeguridadTest` (2 pruebas, `T`; fallan contra HEAD) | La lista de perfiles asignables sigue fija en código (ver RF-82). |
| RF-79 | Catálogos: ver separado de crear y editar | ✅ | `T/Routes/web.php:222-247` (`VER` / `CREAR` / `EDITAR`); migración `database/migrations/2026_10_23_000002_separa_permisos_catalogos.php` (otorga los nuevos permisos a quien ya tenía `view`); seeders actualizados; `CatalogoController::index` y `Catalogos.blade.php` ocultan botones sin permiso; `PermisosPerfilesSeguridadTest` (2 pruebas, `T`) | Admin y Coordinador conservan crear y editar; se pueden retirar por separado. |
| RF-80 | Elegibilidad única como facilitador | ✅ | `Autorizacion::consultaElegiblesFacilitador()` y `esElegibleFacilitador()` (`:207-227`): cuenta activa + perfil activo con asignación activa + `EDITAR_CURSO`; usada en `EditarCursoController`, `CursoDetalleController` y `CursoController::reasignarFacilitador`; `PermisosPerfilesSeguridadTest`, `IntegridadFlujosTest` (`T`) | Coordinador y Administrador también son elegibles porque tienen `EDITAR_CURSO` en la BD actual. |
| RF-81 | Autorización por acción específica y alcance, sin roles encubiertos | ❌ | `T/Policies/Traits/ResuelveRolesTaller.php:27-39` (`esGestor()` = `GESTIONAR_CURSO`, `esFacilitador()` = `EDITAR_CURSO`) | Pendiente de decisión sobre cómo modelar el alcance global (D7). Se hace junto con el rediseño del ciclo de vida. |
| RF-82 | Delegación de perfiles configurable por permiso | ❌ | `R/Http/Controllers/PersonaPerfilController.php:136-155` (lista fija: el coordinador solo asigna Facilitador y Participante) | Un perfil personalizado solo lo asigna el raíz. Pendiente de decisión (D8). |
| RF-83 | Catálogo único de acciones y procesos por dominio | ⚠️ | La pantalla de permisos ya lee las acciones de `security.permissions` (`Helpers.php`, `showActions`) en vez de `processes.actions` | Siguen existiendo `processes.actions` (sin uso en la pantalla), los 5 métodos de `SecurityAction` y un único proceso de cursos ligado al nombre de ruta `taller.cursos.index`. |

---

## 4. Requisitos no funcionales

| ID | Categoría | Requisito | Estado | Evidencia / medición | Brecha |
|---|---|---|---|---|---|
| RNF-01 | Seguridad | Política única de contraseñas | ✅ | `app/app/Providers/AppServiceProvider.php:28-30` (`min(10)->mixedCase()->numbers()->symbols()->max(72)`); usada en `RegisterController.php:99`, `SecurityController.php:331,478,780`, `UsersController.php:153,327`; `tests/Feature/Security/PoliticaContrasenaTest.php` (`R`) | Verificado. Sin historial de contraseñas (lo pedía §4.1 anterior); se reclasifica como P2. |
| RNF-02 | Seguridad | RBAC en el 100 % de rutas mutantes | ⚠️ | `tests/Feature/Security/RutasProtegidasTest.php:28-51` (22 en `PENDIENTES_DE_MIGRAR`, 2 en `AUTORIZADAS_EN_CONTROLADOR`); `T/Routes/web.php:275-290` | Meta no cumplida: 22 rutas dependen de comprobaciones en el controlador; `security.admin` sin ninguna. |
| RNF-03 | Seguridad | Uploads seguros | ⚠️ | `InscripcionController.php:171-213`; `PostulacionFacilitadorRequest.php:41-43`; `PostulacionFacilitadorController.php:142-145`; `InscripcionFlujoTest` (`R`) | **Baja de ✅:** tipo, tamaño y nombre UUID resueltos, pero ambos flujos guardan en el disco `public` (`storage/app/public`, `visibility=public`), no en almacenamiento privado; si se ejecuta `storage:link` los documentos personales quedan servidos por URL. El borrado del archivo previo ocurre antes del `commit`. |
| RNF-04 | Seguridad | Secretos fuera de git | ❌ | `env.example:74` (`MAIL_PASSWORD` con formato de contraseña de aplicación de Gmail, 16 caracteres + espacios); `APP_KEY` real en el historial (`git log -S` → `8e8036d`); `docker-compose.yml:43` (`POSTGRES_PASSWORD: 123456`) | **Refutado "resuelto":** solo se vació `APP_KEY` en el working tree. La credencial de correo sigue versionada y ninguna se rotó. Acción: revocar la contraseña de aplicación de Gmail, rotar `APP_KEY` en todos los entornos, vaciar `MAIL_PASSWORD` en `env.example`, mover `POSTGRES_PASSWORD` a `.env` y valorar purgar el historial. |
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

#### UX-25 · No hay sistema de diseño · P2 · Abierto

- **Evidencia:** 419 `style="` en 97 vistas; 35 bloques `<style>`; dos azules de marca; `btn-success` usado para navegar.
- **Impacto:** cada pantalla se ve distinta y cambiar un color exige tocar varios archivos.
- **Acción:** tokens en `app/public/css/main.css`; `<style>` local solo con `@push('styles')`.

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
| Cursos | RF-09, RF-11 (fechas), RF-13 (parcial), RF-14, RF-15, RF-16 | RF-10, RF-12, RF-17, RF-18, RF-19, RF-20 |
| Inscripción | RF-23, RF-24, RF-25 (corrección), RF-26, RF-27, RF-30, RF-75 | RF-21, RF-22, RF-28, RF-29, RF-76 |
| Asistencia | RF-31 (negativo), RF-32, RF-33, RF-34 | RF-35, RF-36, RF-37 |
| Calificaciones | RF-38, RF-39 | RF-40, RF-41, RF-42, RF-43 |
| Certificación | RF-45 (permiso), RF-48 (unitario) | RF-44, RF-46, RF-47, RF-49, RF-50, RF-51 |
| Facilitadores | RF-55 (negativo), RF-56 | RF-52, RF-53, RF-54 |
| Satisfacción | RF-59 | RF-57, RF-58, RF-60 |
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
| — | Commitear el working tree (tras Pint) | Las correcciones de seguridad y la suite verde se pierden con un `checkout` | 0 |
| E1 (RF-15) | Quitar el forzado a Rechazado en `updateStatus` (`CursoController.php:76-79`) y añadir prueba de aceptación | Ningún curso nuevo puede aceptarse; corregir **antes** de commitear | 0 |
| E2 (RF-12) | Definir `$idPersona` en `EditarCursoController::edit()` y añadir prueba de carga del editor | Nadie puede editar cursos | 0 |
| RNF-13 | Respaldo diario y restauración probada (RPO 24 h / RTO 4 h) | Pérdida irreversible de datos | 4 |

### 7.2 P1 — Núcleo del negocio

| ID | Acción | Impacto | Fase |
|---|---|---|---|
| E3 / E4 / E6 | Una sola tabla acción × estado consultada por UI y backend: `update()` con control de estado, Declinado → revisión coherente, estado del curso en asistencia, certificación y postulaciones | Cambios en cursos cerrados; botones que fallan | 1 |
| E5 (RF-39) | Decidir si se califica en Finalizado y alinear UI y backend | El facilitador pierde las notas al guardar | 1 |
| E7 / E8 | Resolver postulaciones pendientes al iniciar el curso; estado Cancelado | Postulaciones huérfanas; cursos sin salida | 1 |
| RF-81 | Acciones específicas con alcance en lugar de `esGestor()` / `esFacilitador()` (con el rediseño del ciclo) | Permisos comodín | 1 |
| RF-82 | Delegación de perfiles configurable | Perfiles personalizados no delegables | 2 |
| RF-83 | Retirar `processes.actions`, unificar `SecurityAction`, procesos por dominio | Deriva entre fuentes del catálogo | 2 |
| RF-27 | UI de retiro por gestor y decisión D6 (estado `denegado` por defecto) | Retiro sin interfaz | 1 |
| RF-37 / RF-44 / RF-45 | Aplicar umbral de asistencia, nota mínima, encuesta y estado Finalizado antes de certificar | Certificación indebida | 1 |
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
| RF-17 | Duplicar curso o retirar del alcance | 3 |
| RF-18 | Archivar/eliminar borradores individualmente (por POST) | 1 |
| RF-19 | Vista previa real antes de publicar | 3 |
| RF-28 | Lista de espera | 4 |
| RF-42 / RF-60 / RF-64 | Exportaciones CSV/PDF/acta | 4 |
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

### D2 · Quién ve los resultados de satisfacción (UX-16, RF-59, RF-65) — **Pendiente**

Opciones: (a) solo facilitador del curso; (b) solo coordinación/gestor; (c) ambos. Hoy el código permite al facilitador (`HuecosFlujoTest::test_facilitador_puede_ver_resultados_de_satisfaccion`), lo que equivale de facto a (a) o (c).

### D3 · Umbral único de asistencia para certificar (UX-15, RF-37, RF-44) — **Pendiente**

Opciones: (a) 75 %; (b) 80 %; (c) otro. Hoy la UI usa 75/50 para asistencia y 80 para promedio académico. Bloquea RF-37.

### D4 · Alcance del sistema de diseño (UX-25, RNF-15/16/17) — **Pendiente**

Opciones: (a) todas las pantallas (~30 h); (b) solo flujo P1–P8 (~8 h).

### D5 · Alcance de RF-17 y RF-28 — **Pendiente**

¿Se implementan duplicar curso y lista de espera, o se retiran del alcance?

### D6 · Estado de una inscripción retirada por el gestor (RF-27) — **Pendiente** (por defecto `denegado`)

Opciones: (a) nuevo estado `retirado` no corregible; (b) reutilizar `denegado`; (c) mantener `rechazado` y aceptar que el participante pueda volver a postularse. Recomendación: (a), porque conserva la diferencia entre "denegado en revisión" y "retirado durante el curso".

### D7 · Cómo modelar el alcance global de las acciones (RF-81) — **Pendiente**

Opciones: (a) duplicar las acciones que lo necesiten ("Calificar curso asignado" / "Calificar cualquier curso"); (b) un único permiso de supervisión que extienda a todos los cursos las acciones que el usuario ya tiene. (a) es más explícita; (b) genera menos permisos pero se acerca a un comodín.

### D8 · Delegación de perfiles (RF-82) — **Pendiente**

¿Qué perfiles puede asignar cada perfil? Opciones: (a) lista configurable por perfil; (b) regla automática "solo perfiles cuyos permisos ⊆ los míos" (hoy rompería al Coordinador, que no tiene `grade_course` y no podría asignar Facilitador).

---

## 9. Changelog de la auditoría

### 9.1 Cambio de totales

| Dimensión | 21/10 | 22/10 (manual, publicado) | 22/10 (recuento real de sus tablas) | 23/10 (tras lote de permisos) |
|---|---|---|---|---|
| RF | 48 / 14 / 14 | 30 / 28 / 18 | 35 / 25 / 16 | **40 / 25 / 18** (83 RF) |
| RNF | 4 / 9 / 9 / 1 | 1 / 7 / 15 / 2 | 5 / 8 / 10 / 2 | **2 / 10 / 11 / 2** |
| UX | 22 cerrados / 6 abiertos | 22 / 10 | 24 / 8 | **24 / 8** |

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
| E1 | Aceptar asignación deja el curso Rechazado y sin facilitador (reproducido; working tree) | Crítica | RF-15 |
| E2 | El editor de cursos devuelve 500 por `$idPersona` indefinida (reproducido; también en HEAD) | Crítica | RF-12 |
| E3 | `update()` no comprueba el estado del curso (reproducido en Finalizado) | Alta | RF-12 |
| E4 | "Enviar a revisión" en Declinado, transición prohibida por el enum | Alta | RF-15 |
| E5 | Bloqueo de notas en Finalizado contradice la UI (introducido en Tier 1) | Alta | RF-39 |
| E6 | Asistencia, certificación y postulaciones sin control de estado del curso en backend | Alta | RF-31–RF-45 |
| E7 | Postulaciones pendientes quedan huérfanas al iniciar el curso | Media | RF-24 |
| E8 | Sin cancelación de curso desde Inscripción | Media | RF-15 |

El diseño objetivo del ciclo (13 estados, catálogo de 33 acciones con alcance Asignado / Titular / Global, reasignación como acción propia) se discutió en conversación y no forma parte de este documento todavía.

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
- Completar la UI de retiro por gestor (RF-27) y decidir D3 y D6.
- Corregir E1 y E2 antes de commitear; decidir la regla de E5, D7 y D8.
- Reejecutar tests, Pint y `route:list`; medir cobertura; repetir las métricas UX de §5.3.
- Revisar D2–D6.
- Mantener los IDs existentes; añadir nuevos al final (`RF-77+`, `RNF-26+`, `UX-36+`).

**Responsable:** equipo CINEFORM
**Última actualización:** 2026-10-23
