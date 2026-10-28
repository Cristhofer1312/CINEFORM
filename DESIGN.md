---
name: CINEFORM
description: Plataforma de formación cinematográfica (cursos, inscripciones, asistencia y certificación).
colors:
  navy-marca: "#1e3a8a"
  navy-suave: "rgba(30, 58, 138, 0.08)"
  azul-accion: "#1572e8"
  azul-accion-hover: "#105fc4"
  cian-marca: "#00c0ef"
  cian-legible: "#0e7490"
  ambar: "#f59e0b"
  exito: "#198754"
  exito-tinta: "#146c43"
  peligro: "#dc2626"
  peligro-tinta: "#b91c1c"
  aviso-tinta: "#b45309"
  tinta: "#0f172a"
  texto: "#334155"
  texto-tenue: "#64748b"
  borde: "#e2e8f0"
  borde-fuerte: "#cbd5e1"
  superficie: "#ffffff"
  superficie-sutil: "#f8fafc"
  fondo-app: "#f1f5f9"
typography:
  headline:
    fontFamily: "Public Sans, sans-serif"
    fontSize: "1.5rem"
    fontWeight: 700
    lineHeight: 1.25
  title:
    fontFamily: "Public Sans, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 600
    lineHeight: 1.3
  body:
    fontFamily: "Public Sans, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.5
    fontFeature: "tnum"
  label:
    fontFamily: "Public Sans, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 600
    letterSpacing: "0.5px"
rounded:
  control: "10px"
  menu: "12px"
  card: "16px"
  pill: "50rem"
spacing:
  sm: "8px"
  md: "16px"
  lg: "24px"
components:
  button-primary:
    backgroundColor: "{colors.azul-accion}"
    textColor: "{colors.superficie}"
    rounded: "{rounded.pill}"
  button-primary-hover:
    backgroundColor: "{colors.azul-accion-hover}"
  button-success:
    backgroundColor: "{colors.exito}"
    textColor: "{colors.superficie}"
    rounded: "{rounded.pill}"
  button-warning:
    backgroundColor: "{colors.ambar}"
    textColor: "{colors.tinta}"
    rounded: "{rounded.pill}"
  button-danger:
    backgroundColor: "{colors.peligro}"
    textColor: "{colors.superficie}"
    rounded: "{rounded.pill}"
  card:
    backgroundColor: "{colors.superficie}"
    rounded: "{rounded.card}"
  card-header:
    backgroundColor: "{colors.superficie}"
    textColor: "{colors.tinta}"
  card-header-banda:
    backgroundColor: "{colors.navy-marca}"
    textColor: "{colors.superficie}"
  input:
    backgroundColor: "{colors.superficie}"
    textColor: "{colors.texto}"
    rounded: "{rounded.control}"
---

# Design System: CINEFORM

La implementación normativa está en `app/public/css/main.css` (tokens `--cf-*` y utilidades). Este documento explica cómo aplicarla. Si ambos discrepan, manda el CSS y este archivo se corrige.

## Overview

CINEFORM es una herramienta de trabajo: coordinadores, facilitadores y participantes gestionan cursos, revisan postulaciones, pasan asistencia y certifican. El modo es **Operate**. La interfaz debe leerse rápido y comportarse igual en todas las pantallas. La marca se nota en los detalles (el navy, el cian, el fondo de cine en la entrada), no en la decoración.

Base técnica: plantilla KaiAdmin sobre Bootstrap 5.3, fuente Public Sans, iconos Font Awesome 5. `main.css` se carga después de KaiAdmin y antes de `@stack('styles')`, así que corrige a la plantilla sin tocar sus archivos.

Carácter: amable y redondeado. Botones en píldora, tarjetas de 16px, sombras cortas, fondo gris azulado claro con tarjetas blancas.

Decisiones del usuario (D4, 26/10/2026): unificar la paleta actual con un acabado más profesional y uniforme; primero la base y luego los módulos; fondo de cine solo en las pantallas de entrada y en estados vacíos.

## Colors

### Primary
- **Navy de marca** (#1e3a8a): títulos de marca, banda de encabezados con texto blanco, fondos suaves (`bg-primary-soft`), iconos de acento. Blanco encima: 10.36:1.
- **Azul de acción** (#1572e8): botón primario, pestaña activa, enlaces de acción, anillo de foco. Blanco encima: 4.56:1. Hover #105fc4.

### Secondary
- **Cian de marca** (#00c0ef): acento identitario. Solo como acento (borde, punto, icono grande). Nunca con texto blanco encima (2.15:1).
- **Cian legible** (#0e7490): la versión del cian para texto y para fondos con texto blanco (`btn-info`, `bg-info`). 5.36:1.

### Tertiary
- **Ámbar** (#f59e0b): destacados y la acción "Subsanar". Siempre con texto tinta (#0f172a, 8.31:1), nunca blanco.

### Estados
- **Éxito** (#198754, tinta #146c43): aprobar, activo, asistió.
- **Peligro** (#dc2626, tinta #b91c1c): rechazar definitivamente, faltas, errores.
- **Aviso** (tinta #b45309 sobre ámbar suave): pendientes que piden atención.
- Los verdes, ámbar y rojos de KaiAdmin (#31ce36, #ffad46, #f25961) no alcanzan 4.5:1 con texto blanco y quedan sustituidos en `main.css`.

### Neutral
- **Tinta** (#0f172a): títulos y texto sobre ámbar.
- **Texto** (#334155): texto corriente.
- **Texto tenue** (#64748b): metadatos y ayudas. 4.76:1 sobre blanco; no usar más claro para texto.
- **Borde** (#e2e8f0) y **borde fuerte** (#cbd5e1, campos de formulario).
- **Superficie** (#ffffff), **superficie sutil** (#f8fafc), **fondo de la aplicación** (#f1f5f9).

### Named Rules
- **Regla del cian.** El cian de marca es acento, no fondo de texto. Si lleva texto, usa el cian legible.
- **Regla de estado.** Verde, ámbar y rojo solo comunican estado o la acción que lo produce. No se usan como decoración.

## Typography

Public Sans en toda la aplicación, con cifras tabulares (`font-variant-numeric: tabular-nums`) para que las tablas de notas y asistencia alineen.

### Hierarchy
- **Headline** (700, 1.5rem, 1.25): título de la página (nombre del curso, "Panel de Certificación").
- **Title** (600, 1.125rem, 1.3): título de tarjeta.
- **Body** (400, 0.875rem, 1.5): texto de contenido y tablas.
- **Label** (600, 0.75rem, 0.5px, mayúsculas con `.uppercase`): etiquetas de dato ("DURACIÓN", "INICIO") y encabezados de columna.

## Layout

- Menú lateral oscuro de KaiAdmin y barra superior oscura; el contenido va en `.container-menu` sobre el fondo #f1f5f9.
- Ritmo de 8px (8 / 16 / 24). Tarjetas separadas por 16–24px.
- Puntos de corte de Bootstrap. En móvil (<576px) `main.css` reduce los `p-4`/`p-5`, los modales ocupan el ancho y los botones de acción (`.btn-action`) van a ancho completo.

## Elevation & Depth

Capas tonales: fondo gris, tarjeta blanca, sombra corta. Las sombras son ambientales, no estructurales.

### Shadow Vocabulary
- **xs** (`box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06)`): chips y elementos pequeños.
- **sm** (`box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06)`): controles elevados.
- **card** (`box-shadow: 0 8px 24px rgba(15, 23, 42, 0.07)`): tarjetas y hover de tarjetas (`.hover-shadow`).
- **modal** (`box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18)`): solo modales.

## Shapes

- Campos de formulario: 10px. Menús desplegables: 12px. Tarjetas y modales: 16px.
- Botones y badges: píldora (50rem). Dentro de un `input-group` el botón toma 10px para casar con el campo.
- Bordes de 1px en #e2e8f0.

## Components

### Buttons
- **Shape:** píldora (50rem), peso 600.
- **Primary:** azul de acción con texto blanco; hover #105fc4.
- **Estados:** `btn-success` (aprobar), `btn-warning` (subsanar, texto tinta), `btn-danger` (rechazar definitivamente), `btn-info` (cian legible).
- **Hover / Focus:** transición de 180ms con `cubic-bezier(0.2, 0, 0, 1)`; foco visible con contorno azul de 2px separado 2px.
- **Circular:** `.btn-circle` (34px) para acciones de icono en tablas.

### Chips
- Badges en píldora. Variantes suaves `badge-soft-success|danger|warning|primary|secondary`: fondo al 10–14 % y texto en la tinta del estado.

### Cards / Containers
- **Corner Style:** 16px.
- **Background:** blanco sobre #f1f5f9.
- **Shadow Strategy:** `shadow-card` o ninguna; nunca sombras más largas.
- **Encabezado:** blanco con borde inferior #e2e8f0 y texto tinta. Si la vista pide `text-white` o `bg-gradient-primary`, recibe una banda navy.

### Inputs / Fields
- **Style:** borde #cbd5e1, fondo blanco, 10px.
- **Focus:** borde azul de acción y anillo `0 0 0 3px rgba(21, 114, 232, 0.35)`. Sustituye el fondo amarillo #ffff99 anterior.

### Navigation
- Pestañas con línea inferior y contador en badge. `nav-pills.nav-secondary` activa en azul de acción.

## Do's and Don'ts

### Do:
- **Do** usar las utilidades de `main.css` (`shadow-card`, `shadow-xs`, `bg-*-soft`, `badge-soft-*`, `border-light-2`, `btn-circle`, `hover-shadow`, `hvr-push`, `transition-hover`, `custom-scrollbar`, `uppercase`, `cursor-pointer`).
- **Do** usar los tokens `var(--cf-*)` en cualquier `<style>` local que siga siendo necesario.
- **Do** comprobar 4.5:1 en texto normal antes de introducir un color nuevo.
- **Do** dejar el fondo de cine solo en `body.login` y en contenedores `.cf-entrada` (selección de perfil).

### Don't:
- **Don't** redefinir en una vista una utilidad de `main.css`. Antes de D4 había hasta cuatro `shadow-card` distintas.
- **Don't** poner texto blanco sobre #00c0ef, #31ce36, #ffad46 o #f25961.
- **Don't** forzar colores de encabezado con `!important` en `.card-header`.
- **Don't** introducir nuevos azules (#0d6efd, #3b82f6, #2563eb, #3f67f0): usar navy o azul de acción.
