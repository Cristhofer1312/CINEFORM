# CINEFORM — Requisitos Funcionales y No Funcionales

> **Documento:** catálogo de requisitos para lectura y seguimiento
> **Fecha:** 20 de octubre de 2026
> **Alcance:** módulo `Taller` + `Security` + `Registro` + `Comun` (Laravel 10 / PostgreSQL 17)
> **Base:** código verificado al 20/10/2026 y documentación del repositorio
> **Estado:** vivo — se actualiza al cerrar cada fase

---

## Contenido

1. [Resumen](#1-resumen)
2. [Requisitos funcionales](#2-requisitos-funcionales)
   - [2.1 Acceso y seguridad](#21-acceso-y-seguridad)
   - [2.2 Cursos y ciclo de vida](#22-cursos-y-ciclo-de-vida)
   - [2.3 Inscripción](#23-inscripción)
   - [2.4 Asistencia](#24-asistencia)
   - [2.5 Calificaciones](#25-calificaciones)
   - [2.6 Certificación](#26-certificación)
   - [2.7 Facilitadores](#27-facilitadores)
   - [2.8 Satisfacción](#28-satisfacción)
   - [2.9 Estadísticas y administración](#29-estadísticas-y-administración)
   - [2.10 Público](#210-público)
3. [Inventario de brechas](#3-inventario-de-brechas)
4. [Requisitos no funcionales](#4-requisitos-no-funcionales)
5. [Trazabilidad](#5-trazabilidad)
6. [Notas de verificación](#6-notas-de-verificación)
7. [Próxima revisión](#7-próxima-revisión)

---

## Cómo leer este documento

### Estados

| Símbolo | Significado |
|---|---|
| ✅ | Implementado y verificado en código |
| ⚠️ | Implementado parcialmente, con brecha funcional o de seguridad |
| ❌ | No existe o está roto |

### Prioridades

| Prioridad | Criterio |
|---|---|
| **P0** | Bloqueante: seguridad, pérdida de datos o flujo esencial roto |
| **P1** | Núcleo del negocio: debe estar para considerar el sistema funcional |
| **P2** | Mejora de experiencia, operación o alcance |
| **P3** | Deseable / backlog |

### Fases

| Fase | Contenido |
|---|---|
| **0** | Contención: seguridad y bloqueos |
| **1** | Integridad funcional del negocio |
| **2** | Sistema de diseño (base visual) |
| **3** | Rediseño de pantallas |
| **4** | Operación y calidad |
| **—** | Ya implementado o sin trabajo asociado |

---

## 1. Resumen

### 1.1 Totales por estado

| Estado | Cantidad | % |
|---|---:|---:|
| ✅ Implementado | 43 | 56,6 % |
| ⚠️ Parcial | 18 | 23,7 % |
| ❌ Faltante | 15 | 19,7 % |
| **Total RF** | **76** | **100 %** |

### 1.2 Totales por módulo

| Módulo | RF | ✅ | ⚠️ | ❌ |
|---|---:|---:|---:|---:|
| Acceso y seguridad | 8 | 4 | 3 | 1 |
| Cursos y ciclo de vida | 12 | 7 | 4 | 1 |
| Inscripción | 12 | 6 | 4 | 2 |
| Asistencia | 7 | 5 | 1 | 1 |
| Calificaciones | 6 | 4 | 1 | 1 |
| Certificación | 8 | 4 | 2 | 2 |
| Facilitadores | 5 | 3 | 2 | 0 |
| Satisfacción | 4 | 3 | 0 | 1 |
| Estadísticas y administración | 10 | 6 | 1 | 3 |
| Público | 4 | 1 | 0 | 3 |
| **Total** | **76** | **43** | **18** | **15** |

---

## 2. Requisitos funcionales

### 2.1 Acceso y seguridad

| ID | Requisito | Estado | Prioridad | Fase |
|---|---|---|---|---|
| RF-01 | Registro público con verificación por código de email | ✅ | P1 | — |
| RF-02 | Login con captcha y throttle | ✅ | P0 | — |
| RF-03 | Recuperación de contraseña por token | ❌ | **P0** | 0 |
| RF-04 | Perfil multi-rol y cambio de perfil | ⚠️ | P1 | 1 |
| RF-05 | RBAC por proceso/acción en todas las rutas mutantes | ⚠️ | **P0** | 0 |
| RF-06 | Gestión de usuarios, perfiles y permisos | ✅ | P1 | — |
| RF-07 | Auditoría de accesos y acciones críticas | ⚠️ | P1 | 1 |
| RF-08 | Cierre de sesión y expiración | ✅ | P1 | — |

**Brechas principales**

- **RF-03:** el reset genera un token de 64 caracteres y lo guarda en `security.users.token varchar(50)`; PostgreSQL rechaza el `UPDATE`. El usuario no puede recuperar su contraseña.
- **RF-04:** el perfil activo vive en `session('profile_id')`, pero no se valida contra `security.profiles_users.status`; un perfil desactivado puede seguir operando hasta cerrar sesión.
- **RF-05:** solo `CatalogoController`, `UsersController` y `ProfilesController` usan el middleware `permiso:`. El resto de Taller valida permisos de forma ad hoc; hay endpoints sin ninguna verificación (administración de facilitadores).
- **RF-07:** existen campos de auditoría sueltos (`rechazada_por`, `certificado_aprobado_por`), pero no una tabla central de auditoría.

### 2.2 Cursos y ciclo de vida

| ID | Requisito | Estado | Prioridad | Fase |
|---|---|---|---|---|
| RF-09 | Wizard de 5 pasos con borrador persistente | ✅ | P1 | — |
| RF-10 | Código PROCINEC automático, único y estable | ✅ | P1 | — |
| RF-11 | Validación de trimestre, fechas y duración | ✅ | P1 | — |
| RF-12 | Temario con evaluaciones y ponderaciones | ✅ | P1 | — |
| RF-13 | Plantilla y firma de certificado | ✅ | P1 | — |
| RF-14 | Requisitos de inscripción | ✅ | P1 | — |
| RF-15 | Transiciones de estado validadas | ✅ | P1 | — |
| RF-16 | Reasignación de facilitador | ⚠️ | P1 | 1 |
| RF-17 | Duplicar curso / reutilizar plantilla | ❌ | P2 | 3 |
| RF-18 | Archivar o eliminar borradores | ⚠️ | P2 | 1 |
| RF-19 | Vista previa del curso antes de publicar | ⚠️ | P2 | 3 |
| RF-20 | Historial de estados con motivo y autor | ⚠️ | P1 | 1 |

**Brechas principales**

- **RF-16:** `reasignarFacilitador` debe garantizar `rollback` en todos los retornos posteriores a `beginTransaction` y registrar el motivo.
- **RF-18:** el comando `taller:limpiar-wizards` limpia borradores de más de 7 días, pero no hay una acción visible para que el usuario archive o elimine su borrador.
- **RF-20:** `curso_estado` ya tiene columna `motivo`, pero no todas las transiciones la exigen ni la muestran.

### 2.3 Inscripción

| ID | Requisito | Estado | Prioridad | Fase |
|---|---|---|---|---|
| RF-21 | Catálogo de cursos disponibles con filtros | ⚠️ | P1 | 3 |
| RF-22 | Detalle con cupos, fechas, modalidad y requisitos | ✅ | P1 | — |
| RF-23 | Postulación con preguntas y documentos | ✅ | P1 | — |
| RF-24 | Aprobar, rechazar y denegar postulaciones | ✅ | P1 | — |
| RF-25 | Corrección y rehabilitación | ⚠️ | P1 | 1 |
| RF-26 | Cancelación por el participante | ⚠️ | P1 | 1 |
| RF-27 | Retiro por gestor con motivo | ✅ | P1 | — |
| RF-28 | Lista de espera | ❌ | P2 | 4 |
| RF-29 | Notificación de cambio de estado | ❌ | P1 | 1 |
| RF-30 | Validación de documentos (tipo, tamaño, nombre) | ⚠️ | P0 | 1 |
| RF-75 | Carta de permiso (autorización de menores) como documento obligatorio solo en cursos para niños y adolescentes | ✅ | P1 | — |
| RF-76 | Plantilla de la carta de permiso descargable en “Documentos auxiliares” en cursos para menores | ✅ | P1 | — |

> **Incorporación 2026-10-20:** RF-75 y RF-76 se numeran al final para no alterar los IDs existentes. Se detallan en la sección 2.3.1.

**Brechas principales**

- **RF-21:** el catálogo existe solo para usuarios autenticados; el root redirige al login. No hay portal público.
- **RF-25:** `rehabilitar()` pasa la inscripción a `aprobado`, saltando la revisión; además no tiene botón en la interfaz.
- **RF-26:** la cancelación del participante hace `delete()` físico; la rama administrativa marca `rechazado`. Inconsistente y sin auditoría.
- **RF-29:** no se notifica al participante cuando su postulación cambia de estado; solo hay correos en certificación y postulación a facilitador.
- **RF-30:** las subidas solo validan `isValid()`; no hay MIME real, tamaño máximo ni nombre único. Dos requisitos con el mismo título se sobrescriben.

#### 2.3.1 Carta de permiso (incorporación 2026-10-20)

**Regla de negocio:** solo las inscripciones a cursos dirigidos a **menores de edad (niños y adolescentes)** deben solicitar la **Carta de permiso (Autorización de menores)** como documento obligatorio. La condición es a nivel del curso y se determina por su **público objetivo** (`taller.modalidades_especiales`: Niño o Adolescente), no por la edad del participante. La plantilla debe estar disponible en la parte superior de la pantalla de inscripción, bajo el bloque **“Documentos auxiliares”**, con descarga directa.

**Implementación:**

- Catálogo de público objetivo: `taller.modalidades_especiales.es_menor` marca **Niño** y **Adolescente** como menores; **Adulto** y **General** no lo son. La migración `2026_10_20_000000_add_es_menor_to_modalidades_especiales` agrega y completa el flag.
- Plantilla pública: `app/public/documentos/Autorizacion_menores.docx`, descargable en `documentos/Autorizacion_menores.docx`.
- Requisito por defecto: tipo `documento`, título `Carta de permiso (Autorización de menores)`, `obligatorio = true`; solo se crea para cursos con `es_menor = true`.
- Cursos existentes: la migración `2026_10_20_000001_add_carta_permiso_to_curso_requisitos` inserta la carta solo en cursos para menores. La migración `2026_10_20_000002_remove_carta_permiso_from_non_minor_courses` corrige instalaciones donde se agregó a todos y conserva como no obligatoria la evidencia ya cargada.
- Cursos nuevos: se precarga en el paso 5 del wizard y se reafirma en `finalizar()` solo si el público objetivo es Niño o Adolescente.
- Edición: el coordinador puede cambiar el público objetivo; el sistema agrega o retira la carta en consecuencia. Si el curso deja de ser para menores y ya hay documentos cargados, el requisito se conserva como no obligatorio para no perder la evidencia.
- El participante descarga la plantilla, la completa, la firma y la sube en PDF o imagen.
- El gestor o facilitador la revisa en **Requisitos del Participante**.

**Criterios de aceptación:**

1. En cursos para niños o adolescentes, la pantalla de inscripción muestra el bloque “Documentos auxiliares” antes de “Información Importante”.
2. El enlace descarga `Autorizacion_menores.docx`.
3. El formulario exige el documento antes de enviar la postulación solo en cursos para menores.
4. La postulación guarda el archivo y el revisor puede verlo y descargarlo.
5. El requisito no se duplica si el curso ya lo tenía.
6. Un curso para adultos o de público general no muestra el bloque ni exige la carta.

### 2.4 Asistencia

| ID | Requisito | Estado | Prioridad | Fase |
|---|---|---|---|---|
| RF-31 | Token QR temporal por actividad | ✅ | P1 | — |
| RF-32 | Marcado por enlace autenticado | ⚠️ | **P0** | 0 |
| RF-33 | Marcado manual por facilitador/gestor | ✅ | P1 | — |
| RF-34 | Anulación y restauración con motivo | ✅ | P1 | — |
| RF-35 | Consolidado por curso | ✅ | P1 | — |
| RF-36 | Vista individual por participante | ✅ | P1 | — |
| RF-37 | Regla de asistencia mínima para certificar | ❌ | P1 | 1 |

**Brechas principales**

- **RF-32:** `mostrarConfirmacion()` y `marcar()` no comparan el curso de la URL con `tokenRecord->actividad->id_curso`. Un participante del curso B puede usar un token del curso A. Además, aceptan cualquier estado de inscripción, no solo `aprobado`.
- **RF-37:** la certificación no exige un porcentaje mínimo de asistencia; el panel solo muestra el porcentaje como información.

### 2.5 Calificaciones

| ID | Requisito | Estado | Prioridad | Fase |
|---|---|---|---|---|
| RF-38 | Planilla de notas por evaluación | ✅ | P1 | — |
| RF-39 | Notas 0–100 con validación | ✅ | P1 | — |
| RF-40 | Promedio ponderado | ✅ | P1 | — |
| RF-41 | Visibilidad del promedio al participante (7/8/9) | ✅ | P1 | — |
| RF-42 | Exportación de acta / CSV | ❌ | P2 | 4 |
| RF-43 | Auditoría de cambios de nota | ⚠️ | P1 | 1 |

**Brechas principales**

- **RF-43:** solo se guarda `actualizado_en`; no se conserva el valor anterior ni el usuario que cambió la nota.
- **Validación pendiente:** `CalificacionController::store` no verifica que el contenido pertenezca al curso ni que sea una evaluación; el gestor no puede calificar (solo el facilitador).

### 2.6 Certificación

| ID | Requisito | Estado | Prioridad | Fase |
|---|---|---|---|---|
| RF-44 | Panel de elegibilidad (asistencia + nota + encuesta) | ⚠️ | P1 | 1 |
| RF-45 | Aprobación/denegación por participante | ✅ | P1 | — |
| RF-46 | Generación de PDF con QR local | ✅ | P1 | — |
| RF-47 | Descarga bloqueada hasta responder la encuesta | ✅ | P1 | — |
| RF-48 | Verificación pública | ✅ | P1 | — |
| RF-49 | Revocación y reemisión | ❌ | P2 | 4 |
| RF-50 | Emisión masiva | ❌ | P2 | 3 |
| RF-51 | Notificación con enlace de descarga | ⚠️ | P1 | 1 |

**Brechas principales**

- **RF-44:** el panel lista participantes y muestra asistencia/promedio, pero no aplica reglas de elegibilidad ni bloquea la aprobación si faltan requisitos.
- **RF-49:** no existe flujo para revocar un certificado emitido ni para reemitirlo con un motivo.
- **RF-51:** `Mail::queue` se ejecuta en línea porque `QUEUE_CONNECTION=sync`; no hay reintentos ni historial.
- **Guard faltante:** `CertificacionPanelController::aprobar/denegar` no exige que el curso esté en estado 8/9.

### 2.7 Facilitadores

| ID | Requisito | Estado | Prioridad | Fase |
|---|---|---|---|---|
| RF-52 | Landing y requisitos globales | ✅ | P1 | — |
| RF-53 | Postulación con documentos | ✅ | P1 | — |
| RF-54 | Revisión de documentos | ✅ | P1 | — |
| RF-55 | Aprobación → perfil Facilitador | ⚠️ | **P0** | 0 |
| RF-56 | Rechazo con motivo y repostulación | ⚠️ | P1 | 1 |

**Brechas principales**

- **RF-55:** los endpoints de administración no tienen RBAC; cualquier usuario autenticado puede aprobar su propia postulación. Además, el `id_rol=2` está hardcodeado y no hay restricción única que evite duplicados por concurrencia.
- **RF-56:** al rechazar se eliminan los documentos del storage con `@unlink`; el postulante pierde la evidencia y no se conserva historial.

### 2.8 Satisfacción

| ID | Requisito | Estado | Prioridad | Fase |
|---|---|---|---|---|
| RF-57 | Formulario de 4 preguntas (1–5) | ✅ | P1 | — |
| RF-58 | Una respuesta por inscripción | ✅ | P1 | — |
| RF-59 | Resultados agregados | ✅ | P1 | — |
| RF-60 | Exportación de resultados | ❌ | P2 | 4 |

**Brechas principales**

- **RF-60:** no hay exportación CSV/PDF de resultados.
- **Privacidad:** `resultados()` permite el acceso a cualquier autenticado que no sea participante del curso; conviene acotarlo por rol/permiso si los resultados son sensibles.

### 2.9 Estadísticas y administración

| ID | Requisito | Estado | Prioridad | Fase |
|---|---|---|---|---|
| RF-61 | KPIs globales | ✅ | P1 | — |
| RF-62 | Filtros por año, modalidad, actividad, aspecto y estado | ✅ | P1 | — |
| RF-63 | Gráficos | ✅ | P1 | — |
| RF-64 | Exportación de reportes | ❌ | P2 | 4 |
| RF-65 | Alcance por rol | ⚠️ | P1 | 1 |
| RF-66 | CRUD de catálogos | ✅ | P1 | — |
| RF-67 | CRUD de requisitos de facilitador | ✅ | P1 | — |
| RF-68 | CRUD de usuarios/perfiles | ✅ | P1 | — |
| RF-69 | Notificaciones in-app | ❌ | P2 | 4 |
| RF-70 | Auditoría centralizada | ❌ | P1 | 1 |

**Brechas principales**

- **RF-65:** el acceso se controla con un permiso global (`VER_ESTADISTICAS`); no hay alcance por facultad, localidad o programa.
- **RF-64:** no hay exportación de los datasets ni de los KPIs.
- **RF-70:** no existe una tabla de auditoría; solo logs de aplicación.

### 2.10 Público

| ID | Requisito | Estado | Prioridad | Fase |
|---|---|---|---|---|
| RF-71 | Portal público de cursos | ❌ | P1 | 3 |
| RF-72 | Verificación pública de certificados | ✅ | P1 | — |
| RF-73 | Compartir curso | ❌ | P2 | 3 |
| RF-74 | Contacto / soporte | ❌ | P3 | Backlog |

**Brechas principales**

- **RF-71:** el público no puede conocer la oferta; el root redirige a login.
- **RF-73:** no hay metadatos Open Graph ni botón de compartir.
- **RF-74:** no hay formulario de contacto ni canal de soporte visible.

---

## 3. Inventario de brechas

> Solo requisitos ⚠️ y ❌, ordenados por prioridad y fase. Es la lista de trabajo resumida.

| ID | Brecha | Impacto | Prioridad | Fase |
|---|---|---|---|---|
| RF-03 | Reset de contraseña roto por longitud de token | Usuarios bloqueados | **P0** | 0 |
| RF-05 | RBAC incompleto en rutas de Taller | Escalada de privilegios | **P0** | 0 |
| RF-32 | Asistencia sin binding de curso/estado | Datos falsos | **P0** | 0 |
| RF-55 | Admin de facilitadores sin autorización | Auto-aprobación | **P0** | 0 |
| RF-30 | Uploads sin MIME/tamaño | Archivos peligrosos | **P0** | 1 |
| RF-04 | Perfil activo no valida `status` | Acceso persistente | P1 | 1 |
| RF-07 | Auditoría de accesos parcial | Sin trazabilidad | P1 | 1 |
| RF-16 | Reasignación sin rollback/motivo | Estados inconsistentes | P1 | 1 |
| RF-20 | Historial sin motivo obligatorio | Poca trazabilidad | P1 | 1 |
| RF-25 | Rehabilitación salta revisión | Admisión indebida | P1 | 1 |
| RF-26 | Cancelación con borrado físico | Pérdida de auditoría | P1 | 1 |
| RF-29 | Sin notificación de estado | Incertidumbre | P1 | 1 |
| RF-37 | Sin asistencia mínima | Certificación indebida | P1 | 1 |
| RF-43 | Sin auditoría de notas | Fraude/errores | P1 | 1 |
| RF-44 | Sin reglas de elegibilidad | Certificación indebida | P1 | 1 |
| RF-51 | Correos síncronos | Pérdida de avisos | P1 | 1 |
| RF-56 | Rechazo borra documentos | Pérdida de evidencia | P1 | 1 |
| RF-65 | Estadísticas sin alcance por rol | Exceso de visibilidad | P1 | 1 |
| RF-70 | Sin auditoría central | Sin trazabilidad | P1 | 1 |
| RF-17 | Sin duplicar curso | Trabajo manual | P2 | 3 |
| RF-18 | Sin archivar borrador | Desorden | P2 | 1 |
| RF-19 | Vista previa parcial | Publicación a ciegas | P2 | 3 |
| RF-21 | Catálogo solo autenticado | Baja difusión | P1 | 3 |
| RF-28 | Sin lista de espera | Oportunidades perdidas | P2 | 4 |
| RF-42 | Sin exportar acta | Trabajo manual | P2 | 4 |
| RF-49 | Sin revocación/reemisión | Certificados inválidos | P2 | 4 |
| RF-50 | Sin emisión masiva | Cuello de botella | P2 | 3 |
| RF-60 | Sin exportar encuesta | Análisis manual | P2 | 4 |
| RF-64 | Sin exportar reportes | Análisis manual | P2 | 4 |
| RF-69 | Sin notificaciones in-app | Baja comunicación | P2 | 4 |
| RF-71 | Sin portal público | Baja conversión | P1 | 3 |
| RF-73 | Sin compartir curso | Baja difusión | P2 | 3 |
| RF-74 | Sin contacto/soporte | Mala experiencia | P3 | Backlog |

---

## 4. Requisitos no funcionales

| ID | Categoría | Requisito | Criterio medible | Prioridad | Fase |
|---|---|---|---|---|---|
| RNF-01 | Seguridad | Política única de contraseñas | Mín. 10 caracteres, Argon2id/bcrypt, sin reutilización | P0 | 0 |
| RNF-02 | Seguridad | RBAC en el 100 % de rutas mutantes | Test negativo por cada ruta crítica | P0 | 0 |
| RNF-03 | Seguridad | Uploads seguros | Máx. 10 MB, MIME real permitido, nombre UUID, storage privado | P0 | 1 |
| RNF-04 | Seguridad | Secretos fuera de git | `.env.example` sin valores reales; rotación documentada | P0 | 0 |
| RNF-05 | Seguridad | HTTPS + headers | HSTS, CSP, X-Frame-Options, nosniff | P1 | 4 |
| RNF-06 | Auditoría | Log inmutable de acciones críticas | 100 % de cambios de estado, notas y certificados | P1 | 1 |
| RNF-07 | Rendimiento | Listados | p95 < 500 ms con 10.000 cursos | P1 | 4 |
| RNF-08 | Rendimiento | PDF de certificado | < 2 s por certificado | P1 | 4 |
| RNF-09 | Rendimiento | Dashboard | < 1 s con caché | P2 | 4 |
| RNF-10 | Escalabilidad | Paginación obligatoria | Ningún endpoint devuelve > 100 registros sin paginar | P1 | 1 |
| RNF-11 | Escalabilidad | Índices | Consultas críticas sin `seq scan` | P1 | 1 |
| RNF-12 | Disponibilidad | Operación | 99,5 % mensual; healthchecks | P1 | 4 |
| RNF-13 | Disponibilidad | Respaldos | Diario, RPO 24 h, RTO 4 h, restauración probada | P0 | 4 |
| RNF-14 | Colas | Correos y PDFs asíncronos | `QUEUE_CONNECTION=database/redis`, worker supervisado, 3 reintentos | P1 | 1 |
| RNF-15 | Usabilidad | Accesibilidad | WCAG 2.1 AA; contraste 4,5:1; foco visible | P1 | 2 |
| RNF-16 | Usabilidad | Responsive | 360 px a 1920 px sin scroll horizontal | P1 | 2 |
| RNF-17 | Usabilidad | Consistencia | Fechas `d/m/Y`, números `es-VE`, mensajes en español | P2 | 2 |
| RNF-18 | Compatibilidad | Navegadores | Chrome, Edge, Firefox y Safari últimas 2 versiones | P2 | 4 |
| RNF-19 | Mantenibilidad | Estilo | PSR-12 + Pint en verde | P1 | 0 |
| RNF-20 | Mantenibilidad | Análisis estático | PHPStan nivel 5 en servicios críticos | P2 | 4 |
| RNF-21 | Pruebas | Cobertura | ≥ 70 % en servicios de negocio; E2E de flujos | P1 | 1 |
| RNF-22 | Observabilidad | Logs y métricas | Logs estructurados + Sentry + alertas de cola | P2 | 4 |
| RNF-23 | Legal | Datos personales | Consentimiento, retención y derecho de acceso | P1 | 4 |
| RNF-24 | i18n | Idioma | Español base; inglés opcional | P3 | Backlog |
| RNF-25 | Documentación | Operación | README, runbook y ADRs actualizados | P2 | 4 |

### 4.1 Criterios de aceptación globales

Un requisito se considera cerrado cuando:

1. Tiene prueba automatizada (feature o unit) o prueba manual documentada.
2. Pasa `vendor/bin/pint --test`.
3. No introduce secretos, `eval` con input, `@` silenciosos ni consultas sin esquema.
4. Cumple accesibilidad básica en las pantallas tocadas.
5. Tiene rollback documentado si incluye migración de datos.

### 4.2 Requisitos no funcionales críticos (P0)

| ID | Por qué es P0 | Verificación |
|---|---|---|
| RNF-01 | Contraseñas débiles permiten toma de cuentas | Test de validación + revisión de configuración |
| RNF-02 | Un endpoint sin RBAC puede escalar privilegios | Matriz ruta × perfil con respuesta esperada |
| RNF-03 | Un upload sin validación puede ejecutar código | Prueba con `.php`, `.phtml`, archivo > 10 MB |
| RNF-04 | Credenciales expuestas comprometen todo el sistema | Rotación + `git log` sin secretos |
| RNF-13 | Sin respaldo, un error de migración es irrecuperable | Restauración cronometrada en staging |

---

## 5. Trazabilidad

### 5.1 RF → proceso de negocio

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

### 5.2 RF → fase

| Fase | RF principales |
|---|---|
| 0 | RF-03, RF-05, RF-32, RF-55 |
| 1 | RF-04, RF-07, RF-16, RF-18, RF-20, RF-25, RF-26, RF-29, RF-30, RF-37, RF-43, RF-44, RF-51, RF-56, RF-65, RF-70 |
| 2 | Base visual para todos los RF de interfaz (sin RF propio) |
| 3 | RF-09, RF-17, RF-19, RF-21, RF-22, RF-25, RF-26, RF-29, RF-31 a RF-36, RF-38 a RF-42, RF-44 a RF-48, RF-50, RF-57 a RF-59, RF-61 a RF-63, RF-71, RF-73 |
| 4 | RF-28, RF-42, RF-49, RF-60, RF-64, RF-69 |
| Backlog | RF-74 (contacto); RNF-24 (i18n) opcional |

> Las fases 2 y 3 no “crean” requisitos: materializan los existentes en una interfaz usable y consistente.

---

## 6. Notas de verificación

- **Fuente de verdad del estado:** código actual en `app/`, no los documentos de planificación. Varios bugs de `analisis_viabilidad_corregido.md` ya están corregidos (estado 9, filtro `id_estado`, columna `motivo`, `aprobar/rechazar/denegar`, QR local, HMAC, autorización de plantilla, UNIQUE de `codigo`).
- **Certificado:** no es una tabla; es el estado de la inscripción (`certificado_aprobado`, fechas, motivo) + archivos en `storage/app/public/Certificados/` + verificación pública.
- **Estado del curso:** último registro de `taller.curso_estado`; no hay columna de estado en `taller.cursos`.
- **Colas:** `QUEUE_CONNECTION=sync` en `.env` y `.env.example`; `Mail::queue` se ejecuta en línea.
- **Tests:** 18 unitarios; suite completa 23 tests con 1 fallo conocido (`ExampleTest` espera 200 en `/` y recibe 302).
- **Carta de permiso (2026-10-20):** el requisito obligatorio `Carta de permiso (Autorización de menores)` y el bloque “Documentos auxiliares” aplican **solo a cursos dirigidos a niños y adolescentes**. La condición se resuelve por curso con `taller.modalidades_especiales.es_menor` (Niño/Adolescente), no por la edad del participante; `comun.personas` no tiene fecha de nacimiento. La plantilla vive en `app/public/documentos/Autorizacion_menores.docx`. La migración `2026_10_20_000001_add_carta_permiso_to_curso_requisitos` inserta la carta solo en cursos para menores y `2026_10_20_000002_remove_carta_permiso_from_non_minor_courses` limpia los cursos que la hubieran recibido por error.
- **Prueba añadida:** `app/tests/Unit/Taller/CursoRequisitoCartaPermisoTest.php` (6 casos) cubre la definición, la indicación de la condición en la descripción, la detección por título y el filtro por tipo `documento`. No se pudo ejecutar en el host por falta de `mbstring`; debe correrse dentro del contenedor `app`.

---

## 7. Próxima revisión

Actualizar este documento al cerrar cada fase, marcando:

- Estado final del RF (✅/⚠️/❌).
- Fase real de cierre.
- Enlace al commit/PR y a la prueba.
- Fecha de verificación.

**Responsable:** equipo CINEFORM
**Última actualización:** 2026-10-20
