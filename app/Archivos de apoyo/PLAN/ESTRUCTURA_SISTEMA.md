# ESTRUCTURA DEL SISTEMA CINEFORM

> **Archivo unificado de referencia para comprender la arquitectura, funcionalidades, patrones de código y convenciones del sistema completo.**
> **Objetivo:** Permitir entender toda la información del proyecto sin necesidad de re-leer el código fuente.
> **Fuentes consolidadas:** CONTEXTO_SISTEMA.md, CONTEXTO_TECNICO.md, CAMBIOS_IMPLEMENTADOS.md, MIGRACIONES_REFERENCIA.md, REFORMA_ACTIVIDADES_ASISTENCIA.md, Responsables/
> **Última actualización:** 2026-07-20

---

## Tabla de Contenidos

1. [Descripción General y Stack Tecnológico](#1-descripción-general-y-stack-tecnológico)
2. [Arquitectura Modular](#2-arquitectura-modular)
3. [Base de Datos — Esquemas, Tablas y Relaciones](#3-base-de-datos)
4. [Sistema de Autenticación y Seguridad (RBAC)](#4-sistema-de-autenticación-y-seguridad-rbac)
5. [Ciclo de Vida del Curso](#5-ciclo-de-vida-del-curso)
6. [Sistema de Inscripciones](#6-sistema-de-inscripciones)
7. [Sistema de Asistencia](#7-sistema-de-asistencia)
8. [Sistema de Certificados](#8-sistema-de-certificados)
9. [Patrones de Código y Convenciones](#9-patrones-de-código-y-convenciones)
10. [Frontend — Layouts, Componentes y Vistas](#10-frontend)
11. [Rutas Principales por Módulo](#11-rutas-principales)
12. [Reglas de Codificación Obligatorias](#12-reglas-de-codificación-obligatorias)
13. [Conexiones de Base de Datos](#13-conexiones-de-base-de-datos)
14. [Dependencias PHP](#14-dependencias-php)
15. [Errores Comunes y Lecciones Aprendidas](#15-errores-comunes-y-lecciones-aprendidas)
16. [Archivos de Apoyo y Backup](#16-archivos-de-apoyo-y-backup)
17. [Comandos Útiles de Referencia](#17-comandos-útiles)

---

## 1. Descripción General y Stack Tecnológico

### 1.1 ¿Qué es CINEFORM?

**CINEFORM** (Cine + Formación) es una plataforma web para la **gestión integral de formación cinematográfica en Venezuela**. Permite:

- Planificar cursos/talleres de formación cinematográfica
- Gestionar inscripciones de participantes con workflow de aprobación
- Registrar asistencia vía enlace/QR o marcado manual
- Calificar contenido evaluativo
- Emitir certificados con código QR de verificación
- Administrar usuarios con un sistema RBAC (Role-Based Access Control) completo
- Gestionar catálogos (actividades formativas, aspectos de formación, etc.)

### 1.2 Stack Tecnológico Completo

| Capa | Tecnología | Detalle |
|------|-----------|---------|
| **Backend** | Laravel 10.x | PHP ^8.1, framework MVC |
| **Frontend** | Blade templates | Bootstrap (tema KaiAdmin) |
| **Build** | Vite 5.x | Bundler de assets |
| **Base de datos** | PostgreSQL 17 | 5 schemas: `security`, `comun`, `taller`, `registro`, `parametros` |
| **Containerización** | Docker + Docker Compose | 4 servicios |
| **Web server** | Nginx | Reverse proxy al PHP-FPM (puerto 9000) |
| **DB Admin** | pgAdmin4 | Puerto 8081 |
| **PDF** | FPDF + smalot/pdfparser | Generación y lectura de PDFs |
| **Imágenes** | Intervention Image + ImageMagick | Manipulación de imágenes, marcas de agua |
| **Excel** | Maatwebsite/Excel 3.1.58 | Exportaciones a Excel |
| **DataTables** | Yajra Laravel DataTables | Listados paginados con AJAX |
| **Forms** | Spatie Laravel HTML | Construcción de formularios |
| **CAPTCHA** | Biscolab Laravel reCAPTCHA | Protección en login/registro |
| **HTML Purifier** | Mews Purifier | Limpieza de HTML (XSS prevention) |
| **Auth tokens** | Laravel Sanctum | Tokens API (disponible, no activo en uso principal) |
| **Modular** | nwidart/laravel-modules | Arquitectura modular (5 módulos) |

### 1.3 Infraestructura Docker

```
┌─────────────────────────────────────────────────────────────┐
│                      docker-compose.yml                      │
├─────────────┬──────────────┬────────────────────────────────┤
│    app      │   webserver  │    db (PostgreSQL 17)          │
│  PHP-FPM    │   Nginx:80   │    Puerto: 5432               │
│  Puerto:9000│  Puerto:8080 │    DB: cineform               │
│  Xdebug     │              │    User: laravel              │
├─────────────┴──────────────┴────────────────────────────────┤
│               pgAdmin4 (puerto 8081)                         │
│               admin@admin.com / admin                        │
└─────────────────────────────────────────────────────────────┘
```

- `./app` se monta como volumen compartido entre `app` y `webserver`
- Nginx escucha en `localhost:8080`, redirige al FastCGI de `app:9000`
- La conexión a BD usa el `search_path`: `public,security,comun,parametros,registro,taller`

---

## 2. Arquitectura Modular

El proyecto usa **nwidart/laravel-modules** con 5 módulos habilitados en `modules_statuses.json`:

### 2.1 Módulos del Sistema

| Módulo | Propósito | Controllers | Entities (Modelos) |
|--------|-----------|-------------|---------------------|
| **Security** | Autenticación, usuarios, perfiles, permisos RBAC | SecurityController, UsersController, ProfilesController | User, Profile, ProfileUser, Permission, Process, Menu, Modulo, Codes, Countries, DocumentType, Genders, ProfilePermission, ProfileProcesse, Saime |
| **Comun** | Datos personales, catálogos geográficos compartidos | ComunController, PersonalDataController | PersonalData, Especializacion |
| **Taller** | Core del negocio: cursos, inscripciones, asistencia, calificaciones, certificados | CursoController, CrearCursoController, EditarCursoController, CursoAsignadoController, CursoInscritoController, CursoDetalleController, InscripcionController, CalificacionController, CertificadoController, CatalogoController, CursoRequisitoController, BaseController, AsistenciaController | Curso, ContenidoCurso, Inscripcion, InscripcionRespuesta, CursoRequisito, ObservacionCurso, Estado, Modalidad, ActividadFormativa, Aspecto, ModalidadEspecial, TipoEvaluacion, Asistencia, AsistenciaToken |
| **Registro** | Registro público de usuarios, asignación de perfiles | RegisterController, PersonaPerfilController | (usa PersonalData de Comun) |
| **Parametros** | Parámetros geográficos (estados, municipios, parroquias, carreras) | ParametrosController | Estados, Municipios, Parroquias, Carreras, NivelesEducacion |

### 2.2 Estructura de Carpetas de Cada Módulo

```
Modules/{Modulo}/
├── Config/
├── Console/
├── Database/
│   ├── factories/
│   ├── Migrations/
│   └── Seeders/
├── Entities/              ← Modelos Eloquent (NO en app/Models/)
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Providers/             ← RouteServiceProvider + ServiceProvider
├── Resources/
│   ├── assets/
│   ├── lang/
│   └── views/             ← Blade templates del módulo
├── Routes/
│   ├── api.php
│   └── web.php
├── Services/              ← Solo Taller (lógica de negocio)
├── Tests/
└── vite.config.js
```

### 2.3 Árbol de Directorios del Proyecto Completo

```
CINEFORM/
├── .devcontainer/                    ← Config devcontainer
├── docker-compose.yml                ← Orquestación Docker
├── Dockerfile                        ← Build de la imagen PHP
├── nginx/nginx.conf                  ← Config Nginx
├── Archivos de apoyo/               ← Documentación y backups
│   ├── PLAN/
│   │   ├── ESTRUCTURA_SISTEMA.md    ← ESTE ARCHIVO
│   │   ├── CONTEXTO_SISTEMA.md      ← Contexto general del sistema
│   │   ├── CONTEXTO_TECNICO.md      ← Contexto técnico del código
│   │   ├── CAMBIOS_IMPLEMENTADOS.md ← Cambios de asistencia implementados
│   │   ├── MIGRACIONES_REFERENCIA.md← Migraciones consolidadas
│   │   └── REFORMA_ACTIVIDADES_ASISTENCIA.md ← Plan original de asistencia
│   ├── backup_migrations_modulos/   ← 38 archivos de migración originales de módulos
│   ├── backup_migrations_app/       ← 9 archivos de migración originales de app
│   └── Responsables/                ← Código fuente de referencia (asistencia)
└── app/                              ← Código Laravel
    ├── artisan
    ├── artisan.sql                   ← Posible dump SQL del esquema
    ├── composer.json
    ├── modules_statuses.json         ← Módulos habilitados
    ├── app/
    │   ├── Constants/SecurityAction.php   ← RBAC centralizado
    │   ├── Enums/EstadoCurso.php          ← Enum de estados del curso
    │   ├── Helpers/Helpers.php            ← Funciones globales
    │   ├── Helpers/Encryptor.php          ← Cifrado de IDs
    │   ├── Helpers/LockDB.php             ← (NO MODIFICAR - ofuscado)
    │   ├── Observers/CursoObserver.php    ← Generación código PROCINEC
    │   ├── Traits/EncryptationId.php      ← Trait de IDs encriptados
    │   └── Http/                          ← Controllers base
    ├── database/
    │   └── seeders/DatabaseSeeder.php     ← Orquestador de seeders (25 archivos)
    ├── resources/views/layouts/           ← Templates base
    └── Modules/
        ├── Security/    ← Auth, RBAC, usuarios
        ├── Comun/       ← Datos personales, catálogos compartidos
        ├── Taller/      ← Core del negocio
        ├── Registro/    ← Registro público, asignación de perfiles
        └── Parametros/  ← Geografía Venezuela
```

---

## 3. Base de Datos — Esquemas, Tablas y Relaciones

### 3.1 Los 5 Schemas PostgreSQL (en orden topológico)

```
ESQUEMA       TABLAS
──────────    ──────────────────────────────────────────────────
security      genders, countries, document_types, modules,
              codes, menus, processes, profiles, users,
              permissions, profile_permissions, profiles_users

comun         estados, municipios, parroquias,
              especializaciones, niveles_educacion, carreras,
              personas, personas_educacion, personas_especializacion

taller        modalidad, tipo_evaluaciones, estados_curso,
              aspectos, actividades_formativas,
              modalidades_especiales, cursos, contenido_cursos,
              inscripciones, curso_estado, calificaciones,
              observaciones_curso, curso_localidades,
              curso_requisitos, inscripcion_respuestas,
              asistencias, asistencia_tokens

registro      (sin tablas propias, usa comun.personas)

parametros    (estados/municipios/parroquias bajo comun)
```

### 3.2 Orden Topológico de Creación de Tablas (36 tablas)

```
Orden  Tabla                          Schema       Dependencias
─────  ─────────────────────────────  ───────────  ──────────────────────────
 1     genders                        security     (ninguna)
 2     countries                      security     (ninguna)
 3     document_types                 security     (ninguna)
 4     modules                        security     (ninguna)
 5     estados                        comun        (ninguna)
 6     codes                          security     (ninguna)
 7     modalidad                      taller       (ninguna)
 8     tipo_evaluaciones              taller       (ninguna)
 9     estados_curso                  taller       (ninguna)
10     aspectos                       taller       (ninguna)
11     actividades_formativas         taller       (ninguna)
12     modalidades_especiales         taller       (ninguna)
13     especializaciones              comun        (ninguna)
14     niveles_educacion              comun        (ninguna)
15     carreras                       comun        (ninguna)
16     menus                          security     → modules
17     processes                      security     → menus
18     profiles                       security     (ninguna)
19     users                          security     (ninguna, sin profile_id)
20     municipios                     comun        → estados
21     parroquias                     comun        → municipios
22     permissions                    security     → processes
23     profile_permissions            security     → profiles, permissions
24     profiles_users                 security     → profiles, users
25     personas                       comun        → users, document_types, genders,
                                                     countries, estados, municipios, parroquias
26     cursos                         taller       → modalidad, personas, aspectos,
                                                     actividades_formativas, modalidades_especiales
27     contenido_cursos               taller       → cursos, tipo_evaluaciones
28     inscripciones                  taller       → cursos, personas
29     curso_estado                   taller       → cursos, estados_curso
30     calificaciones                 taller       → cursos, contenido_cursos, personas
31     observaciones_curso            taller       → cursos
32     curso_localidades              taller       → cursos, estados
33     curso_requisitos               taller       → cursos
34     inscripcion_respuestas         taller       → inscripciones, curso_requisitos
35     personas_educacion             comun        → personas, niveles_educacion, carreras
36     personas_especializacion       comun        → personas, especializaciones
```

### 3.3 Migración Consolidada

- Un solo archivo `2026_06_21_000000_full_schema.php` crea todo el schema
- 36 tablas en orden con FK constraints, índices y unique constraints
- Backups originales en `backup_migrations_modulos/` (38 archivos) y `backup_migrations_app/` (9 archivos)

### 3.4 Tablas Clave y Campos Nullable (CRITICO)

> **REGLA:** Siempre verificar los campos nullable antes de llamar métodos sobre ellos en Blade/PHP.
> Si un campo tiene `->nullable()` en la migración, DEBE tener un `@if` o `??` en las vistas.

#### `taller.contenido_cursos` (Actividades)

| Columna | Tipo | Nullable | Cast Eloquent | Notas |
|---------|------|----------|---------------|-------|
| `id_contenido_curso` | bigint PK | NO | - | PK descriptiva |
| `id_curso` | bigint FK | NO | - | FK → `taller.cursos.id_curso` |
| `titulo` | varchar | NO | - | |
| `descripcion_breve` | text | NO | - | |
| `descripcion` | text | NO | - | |
| `url_contenido` | varchar | **SÍ** | - | Puede ser null si no hay enlace |
| `es_evaluacion` | boolean | NO | - | Default: `false` |
| `id_tipo_evaluacion` | bigint FK | **SÍ** | - | Solo si `es_evaluacion = true` |
| `ponderacion` | decimal(5,2) | **SÍ** | - | Solo si `es_evaluacion = true` |
| `fecha_contenido` | date | **SÍ** | `'date'` | **PUEDE SER NULL.** NUNCA llamar `->format()` sin verificar |
| `creado_por` | bigint | NO | - | |
| `creado_en` | timestamp | NO | - | |
| `actualizado_por` | bigint | **SÍ** | - | |
| `actualizado_en` | timestamp | NO | - | |

#### `taller.asistencias`

| Columna | Tipo | Nullable | Notas |
|---------|------|----------|-------|
| `id_asistencia` | bigint PK | NO | |
| `id_contenido_curso` | bigint FK | NO | FK → `contenido_cursos` |
| `id_persona` | bigint FK | NO | FK → `comun.personas` |
| `id_inscripcion` | bigint FK | NO | FK → `taller.inscripciones` |
| `fecha_hora_marcado` | timestamp | NO | |
| `activa` | boolean | NO | Default: `true` |
| `anulada_por` | bigint | **SÍ** | |
| `fecha_anulacion` | timestamp | **SÍ** | |
| `motivo_anulacion` | text | **SÍ** | |
| `ip_marcado` | varchar(45) | **SÍ** | |
| `metodo_marcado` | varchar(10) | NO | Default: `'link'` |
| `creado_en` | timestamp | NO | |
| `actualizado_en` | timestamp | NO | |

> **IMPORTANTE:** La tabla `taller.asistencias` **NO tiene columna `id_curso`**.
> La relación con el curso es **INDIRECTA** a través de:
> `asistencias.id_contenido_curso → contenido_cursos.id_contenido_curso → contenido_cursos.id_curso`

#### `taller.asistencia_tokens`

| Columna | Tipo | Nullable | Notas |
|---------|------|----------|-------|
| `id_token` | bigint PK | NO | |
| `id_contenido_curso` | bigint FK | NO | FK → `contenido_cursos` |
| `token` | varchar(64) | NO | UNIQUE |
| `activo` | boolean | NO | Default: `true` |
| `fecha_expiracion` | timestamp | **SÍ** | |
| `creado_por` | bigint | NO | |
| `creado_en` | timestamp | NO | |
| `actualizado_en` | timestamp | NO | |

### 3.5 Mapa de Relaciones Indirectas (CRITICO)

```
asistencias ─── id_contenido_curso ──→ contenido_cursos ─── id_curso ──→ cursos
asistencia_tokens ─ id_contenido_curso ─→ contenido_cursos ─── id_curso ──→ cursos
calificaciones ─── id_contenido_curso ──→ contenido_cursos ─── id_curso ──→ cursos
```

> **REGLA:** Para filtrar asistencias por curso, usar los IDs de contenido, NO filtrar directamente por `id_curso`.

### 3.6 Seeders (25 archivos en `database/seeders/`)

Orquestados por `DatabaseSeeder.php`:

| # | Seeder | Contenido |
|---|--------|-----------|
| 1 | GendersSeeder | Masculino, Femenino |
| 2 | CountriesSeeder | 240+ países (Venezuela default=1) |
| 3 | DocumentTypesSeeder | V, E, P, V(Firma), J, G, C |
| 4 | ModulesMenusSeeder | 1 módulo + 3 menús (Seguridad, Formación, Administración) |
| 5 | ProcessesSeeder | 7 procesos del sistema |
| 6 | ProfilesSeeder | Administrator, Facilitador, Participante, Coordinador |
| 7 | UsersSeeder | Admin (username: admin, email: crisclasyt@gmail.com) |
| 8 | ProfilesUsersSeeder | Admin user → todos los perfiles |
| 9 | PermissionsSeeder | Todos los permisos por proceso (incluye process_id 8 y 9 de asistencia) |
| 10 | ProfilePermissionsSeeder | Mapeo perfil→permisos (Admin, Coordinador, Facilitador, Participante) |
| 11 | EstadosGeografiaSeeder | 24 estados + 335 municipios + ~1100 parroquias |
| 12 | NivelesEducacionSeeder | 6 niveles educativos |
| 13 | CarrerasSeeder | ~98 carreras profesionales |
| 14 | EspecializacionesSeeder | 3 especializaciones (Cinematografía, Edición, Iluminación) |
| 15 | ModalidadSeeder | Presencial, Virtual, Híbrida |
| 16 | TipoEvaluacionesSeeder | Examen, Exposición, Trabajo |
| 17 | EstadosCursoSeeder | 9 estados del flujo (Por_aceptar → Cerrado) |
| 18 | ActividadesFormativasSeeder | 7 tipos (Taller, Foro, Simposio, etc.) |
| 19 | ModalidadesEspecialesSeeder | Niño, Adolescente, Adulto |
| 20 | AspectosSeeder | 26 aspectos cinematográficos |
| 21 | CursoEjemploSeeder | Curso demo con contenidos (existente) |
| 22 | PersonasSeeder | Datos de persona demo |
| 23-25 | (Otros) | Seeders adicionales |

---

## 4. Sistema de Autenticación y Seguridad (RBAC)

### 4.1 Login

- **Ruta:** `/security/login` (rate-limit: 10 intentos/min por IP)
- **Soporta CAPTCHA** (reCAPTCHA de Google + captcha customizado)
- **Recuperación de contraseña** por token vía email (token SHA-256, validez 30 min)
- **Registro** con código de verificación por email (6 dígitos, validez 15 min)
- **Post-login:** `redirect()->intended(route('home'))` — redirige a la ruta que el usuario intentaba acceder

### 4.2 Sesión y Perfiles

- Al logearse, el usuario **selecciona un perfil** (`profile_id` en sesión)
- Un usuario puede tener múltiples perfiles (tabla pivote `profiles_users`)
- El `profile_id` de sesión controla qué menús y permisos ve
- Si tiene 1 solo perfil, se asigna automáticamente
- Si tiene múltiples, se muestra pantalla de selección

### 4.3 Modelo RBAC (Role-Based Access Control)

```
┌────────────┐     ┌──────────────┐     ┌────────────────┐
│  processes │────→│  permissions │←────│    profiles     │
│  (sección) │     │  (acciones)  │     │  (roles/Perfil) │
└────────────┘     └──────┬───────┘     └────────┬───────┘
                          │                      │
                          │    ┌─────────────────┘
                          │    │
                   ┌──────▼────▼───────┐
                   │ profile_permissions│  ← Tabla pivote
                   └───────────────────┘
```

**Flujo de verificación:**
1. Usuario hace request → middleware `'permiso:users,1'` o `'auth'`
2. Controller verifica: `hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO)`
3. Obtiene `profile_id` de sesión
4. Convierte `actionId` → slug via `SecurityAction::dbString()`
5. Query: `permissions.process_id = X AND profile_permissions.profile_id = session_profile AND permissions.slug = 'manage_course'`
6. → EXISTS? → permitido / denegado

### 4.4 SecurityAction — Mapeo Centralizado de 22+ Acciones

| ID | Constante | Slug DB | Uso principal |
|----|-----------|---------|---------------|
| 1 | VER | `view` | Acceso a sección |
| 2 | CREAR | `create` | Crear registros |
| 3 | CREAR_CURSO | `create_course` | Planificar curso |
| 4 | GESTIONAR_CURSO | `manage_course` | Coordinador |
| 5 | EDITAR_CURSO | `edit_course` | Facilitador |
| 6 | EDITAR_CURSO_E | `edit_course_e` | Edición excepcional |
| 7 | CALIFICAR_CURSO | `grade_course` | Calificar |
| 8 | RESPONDER_CURSO | `accept_course` | Aceptar/rechazar |
| 9 | APROBAR_CURSO | `approve_course` | Abrir inscripciones |
| 10 | EDITAR | `edit` | Editar registros admin |
| 11 | GESTIONAR_PERMISOS | `permissions` | Modificar permisos |
| 12 | SEGURIDAD_USUARIO | `security` | Resetear contraseñas |
| 18 | INSCRIBIRSE_CURSO | `enroll` | Inscribirse |
| 19 | CANCELAR_INSCRIPCION | `cancel_enrollment` | Retirar participante |
| 20 | ASIGNAR_PERFIL | `assign` | Asignar perfiles |
| 21 | VER_PARTICIPANTES | `view_participants` | Ver inscritos |
| 22 | MARCAR_ASISTENCIA | `mark_attendance` | Marcar asistencia propia |
| 23 | GESTIONAR_ASISTENCIA | `manage_attendance` | Gestionar asistencia de cursos |

### 4.5 Perfiles por Defecto

| Perfil | ID aprox | Permisos |
|--------|----------|----------|
| Administrator | 1 | Todos los permisos (CRUD total + permisos + seguridad + asistencia) |
| Coordinador | 4 | Gestión de cursos, aprobaciones, gestionar asistencia |
| Facilitador | 2 | Editar/crear cursos asignados, calificar, gestionar asistencia |
| Participante | 3 | Inscribirse, ver contenido, emitir certificado, marcar asistencia |

### 4.6 Middlewares

| Middleware | Uso |
|-----------|-----|
| `auth` | Verifica autenticación |
| `decrypt_id` | Descifrado de IDs en URLs (middleware global en Taller) |
| `permiso:{route},{actionId}` | Verificación de permisos RBAC |
| `CheckSecurity` | Verifica permisos RBAC para rutas administrativas (users.*) |
| `SetLanguage` | Gestión de idioma por sesión |
| `CheckIntranet` | Verificación de acceso a intranet |

### 4.7 Helpers de Permisos (Helpers.php)

| Función | Propósito |
|---------|-----------|
| `hasPermission($processId, $actionId)` | Verificar permiso por ID de proceso |
| `hasPermissionRoute($routeSlug, $actionId)` | Verificar permiso por nombre de ruta |
| `showActions($text, $name, $actions)` | Generar checkboxes de permisos con labels del SecurityAction |

---

## 5. Ciclo de Vida del Curso

### 5.1 Los 9 Estados del Curso (EstadoCurso enum)

```
 1. POR_ACEPTAR   → Curso recién creado, facilitador debe aceptar
 2. RECHAZADO     → Facilitador rechazó la asignación
 3. DECLINADO     → Curso declinado por coordinación
 4. EDICION       → Facilitador está editando el contenido
 5. APROBACION    → En revisión de coordinación para aprobar
 6. INSCRIPCION   → Abierto para inscripciones de participantes
 7. EN_CURSO      → En progreso, dictándose
 8. FINALIZADO    → Curso terminado, calificaciones registradas
 9. CERRADO       → Archivado definitivamente
```

### 5.2 Flujo de Estados

```
CREAR → POR_ACEPTAR → EDICION → APROBACION → INSCRIPCION → EN_CURSO → FINALIZADO → CERRADO
         ↓               ↑          ↓
      RECHAZADO     DECLINADO   (rechazado vuelve a EDICION)
```

### 5.3 Flujo Detallado por Etapa

#### Creación del curso
1. Coordinador completa formulario (`CrearCursoController::create`)
2. Carga: modalidades, actividades, aspectos, regiones, facilitadores
3. Calcula próximo correlativo automático
4. POST → `StoreCursoRequest` (validación)
5. `DB::beginTransaction()` → `Curso::create($data)`
6. `CursoObserver::creating()` genera código PROCINEC automáticamente
7. Sincroniza localidades (`curso_localidades`)
8. `DB::commit()` → Curso queda en estado: **POR_ACEPTAR (1)**

#### Aceptación por facilitador
1. Facilitador ve cursos asignados (`CursoAsignadoController::index`)
2. Filtra por `id_persona = usuario_actual`
3. Muestra botón "Aceptar" solo si estado = POR_ACEPTAR
4. POST `/taller/cursos/{id}/aceptar-estado` → Cambia estado a **EDICION (4)**

#### Edición y envío a aprobación
1. Facilitador edita curso (`EditarCursoController`)
2. Agrega contenidos (actividades), modifica info
3. `CondicionalEditarCurso` resuelve qué partial mostrar
4. Cuando termina → Cambia estado a **APROBACION (5)**

#### Aprobación por coordinación
1. Coordinador revisa en vista `CursoDetalle`
2. `CondicionalEstadoCurso`: estado 5 → capacidades: aprobar, rechazar
3. Muestra partial: `en-aprobacion-coordinador.blade.php`
4. POST `/taller/cursos/{id}/status` con `id_estado: 6`
5. Verifica `SecurityAction::APROBAR_CURSO`
6. `Curso::agregarEstado(6)` → **INSCRIPCION (6)**

#### Inscripciones
1. Participante ve curso en listado
2. `CondicionalEstadoCurso`: estado 6 → capacidad: inscribirse
3. Verifica: no es facilitador, hay cupos, tiene permiso RBAC
4. GET/POST `/taller/cursos/{id}/inscribirse`
5. Flujo: postulado → aprobado (consume cupo) / rechazado / denegado

#### Dictado y calificación
1. Estado → **EN_CURSO (7)**
2. Facilitador accede a contenido
3. `CalificacionController` muestra planilla
4. Guarda calificaciones por contenido evaluativo

#### Finalización y certificados
1. Estado → **FINALIZADO (8)**
2. `CertificadoService` genera PDF con QR de verificación
3. Estado → **CERRADO (9)** → Archivado

### 5.4 Generación de Código PROCINEC (CursoObserver)

- **creating**: Genera código automáticamente al crear
- **updating**: Regenera código si cambian campos clave
- **Formato:** `LAB-{actividad}{trimestre}{correlativo}{aspecto}{modalidad}{modalidadEspecial}-{anio}`
- **Ejemplo:** `LAB-T012A0101-2026`
- **Campos clave que disparan regeneración:**
  - `id_actividad_formativa`, `id_aspecto`, `trimestre`, `correlativo`, `anio`, `id_modalidad_especial`, `id_modalidad`

### 5.5 CondicionalEstadoCurso — Mapa de Capacidades

```php
MAPA_CAPACIDADES = [
    1 => ['aceptar_asignacion' => 'operativo', 'rechazar_asignacion' => 'operativo'],
    3 => ['ver_motivo' => 'operativo', 'editar' => 'operativo', 'enviar_aprobacion' => 'operativo'],
    4 => ['editar' => 'operativo', 'enviar_aprobacion' => 'operativo'],
    5 => ['aprobar' => 'gestion', 'rechazar' => 'gestion'],
    6 => ['inscribirse' => 'publico', 'acceder_contenido' => 'participante'],
    7 => ['acceder_contenido' => 'participante', 'finalizar_curso' => 'gestion',
          'gestionar_asistencia' => 'operativo', 'marcar_asistencia' => 'participante'],
    8 => ['acceder_contenido' => 'participante', 'emitir_certificado' => 'participante',
          'consultar_asistencia' => 'operativo'],
    9 => ['ver_archivo' => 'publico'],
];
```

### 5.6 Contextos de Usuario

| Contexto | Quién es | Cómo se verifica |
|----------|----------|------------------|
| `gestion` | Coordinador/Administrador | `hasPermissionRoute('taller.cursos.index', GESTIONAR_CURSO)` |
| `operativo` | Facilitador (dueño del curso) | `$curso->id_persona == $user->personalData->id_persona` |
| `participante` | Inscrito en el curso | `$curso->inscripciones->contains('id_persona', $persona->id)` |
| `publico` | Cualquiera que no sea facilitador ni participante | Negación de los anteriores + cupos disponibles |

---

## 6. Sistema de Inscripciones

### 6.1 Estados de Inscripción

```php
const ESTADO_POSTULADO = 'postulado';   // Solicitud enviada
const ESTADO_APROBADO  = 'aprobado';    // Aceptada, consume cupo
const ESTADO_RECHAZADO = 'rechazado';   // Rechazada por coordinación
const ESTADO_DENEGADO  = 'denegado';    // Denegada permanentemente
```

### 6.2 Flujo Completo

```
POST /taller/cursos/{id}/inscribirse
  → Valida permiso: SecurityAction::INSCRIBIRSE_CURSO
  → Verifica: no tiene inscripción previa (excepto rechazada)
  → Verifica: cupos disponibles
  → Crea Inscripcion (estado: postulado)
  → Guarda respuestas a requisitos (InscripcionRespuesta)

POST /taller/inscripciones/{id}/aprobar
  → Solo coordinador con permiso GESTIONAR_CURSO
  → Cambia estado → aprobado
  → Consume 1 cupo del curso

POST /taller/inscripciones/{id}/rechazar
  → Cambia estado → rechazado
  → No consume cupo

DELETE /taller/inscripciones/{id}
  → Solo facilitador (cancela participación)
  → Revierte cupo si estaba aprobado
```

### 6.3 Conteo de Cupos

```php
// Solo inscripciones "aprobadas" consumen cupo:
$inscritos = Inscripcion::activas()->where('id_curso', $curso->id_curso)->count();
// scopeActivas() → where('estado', 'aprobado')

// Verificación antes de inscribir:
if ($curso->cantidad_cupos !== null && $inscritos >= (int) $curso->cantidad_cupos) {
    // Sin cupos disponibles
}
```

---

## 7. Sistema de Asistencia

### 7.1 Descripción General

El sistema de asistencia permite:
- **Generar enlaces/QR temporales** para que los estudiantes marquen asistencia
- **Marcado vía link/QR** con autenticación obligatoria
- **Marcado manual** por el facilitador (para participantes sin teléfono/datos)
- **Consolidado de asistencia** (tabla participantes × actividades)
- **Listas individuales** por actividad
- **Anulación y restauración** de asistencias
- **Exportación a Excel** del consolidado

### 7.2 Flujo de Marcado de Asistencia

```
Facilitador presiona "Generar Enlace / QR"
        │
        ▼
AsistenciaController::generarToken()
  → Desactiva tokens anteriores de esa actividad
  → Crea token con expiración (default 30 min)
  → Retorna URL + QR image
        │
        ▼
Facilitador comparte el enlace / proyecta QR
        │
        ▼
Estudiante abre el enlace
        │
        ├─ ¿Tiene sesión? ──No──→ Login → redirect()->intended() → Vuelve aquí
        │
        ├─ Sí
        ▼
mostrarConfirmacion()
  → Valida token (activo, no expirado)
  → Valida inscripción del estudiante
  → Valida que no haya marcado ya
  → Muestra pantalla de confirmación
        │
        ▼
Estudiante presiona "Sí, registrar mi asistencia"
        │
        ▼
marcar()
  → Crea registro en asistencias (metodo_marcado: 'link')
  → Muestra pantalla de éxito verde
```

### 7.3 Controller — AsistenciaController (8 métodos)

Extiende `BaseController`. Ubicación: `Modules/Taller/Http/Controllers/AsistenciaController.php`

| Método | Ruta | Descripción |
|--------|------|-------------|
| `consolidado($curso)` | `GET /cursos/{curso}/asistencia` | Tabla consolidada: participantes × actividades con check/x y % asistencia |
| `individual($curso, $inscripcion)` | `GET /cursos/{curso}/asistencia/{inscripcion}/individual` | Detalle de un participante con resumen cards |
| `generarToken($curso, $contenido)` | `POST /cursos/{curso}/contenido/{contenido}/generar-token` | Crea token temporal, retorna URL + QR |
| `anular($curso, $asistencia)` | `POST /cursos/{curso}/asistencia/{asistencia}/anular` | Marca `activa = false` con motivo |
| `restaurar($curso, $asistencia)` | `POST /cursos/{curso}/asistencia/{asistencia}/restaurar` | Reactiva asistencia anulada |
| `marcarManual($curso, $contenido)` | `POST /cursos/{curso}/contenido/{contenido}/marcar-manual` | Facilitador marca por otro participante |
| `mostrarConfirmacion($curso, $token)` | `GET /asistencia/{curso}/{token}` | Pantalla de confirmación antes de marcar |
| `marcar($curso, $token)` | `POST /asistencia/{curso}/{token}/confirmar` | Registra la asistencia en BD |

### 7.4 Rutas de Asistencia (8 rutas)

| Método | Ruta | Nombre | Auth |
|--------|------|--------|------|
| GET | `/taller/cursos/{curso}/asistencia` | `taller.asistencia.consolidado` | Sí |
| GET | `/taller/cursos/{curso}/asistencia/{inscripcion}/individual` | `taller.asistencia.individual` | Sí |
| POST | `/taller/cursos/{curso}/contenido/{contenido}/generar-token` | `taller.asistencia.generar-token` | Sí |
| POST | `/taller/cursos/{curso}/asistencia/{asistencia}/anular` | `taller.asistencia.anular` | Sí |
| POST | `/taller/cursos/{curso}/asistencia/{asistencia}/restaurar` | `taller.asistencia.restaurar` | Sí |
| POST | `/taller/cursos/{curso}/contenido/{contenido}/marcar-manual` | `taller.asistencia.marcar-manual` | Sí |
| GET | `/taller/asistencia/{curso}/{token}` | `taller.asistencia.confirmar` | Sí (intencionado) |
| POST | `/taller/asistencia/{curso}/{token}/confirmar` | `taller.asistencia.confirmar-marcar` | Sí (intencionado) |

Las últimas dos rutas están **fuera** del grupo `auth` pero con middleware `auth` individual, permitiendo el flujo `redirect()->intended()`.

### 7.5 Vistas de Asistencia (5 vistas)

| Vista | Archivo | Descripción |
|-------|---------|-------------|
| **Consolidado** | `AsistenciaConsolidado.blade.php` | Tabla con todos los participantes, indicadores por actividad, modal para generar QR |
| **Individual** | `AsistenciaIndividual.blade.php` | Cards de resumen (total, %, ausencias) + detalle por actividad |
| **Confirmar** | `AsistenciaConfirmar.blade.php` | Pantalla minimalista que el estudiante ve al abrir el enlace |
| **Exitosa** | `AsistenciaExitosa.blade.php` | Confirmación verde post-marcado |
| **Expirada** | `AsistenciaExpirada.blade.php` | Mensaje de error para tokens expirados/inválidos |

### 7.6 Diagrama de BD de Asistencia

```
┌──────────────────────────────┐       ┌─────────────────────────────────┐
│    taller.asistencia_tokens   │       │        taller.asistencias        │
├──────────────────────────────┤       ├─────────────────────────────────┤
│ id_token (PK)                │       │ id_asistencia (PK)              │
│ id_contenido_curso (FK) ─────┤───┐   │ id_contenido_curso (FK) ───────┤──┐
│ token (UNIQUE)               │   │   │ id_persona (FK) ───────────────┤──┤
│ activo                       │   │   │ id_inscripcion (FK) ───────────┤──┤
│ fecha_expiracion             │   │   │ fecha_hora_marcado             │   │
│ creado_por (FK→users)        │   │   │ activa (default true)          │   │
│ creado_en / actualizado_en   │   │   │ anulada_por (FK→users)         │   │
└──────────────────────────────┘   │   │ fecha_anulacion                │   │
                                   │   │ motivo_anulacion               │   │
                                   │   │ ip_marcado                     │   │
                                   │   │ metodo_marcado (link/qr/manual)│   │
┌──────────────────────────────┐   │   │ UNIQUE(id_contenido, id_persona)│  │
│   taller.contenido_cursos    │   │   └─────────────────────────────────┘  │
├──────────────────────────────┤   │                                        │
│ id_contenido_curso (PK) ─────┘───┘                                        │
│ id_curso (FK)                                                            │
│ es_evaluacion → false = "día de clases"                                  │
└──────────────────────────────┘                                            │
                                                                            │
┌──────────────────────────────┐       ┌────────────────────────────────┐   │
│    taller.inscripciones       │       │      comun.personas            │   │
├──────────────────────────────┤       ├────────────────────────────────┤   │
│ id_inscripcion (PK) ─────────┤───────┤ id_persona (PK)                │   │
│ id_curso (FK)                │       │ primer_nombre, primer_apellido │   │
│ id_persona (FK→personas)     │       │ dni, telefono, email           │   │
└──────────────────────────────┘       └────────────────────────────────┘   │
```

### 7.7 Modelos de Asistencia

**Asistencia.php** (`Modules/Taller/Entities/Asistencia.php`)
- Tabla: `taller.asistencias`, PK: `id_asistencia`
- Relaciones: `actividad()`, `persona()`, `inscripcion()`, `anulador()`
- Scopes: `scopeActivas()`, `scopeAnuladas()`
- Accessor: `crypt_id` para URLs encriptadas

**AsistenciaToken.php** (`Modules/Taller/Entities/AsistenciaToken.php`)
- Tabla: `taller.asistencia_tokens`, PK: `id_token`
- Relación: `actividad()`
- Método estático: `generarToken()` genera 64 caracteres hex aleatorios (256 bits)
- Accessor: `crypt_id`

---

## 8. Sistema de Certificados

### 8.1 Generación PDF (CertificadoService)

```
Entrada: Curso + Inscripcion
   ↓
1. Carga coordenadas: hardcoded → defaults.json → {curso_id}.json
2. Genera QR: URL → /taller/certificados/verificar/{codigo}
3. Descarga QR como imagen temporal
4. Carga plantilla: {curso_id}.png → plantilla.png (fallback)
5. Crea PDF A4 horizontal con FPDF
6. Superpone: fondo → QR → firma → textos (nombre, DNI, código)
7. Retorna contenido binario del PDF
```

### 8.2 Estructura de Storage para Certificados

```
storage/app/public/Certificados/
├── plantilla.png                    ← Plantilla por defecto
├── defaults.json                    ← Coordenadas globales
└── cursos/
    ├── {curso_id}.png               ← Plantilla específica del curso
    ├── {curso_id}.json              ← Coordenadas específicas del curso
    ├── {curso_id}_firma.png         ← Firma digital del facilitador
    └── {curso_id}_firma.jpg         ← Alternativa JPG
```

### 8.3 Formato del Código de Certificado

```
Código = {codigo_curso}-{dni_participante}
Ejemplo: LAB-T012A0101-2026-12345678

URL de verificación: /taller/certificados/verificar/{codigo}
Esta ruta es PÚBLICA (sin auth, sin decrypt_id)
```

---

## 9. Patrones de Código y Convenciones

### 9.1 Convenciones de Naming

#### Base de Datos
```
ESQUEMA.tabla           → snake_case, plural (taller.cursos, security.users)
COLUMNA_ID FK           → id_{entidad} (id_curso, id_persona, id_modalidad)
COLUMNA PK              → id_{entidad_singular} (id_curso, id_persona, id_inscripcion)
TIMESTAMPS              → creado_en / actualizado_en (NO created_at/updated_at)
COLUMNAS AUDITORÍA      → creado_por / actualizado_por (user ID, NO timestamps)
```

#### Modelos Eloquent
```
NAMESPACE              → Modules\{Modulo}\Entities\{Nombre}
TABLA                  → protegida: $table = 'esquema.tabla' (SIEMPRE con prefijo de schema)
PK                     → $primaryKey = 'id_{entidad}' (nunca 'id' genérico)
TIMESTAMPS             → const CREATED_AT = 'creado_en'; const UPDATED_AT = 'actualizado_en';
                         O $timestamps = false; si no tiene columnas de timestamp
ENCRYPTED ID           → $appends = ['crypt_id']; + getCryptIdAttribute()
FILLABLE               → Todos los campos editables explícitamente listados
```

#### Controllers
```
NAMESPACE              → Modules\{Modulo}\Http\Controllers\{Nombre}Controller
BASE                   → Extienden BaseController (Taller) o Controller (Laravel base)
MÉTODOS                → index, create, store, show, edit, update, destroy (Resource)
                       → Métodos custom: aceptar, aprobar, rechazar, contenido, etc.
```

#### Rutas
```
PREFIJO                → /{modulo}/ (taller, security, registro, comun, parametros)
NOMBRE                 → {modulo}.{recurso}.{acción} (taller.cursos.index, security.profiles.create)
MIDDLEWARE             → 'auth', 'decrypt_id', 'permiso:{route},{actionId}'
```

#### Vistas Blade
```
UBICACIÓN              → Modules/{Mod}/Resources/views/{archivo}.blade.php
NAMESPACE              → @extends('security::layouts.kaiadmin-main')
COMPONENTES            → @component('taller::alert') o <x-taller.alert />
PARTIALS               → Partes reutilizables en subcarpeta partials/
```

### 9.2 Patrón de BaseController (Módulo Taller)

Todos los controllers del módulo Taller extienden `BaseController`:

```php
class BaseController extends Controller
{
    // Devuelve el usuario autenticado con sus datos personales precargados
    protected function getUsuarioAutenticado()
    {
        return Auth::user()->load('personalData');
    }

    // Verifica si el usuario tiene datos personales (comun.personas)
    protected function usuarioSinDatosPersonales()
    {
        $usuario = $this->getUsuarioAutenticado();
        return !$usuario->personalData;
    }
}
```

**Uso consistente en todos los controllers:**
```php
// SIEMPRE al inicio de cada método controller:
if ($this->usuarioSinDatosPersonales()) {
    return redirect()->back()->with('error', 'Datos personales no encontrados.');
}

$user = $this->getUsuarioAutenticado();
$personalData = $user->personalData;
// Usar $personalData->id_persona como FK a tablas del dominio
```

### 9.3 Sistema de IDs Encriptados

**Cómo funciona:**
```
URL del navegador: /taller/cursos/S0lUMVNwTTNzMVZFSUtLT3U1T1Y1Zz09/
                              └─────────── ID encriptado (AES-256-CBC) ───────────┘

Middleware 'decrypt_id':
1. Intercepta todos los parámetros de ruta
2. Intenta descifrar con Crypt::decryptString() (Laravel)
3. Si falla, intenta con método legacy (openssl IV fijo)
4. El controller recibe el ID DESCIFRADO como entero
```

**Generación en modelos:**
```php
// Trait EncryptationId (usado por User, Profile, Process)
public function getCryptIdAttribute() {
    return Encryptor::encrypt($this->attributes['id']);
}

// Modelos que lo usan manualmente (Curso, Inscripcion, PersonalData, Asistencia):
public function getCryptIdAttribute() {
    return Encryptor::encrypt($this->id_curso);  // usa la PK específica
}
protected $appends = ['crypt_id'];
```

**Uso en vistas Blade:**
```blade
<!-- Para links: SIEMPRE usar crypt_id, nunca el ID plano -->
<a href="{{ route('taller.cursos.show', $curso->crypt_id) }}">Ver curso</a>

<!-- Para formularios POST/PUT: el ID viene del route param ya descifrado -->
<form action="{{ route('taller.cursos.update', $curso->id_curso) }}">
```

### 9.4 Patrón de Validación

**Form Requests (FormRequest):**
```php
class StoreCursoRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'trimestre' => 'required|integer|min:1|max:4',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'contenidos.*.titulo' => 'required_with:contenidos|string|max:255',
            'contenidos.*.ponderacion' => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre del curso es obligatorio.',
        ];
    }
}
```

**Validación inline en Controllers:**
```php
$request->validate([
    'nombre'      => 'required|string|max:100',
    'abreviatura' => 'required|string|max:4|unique:' . ActividadFormativa::class . ',abreviatura',
], [
    'nombre.required' => 'El nombre de la actividad es obligatorio.',
]);
```

### 9.5 Patrón de Respuesta JSON

```php
// Éxito:
return response()->json([
    'success' => true,
    'message' => 'Estado del curso actualizado correctamente'
]);

// Error con código HTTP:
return response()->json([
    'success' => false,
    'message' => 'No tienes permiso para aprobar cursos.'
], 403);

// Errores con debug:
return response()->json([
    'success' => false,
    'message' => 'Error al actualizar: ' . $e->getMessage()
], 500);
```

**Códigos de error usados:**
```
400 → Bad Request (datos inválidos del cliente)
403 → Forbidden (sin permisos RBAC)
404 → Not Found (recurso no existe)
422 → Unprocessable Entity (estado inválido para la operación)
500 → Internal Server Error (excepción no controlada)
```

### 9.6 Patrón de Transacciones de BD

```php
// SIEMPRE en operaciones que crean/modifican múltiples tablas:
DB::beginTransaction();
try {
    $curso = Curso::create($data);  // Tabla principal
    $curso->localidades()->sync($localidades);  // Tabla pivote
    // ... más operaciones
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    Log::error('Error: ' . $e->getMessage());
    return back()->with('error', 'Error al guardar.');
}
```

### 9.7 Patrón de Partial Actions (Vistas Contextuales)

**Categorías de partials:**
```
partials/curso-actions/         → Acciones según estado×rol (17 archivos)
partials/editar-actions/        → Formulario de edición según rol
partials/participantes/         → Listado de participantes
partials/Buscador-actions/      → Búsqueda/filtros del listado de cursos
```

**Naming de partials de curso-actions:**
```
{estado}-{rol}.blade.php

Ejemplos:
por-aceptar-facilitador.blade.php
en-edicion-facilitador.blade.php
en-aprobacion-coordinador.blade.php
en-progreso-facilitador.blade.php
finalizado-estudiante.blade.php
cerrado.blade.php  (sin rol, aplica a todos)
```

**Resolución de partials (CondicionalEditarCurso):**
```php
MAPA_ACCIONES = [
    1 => ['facilitador' => 'partials.editar-actions.Editar-Facilitador'],
    3 => ['facilitador' => 'partials.editar-actions.Editar-Facilitador'],
    4 => ['facilitador' => 'partials.editar-actions.Editar-Facilitador',
          'coordinador' => 'partials.editar-actions.Editar-Coordinador'],
    6 => ['coordinador' => 'partials.editar-actions.Editar-Coordinador'],
    7 => ['coordinador' => 'partials.editar-actions.Editar-Coordinador'],
];
// Prioridad: coordinador > facilitador > default
```

### 9.8 Patrón de Renderizado en CursoDetalle

```blade
{{-- CursoDetalle.blade.php --}}
@foreach ($capacidades as $capacidad)
    @if($capacidad === 'aceptar_asignacion')
        @include('taller::a.partials.curso-actions.por-aceptar-facilitador')
    @endif
    @if($capacidad === 'editar')
        @include($vistaEdicion)  {{-- Resuelta por CondicionalEditarCurso --}}
    @endif
    @if($capacidad === 'aprobar')
        @include('taller::a.partials.curso-actions.en-aprobacion-coordinador')
    @endif
    {{-- ... --}}
@endforeach
```

### 9.9 Helpers Globales (Helpers.php)

| Función | Propósito |
|---------|-----------|
| `Upper($text)` | Mayúsculas UTF-8 |
| `Lower($text)` | Minúsculas UTF-8 |
| `Abc($text)` | Primera mayúscula |
| `CamelCase($text)` | CamelCase |
| `setLabel($text)` | Texto → label con guiones bajos |
| `removeLabel($text)` | Label → texto con espacios |
| `showFloat($monto)` | Formato numérico VZLA (1.234,56) |
| `saveFloat($monto)` | Guardar float desde formato VZLA |
| `showDate($date)` | YYYY-MM-DD → DD/MM/YYYY |
| `saveDate($date)` | DD/MM/YYYY → YYYY-MM-DD |
| `showDateFull($date)` | DateTime → DD/MM/YYYY HH:MM |
| `showPhone($num)` | Formato teléfono (XXXX-XXXXXXX) |
| `checkCta(...)` | Validar cuenta bancaria venezolana |
| `hasPermission($processId, $actionId)` | Verificar permiso RBAC por proceso |
| `hasPermissionRoute($routeSlug, $actionId)` | Verificar permiso RBAC por ruta |
| `showActions($text, $name, $actions)` | Generar checkboxes de permisos |
| `renderAvatar($user, $size)` | Renderizar avatar (foto o iniciales) |

---

## 10. Frontend — Layouts, Componentes y Vistas

### 10.1 Layouts Principales

| Layout | Archivo | Uso |
|--------|---------|-----|
| Principal (autenticado) | `resources/views/layouts/kaiadmin-main.blade.php` | Layout principal con menú lateral |
| Login | `resources/views/layouts/kaiadmin-login.blade.php` | Layout de login |
| Público | `resources/views/layouts/kaiadmin-front.blade.php` | Layout público |
| Menú | `resources/views/layouts/kaiadmin-menu.blade.php` | Componente del menú lateral |
| Template base | `resources/views/layouts/kaiadmin-template.blade.php` | Template base |
| Selección de perfil | `resources/views/layouts/kaiadmin-select-profile.blade.php` | Selección de perfil |
| Email | `resources/views/layouts/email.blade.php` | Layout de correos |

### 10.2 Componentes Blade Reutilizables

```
resources/views/components/taller/
├── alert.blade.php          ← Alertas success/error/warning
├── card-header.blade.php    ← Headers de tarjetas Bootstrap
├── contenido-item.blade.php ← Items de contenido del curso
├── input.blade.php          ← Inputs personalizados
└── select.blade.php         ← Selects personalizados
```

**Uso:**
```blade
<x-taller.alert type="success" message="{{ session('success') }}" />
<x-taller.input name="nombre" label="Nombre del Curso" :value="$curso->nombre" />
<x-taller.select name="modalidad" label="Modalidad" :options="$modalidades" />
```

### 10.3 Vistas del Módulo Taller (Resources/views/a/)

| Vista | Propósito |
|-------|-----------|
| `Cursos.blade.php` | Listado principal de cursos |
| `CursosAsignados.blade.php` | Cursos asignados al facilitador |
| `CursosInscritos.blade.php` | Cursos donde el participante está inscrito |
| `CursoDetalle.blade.php` | Detalle de un curso con acciones contextuales |
| `CursoCrear.blade.php` | Formulario de creación |
| `CursoEditar.blade.php` | Formulario de edición |
| `CursoContenido.blade.php` | Gestión de actividades del curso |
| `CursoCalificar.blade.php` | Calificaciones |
| `CursoInscribirse.blade.php` | Formulario de inscripción |
| `CursoRequisitosCrear.blade.php` | Crear requisitos |
| `CursoRequisitosEditar.blade.php` | Editar requisitos |
| `CursoPlantillaCrear.blade.php` | Plantilla de certificado |
| `CursoParticipantes.blade.php` | Listado de participantes |
| `CursoParticipantesRespuestas.blade.php` | Respuestas a requisitos |
| `Catalogos.blade.php` | Gestión de catálogos (actividades, aspectos) |
| `AsistenciaConsolidado.blade.php` | Tabla consolidada de asistencia |
| `AsistenciaIndividual.blade.php` | Detalle individual de asistencia |
| `AsistenciaConfirmar.blade.php` | Confirmación de marcado |
| `AsistenciaExitosa.blade.php` | Éxito post-marcado |
| `AsistenciaExpirada.blade.php` | Error para tokens expirados |

### 10.4 DataTables (Yajra)

**Patrón de listado:**
```php
// UsersController::list()
$Users = User::limit(100)->where('active', '0')->with('perfiles')->get();

$data = DataTables::of($Users)
    ->addIndexColumn()
    ->addColumn('action', function ($user) {
        return '<a href="' . route('users.update', $user->crypt_id) . '">Editar</a>';
    })
    ->rawColumns(['action'])
    ->make(true);
```

**Uso en frontend:**
```javascript
$('#users-table').DataTable({
    ajax: '{{ route("users.list") }}',
    columns: [
        { data: 'DT_RowIndex' },
        { data: 'username' },
        { data: 'email' },
        { data: 'action', orderable: false }
    ]
});
```

---

## 11. Rutas Principales por Módulo

### 11.1 Seguridad (`/security/`)

```
GET/POST /security/login              → Login
GET/POST /security/register           → Registro
GET/POST /security/recovery/{token?}  → Recuperar contraseña
GET       /security/logout            → Cerrar sesión
GET       /security/home              → Dashboard
GET       /security/profiles          → Listado de perfiles
GET       /security/usuarios          → Listado de usuarios
GET/POST /security/users-create       → Crear usuario
GET/POST /security/users-update/{id}  → Editar usuario
```

### 11.2 Taller (`/taller/`) — requiere auth + decrypt_id

```
GET    /taller/cursos                          → Listado
GET    /taller/cursos/{curso}                  → Detalle
GET    /taller/cursos/{curso}/contenido/{id?}  → Contenido/Actividades
GET    /taller/Cursos-asignados                → Asignados (facilitador)
GET    /taller/mis-cursos                      → Inscritos (participante)
GET    /taller/crear-curso                     → Crear
POST   /taller/crear-curso                     → Guardar
GET    /taller/cursos/{curso}/inscribirse      → Inscribirse
POST   /taller/cursos/{curso}/inscribirse      → Procesar inscripción
POST   /taller/inscripciones/{id}/aprobar      → Aprobar inscripción
POST   /taller/inscripciones/{id}/rechazar     → Rechazar inscripción
POST   /taller/inscripciones/{id}/denegar      → Denegar inscripción
GET    /taller/cursos/{curso}/asistencia       → Consolidado asistencia
GET    /taller/cursos/{curso}/asistencia/{inscripcion}/individual → Individual
POST   /taller/cursos/{curso}/contenido/{contenido}/generar-token → Token
POST   /taller/cursos/{curso}/asistencia/{asistencia}/anular     → Anular
POST   /taller/cursos/{curso}/asistencia/{asistencia}/restaurar  → Restaurar
POST   /taller/cursos/{curso}/contenido/{contenido}/marcar-manual → Manual
GET    /taller/asistencia/{curso}/{token}      → Confirmar (público auth)
POST   /taller/asistencia/{curso}/{token}/confirmar → Marcar (público auth)
GET    /taller/certificados/{curso}/descargar  → Descargar certificado
GET    /taller/certificados/verificar/{codigo} → Verificar certificado (público)
GET    /taller/catalogos                       → Catálogos
```

### 11.3 Registro (`/registro/`)

```
GET/POST /registro/usuario              → Registro público
GET      /registro/ajax/estados         → AJAX estados
GET      /registro/ajax/municipios/{id} → AJAX municipios
GET      /registro/ajax/parroquias/{id} → AJAX parroquias
GET      /registro/usuarios/asignar-perfil → Admin: asignar perfiles
```

### 11.4 Común (`/comun/`)

```
GET /comun/ → Index básico
```

### 11.5 Parámetros (`/parametros/`)

```
GET /parametros/ → Index básico
```

---

## 12. Reglas de Codificación Obligatorias

> **Estas reglas existen para prevenir errores recurrentes. Cualquier código nuevo DEBE cumplirlas.**

### 12.1 Campos Nullable — Verificación Obligatoria

**REGLA:** Antes de llamar cualquier método sobre un campo, verificar si puede ser `null` según la migración.

```php
// INCORRECTO — Si fecha_contenido es null, lanza "Call to member function on null"
{{ $contenidoActual->fecha_contenido->format('d/m/Y') }}

// CORRECTO — Verificar con @if o usar operador null-safe
@if($contenidoActual->fecha_contenido)
    {{ $contenidoActual->fecha_contenido->format('d/m/Y') }}
@endif

// TAMBIÉN CORRECTO — Operador null-safe de PHP 8
{{ $contenidoActual->fecha_contenido?->format('d/m/Y') ?? 'Sin fecha' }}
```

**Campos nullable conocidos que requieren verificación:**

| Tabla | Campo | Tipo | Error si no se verifica |
|-------|-------|------|------------------------|
| `contenido_cursos` | `fecha_contenido` | date | `->format()` on null |
| `contenido_cursos` | `url_contenido` | string | `str_contains()` on null |
| `contenido_cursos` | `ponderacion` | decimal | |
| `contenido_cursos` | `id_tipo_evaluacion` | bigint | relación null |
| `asistencias` | `fecha_anulacion` | timestamp | `->format()` on null |
| `asistencia_tokens` | `fecha_expiracion` | timestamp | `->isPast()` on null |
| `inscripciones` | campos opcionales | varios | |

### 12.2 Consultas a `taller.asistencias` — Relación Indirecta con Curso

**REGLA:** La tabla `asistencias` NO tiene columna `id_curso`. Para filtrar por curso, usar los IDs de contenido.

```php
// INCORRECTO — La columna id_curso NO existe en asistencias
Asistencia::where('id_curso', $curso_id)->get();

// CORRECTO — Filtrar por los contenidos del curso
$contenidoIds = $curso->contenidos->pluck('id_contenido_curso');
Asistencia::whereIn('id_contenido_curso', $contenidoIds)->get();
```

### 12.3 Sintaxis Blade — Errores Comunes

**REGLA:** Verificar siempre que los paréntesis estén balanceados en expresiones Blade `{{ }}`.

```php
// INCORRECTO — Falta paréntesis de cierre en substr()
{{ substr($persona->primer_nombre ?? 'N', 0, 1 }}

// CORRECTO
{{ substr($persona->primer_nombre ?? 'N', 0, 1) }}
```

### 12.4 Convenciones de Primary Keys

**REGLA:** Cada tabla tiene su propia PK con nombre descriptivo. NO asumir que es `id`.

| Tabla | PK | Modelo |
|-------|----|--------|
| `cursos` | `id_curso` | `Curso` |
| `contenido_cursos` | `id_contenido_curso` | `ContenidoCurso` |
| `inscripciones` | `id_inscripcion` | `Inscripcion` |
| `asistencias` | `id_asistencia` | `Asistencia` |
| `asistencia_tokens` | `id_token` | `AsistenciaToken` |
| `personas` | `id_persona` | `PersonalData` |
| `users` | `id` | `User` (excepción: usa `id` genérico) |
| `calificaciones` | `id_calificacion` | `Calificacion` |

### 12.5 Peticiones AJAX y Fetch API (JavaScript)

**REGLA 1: Cabeceras Obligatorias para Fetch**
La API nativa `fetch()` en JavaScript **NO** envía la cabecera `X-Requested-With` automáticamente. Si no se incluye, Laravel no detectará la petición como AJAX.

```javascript
// CORRECTO — Incluir siempre ambas cabeceras
fetch(url, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest' // CRITICO PARA $request->ajax()
    },
    body: JSON.stringify(data),
})
```

**REGLA 2: Construcción Segura de URLs Dinámicas**
Usar una palabra clave como comodín (ej. `ID_COMODIN`) y reemplazarla en el lado del cliente.

```javascript
// INCORRECTO — Si route() genera '/ruta/123', el replace('/ruta/', ...) fallará silenciosamente
const url = `{{ route('nombre.ruta', ['id' => $modelo->id]) }}`.replace('/ruta/', '/ruta/' + idReal);

// CORRECTO — Usar un parámetro comodín seguro
const url = `{{ route('nombre.ruta', ['id' => 'ID_COMODIN']) }}`.replace('ID_COMODIN', idReal);
```

**REGLA 3: Nombres de Rutas Exactos**
Los nombres y parámetros deben coincidir **exactamente** con lo declarado en `web.php`.

---

## 13. Conexiones de Base de Datos

### 13.1 Conexión Principal (pgsql)

```php
'search_path' => 'public,security,comun,parametros,registro,taller'
// Todas las queries usan prefijo de schema: taller.cursos, security.users, etc.
```

### 13.2 Conexión SAIME (externa)

```php
'saime' => [
    'driver' => 'pgsql',
    // Conexión a sistema de ciudadanía venezolano (SAIME)
    // Usada para validación de documentos de identidad
]
```

### 13.3 LockDB (obfuscado)

```php
// Helpers/LockDB.php contiene código ofuscado
// Originalmente usaba LockDB::crazy() para proteger credenciales
// Ahora está comentado en config/database.php
// NO MODIFICAR — propiedad intelectual del autor
```

---

## 14. Dependencias PHP

| Paquete | Uso en CINEFORM |
|---------|-----------------|
| `nwidart/laravel-modules` | Arquitectura modular (5 módulos) |
| `yajra/laravel-datatables-oracle` | Listados paginados con AJAX |
| `setasign/fpdf` | Generación de PDFs de certificados |
| `smalot/pdfparser` | Lectura de PDFs (file_admin) |
| `intervention/image` | Manipulación de imágenes (avatars, marcas de agua) |
| `calcinai/php-imagick` | Conversión PDF→imagen (marcas de agua) |
| `biscolab/laravel-recaptcha` | CAPTCHA en login/registro |
| `mews/purifier` | Limpieza de HTML (XSS prevention) |
| `maatwebsite/excel` | Exportaciones a Excel |
| `spatie/laravel-html` | Construcción de formularios |
| `laravel/sanctum` | Tokens API (disponible pero no activo en uso principal) |

---

## 15. Errores Comunes y Lecciones Aprendidas

| # | Problema | Causa probable | Solución | Lección |
|---|----------|---------------|----------|---------|
| 1 | "Datos personales no encontrados" | Usuario sin registro en `comun.personas` | Registrar vía `/registro/usuario` | |
| 2 | "No tiene permisos" (403) | RBAC no asignado al perfil | Verificar `profile_permissions` en BD | |
| 3 | IDs en URLs no funcionan | Usar `crypt_id`, no ID plano | Cambiar links para usar `$modelo->crypt_id` | |
| 4 | Código PROCINEC no se genera | Observer no registrado | Verificar `Providers/EventServiceProvider` | |
| 5 | Certificado sin QR | API externa no disponible | Verificar conexión a `api.qrserver.com` | |
| 6 | Transacción abortada | Exception sin rollBack | Asegurar `DB::rollBack()` en catch | |
| 7 | Timestamps en 0000-00-00 | Usar constants CREATED_AT/UPDATED_AT | Definir const timestamps personalizados | |
| 8 | `Call to a member function format() on null` | Campo nullable sin verificar | Usar `@if` o `?->` antes de llamar métodos | CONTEXTO_SISTEMA §19.1 |
| 9 | `Undefined column: id_curso` en asistencias | Asumir que asistencias tiene `id_curso` | Usar `whereIn('id_contenido_curso', ...)` | CONTEXTO_SISTEMA §19.2 |
| 10 | `syntax error, unexpected token ";"` en Blade | Paréntesis desbalanceados | Verificar paréntesis de cierre en `{{ }}` | CONTEXTO_SISTEMA §19.3 |
| 11 | `RouteNotFoundException` y redirecciones inesperadas | URL dinámica construida incorrectamente + falta `X-Requested-With` | Usar comodines seguros + cabecera AJAX | CONTEXTO_SISTEMA §19.5 |
| 12 | `boolean('default', 10)` | Boolean no acepta argumento length | `boolean('default')` sin argumento | MIGRACIONES |
| 13 | Type mismatch en FKs | `unsignedInteger` vs PK `bigInteger` | Usar `unsignedBigInteger` para FKs | MIGRACIONES |
| 14 | Typo `instituacion_educativa` | Falta la 't' | Corregir a `institucion_educativa` | MIGRACIONES |
| 15 | `profile_id` redundante en users | Ya existe pivot `profiles_users` | Eliminar de `security.users` | MIGRACIONES |

---

## 16. Archivos de Apoyo y Backup

### 16.1 Carpeta `Archivos de apoyo/`

```
Archivos de apoyo/
├── PLAN/                              ← Documentación de referencia
│   ├── ESTRUCTURA_SISTEMA.md          ← ESTE ARCHIVO (unificado)
│   ├── CONTEXTO_SISTEMA.md            ← Contexto general (630 líneas)
│   ├── CONTEXTO_TECNICO.md            ← Contexto técnico (870 líneas)
│   ├── CAMBIOS_IMPLEMENTADOS.md       ← Cambios de asistencia (426 líneas)
│   ├── MIGRACIONES_REFERENCIA.md      ← Migraciones consolidadas (140 líneas)
│   └── REFORMA_ACTIVIDADES_ASISTENCIA.md ← Plan original asistencia (986 líneas)
├── backup_migrations_modulos/         ← 38 archivos de migración originales de módulos
│   ├── 2000_01_01_100000_create_postgresql_schemas.php
│   ├── 2024_06_12_000000_create_security_genders_table.php
│   ├── ... (38 archivos totales)
│   └── 2026_05_11_164900_add_cancel_enrollment_permission.php
├── backup_migrations_app/             ← 9 archivos de migración originales de app
│   ├── 2026_05_05_221420_add_telegram_to_cursos_table.php
│   ├── 2026_05_05_222749_create_curso_localidades_table.php
│   ├── ... (9 archivos totales)
│   └── 2026_06_21_000001_fix_active_column_type_security_users.php
└── Responsables/                      ← Código fuente de referencia (asistencia)
    ├── AsistenciaController.php       ← Controller completo (393 líneas)
    ├── AsistenciaToken.php            ← Modelo Token (46 líneas)
    ├── AsistenciaConsolidado.blade.php ← Vista consolidada (236 líneas)
    ├── AsistenciaConfirmar.blade.php  ← Vista confirmación (59 líneas)
    ├── AsistenciaExitosa.blade.php    ← Vista éxito (44 líneas)
    ├── AsistenciaExpirada.blade.php   ← Vista error (31 líneas)
    ├── SecurityController.php         ← Controller de seguridad (783 líneas)
    └── web.php                        ← Rutas del módulo Taller (195 líneas)
```

### 16.2 Contenido de `Responsables/` (Detalle)

Los archivos en `Responsables/` son el **código fuente de referencia** para el sistema de asistencia. Incluyen:

- **AsistenciaController.php**: Controller completo con 8 métodos (consolidado, individual, generarToken, anular, restaurar, marcarManual, mostrarConfirmacion, marcar)
- **AsistenciaToken.php**: Modelo Eloquent para tokens temporales
- **AsistenciaConsolidado.blade.php**: Vista con tabla de participantes × actividades + modal para generar tokens
- **AsistenciaConfirmar.blade.php**: Vista minimalista para que el estudiante confirme su asistencia
- **AsistenciaExitosa.blade.php**: Pantalla de confirmación verde post-marcado
- **AsistenciaExpirada.blade.php**: Pantalla de error para tokens expirados/inválidos
- **SecurityController.php**: Controller de seguridad con login, registro, recuperación, selección de perfil (con redirect()->intended())
- **web.php**: Rutas del módulo Taller incluyendo las 8 rutas de asistencia

---

## 17. Comandos Útiles de Referencia

### 17.1 Migraciones y Seeders

```bash
# Migraciones y seeders
php artisan migrate:fresh
php artisan db:seed

# Módulos (nwidart)
php artisan module:make NombreModulo
php artisan module:migrate NombreModulo
php artisan module:seed NombreModulo

# Permisos (después de cambios en módulos)
php artisan db:seed --class=PermissionsSeeder
php artisan db:seed --class=ProfilePermissionsSeeder

# Verificar rutas de asistencia
php artisan route:list --path=asistencia
```

### 17.2 Desarrollo

```bash
npm run dev          # Vite dev server
npm run build        # Build de producción
php artisan serve    # Servidor de desarrollo
```

### 17.3 Docker

```bash
docker-compose up -d
docker-compose down
docker-compose logs -f app
```

### 17.4 Cache y Limpieza

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## Notas Importantes para Futuras Intervenciones

1. **Los modelos están en `Modules/{Mod}/Entities/`**, NO en `app/Models/`
2. **Las migraciones consolidadas están vacías** (`database/migrations/` está vacío) — se movieron a un archivo único consolidado
3. **LockDB.php** contiene código ofuscado/obfuscado (protección de propiedad intelectual) — NO modificar
4. **Los IDs se encriptan en URLs** — siempre usar `crypt_id` en rutas, nunca IDs planos
5. **El sistema RBAC centraliza permisos en SecurityAction** — cualquier nueva acción debe agregarse ahí
6. **Las vistas usan el partial pattern** para acciones contextuales según estado×rol del curso
7. **La migración de IDs legacy** de Encryptor aún tiene fallback — considerar limpiar cuando se confirme que no hay tokens legacy
8. **Conexión SAIME** disponible para validación de ciudadanos venezolanos (sistema externo)
9. **Seeders centralizados** en `database/seeders/` (no en módulos) — ejecutar con `php artisan db:seed`
10. **Las rutas de Taller usan middleware `decrypt_id`** global en el prefijo
11. **La tabla `asistencias` NO tiene `id_curso`** — la relación es indirecta vía `contenido_cursos`
12. **El "Contenido" se renombró a "Actividades" en la UI** — pero la tabla sigue siendo `contenido_cursos`
13. **Los tokens de asistencia son de 64 caracteres hex** (256 bits de entropía), generados con `random_bytes()`
14. **Las rutas de marcado usan `redirect()->intended()`** para que el usuario regrese a la pantalla de asistencia después del login
