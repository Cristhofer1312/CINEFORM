---
name: cineform-lote-backlog
description: Ejecuta un lote del backlog de CINEFORM (RF-xx, RNF-xx, UX-xx de REQUISITOS_RF_RNF.md) de principio a fin - verificar el hallazgo contra el código, cambio mínimo, prueba, Pint y actualización del documento. Úsalo siempre que el usuario pida implementar, arreglar, "ejecutar el siguiente lote", "el Tier 1/2", o mencione un ID de requisito o hallazgo de la auditoría, aunque no diga "lote" ni "backlog".
---

# Ejecutar un lote del backlog CINEFORM

Este skill convierte una fila del backlog de `REQUISITOS_RF_RNF.md` en un cambio verificado. El documento es una auditoría escrita en un momento concreto, y ya se ha comprobado que algunas filas están desactualizadas o son falsas. Por eso el primer paso es siempre verificar, no implementar.

## Flujo

1. **Localiza la fila.** Busca el ID en §3 (requisito), §5 (UX) o §7 (backlog). Anota el estado, la evidencia y la acción propuesta.

2. **Verifica el hallazgo contra el código actual.** Lee los archivos y líneas que cita la fila. Si la afirmación ya no se sostiene, para y dilo: no inventes un arreglo para un problema que no existe. Ejemplos reales de este proyecto: RF-16 decía "ruta sin llamador UI" y ya no era cierto; RF-31 decía "no valida duración" y sí valida.

   **Comprueba si el trabajo ya está hecho.** Antes de cambiar nada, busca señales de que la fila ya se resolvió:
   - Estado ✅, o el texto tachado / "Resuelto" en la fila o en §7.
   - El cambio pedido ya existe en el código (la guarda, la ruta, la validación, el campo).
   - Ya existe la prueba que la fila pide.

   Si alguna señal aparece, **no reimplementes**. Informa qué está hecho y con qué evidencia (archivo, línea, prueba). Luego separa lo que queda pendiente: la fila suele listar varias brechas, y puede que solo quede una. Trabaja únicamente esa brecha y di explícitamente que el resto ya estaba. Si no queda nada pendiente, para y pregunta si quieres cerrar la fila o revisar otra cosa. No toques código por una tarea que el documento ya cerró.

   Si lo pendiente es ambiguo (por ejemplo, la fila dice "corregir" pero el código cambió y no sabes qué falta), pregunta antes de editar.

3. **Comprueba bloqueos de decisión.** Si la fila depende de una decisión pendiente en §8 (D2–D6), no implementes la parte que depende de ella. Pregunta primero. Hoy, RF-37/RF-44 dependen de D3 (umbral de asistencia).

4. **Lee el código exacto antes de editar.** Las herramientas de edición exigen leer el archivo en la conversación, y los nombres de parámetros de ruta cambian entre rutas (`{curso}` vs `{curso_id}`, `{contenido}` vs `{contenido_id}`). Lee `Modules/*/Routes/web.php` para la ruta concreta.

5. **Cambio mínimo.** Aplica la guarda o corrección en el punto donde el fallo ocurre, no en una capa nueva. Reutiliza los patrones que ya usa el código cercano (por ejemplo, `esAprobado()` ya existe en `Inscripcion`; `PersonalData->user->perfiles()` ya se usa en otros puntos). No cambies comportamiento que la fila no pide.

6. **Prueba nueva.** Crea un archivo de prueba nuevo nombrado por tema en `app/tests/Feature/<Modulo>/`, no ampliar archivos grandes existentes. Usa la misma forma de fixtures que `HuecosFlujoTest` o `IntegridadFlujosTest`: usuarios por perfil con fallback a ids conocidos, curso con `Curso::withoutEvents`, y estado registrado en `taller.curso_estado`. Afirma el mensaje o código de error exacto, no solo "no falla".

7. **Verifica en Docker.** Ejecuta primero el filtro de la prueba nueva, luego la suite completa:
   ```
   docker compose exec -T app php artisan test --colors=never --filter=<NombreDelTest>
   docker compose exec -T app php artisan test --colors=never
   ```
   Si una prueba antigua falla, comprueba si el fallo es causado por tu cambio o si es una suposición del test que ya no cumple (por ejemplo, `RutasProtegidasTest` exige catalogar cada ruta mutante nueva). Corrígelo con la razón escrita en el código, no suprimiendo la prueba.

8. **Pint solo sobre lo que añadiste.** Los archivos tocados ya tienen issues de estilo heredados. Ejecuta `pint --test` sobre los archivos nuevos y verifica con `pint --test -v` que los issues restantes no están en tus líneas. No reformatees el archivo entero: el usuario tiene cambios sin commitear en los mismos archivos.

9. **Actualiza el documento en el mismo cambio.** Cambia el estado y la evidencia de la fila, y recalcula los totales de §2 desde las tablas (no a mano desde la fila). La regla de cierre 8 de §10.4 lo exige.

10. **No hagas commit salvo que lo pidan.** Los archivos suelen tener cambios previos sin commitear. Deja los cambios en el árbol de trabajo y di qué archivos son tuyos.

## Informe final

Responde con:
- Qué fila se ejecutó y si el hallazgo se confirmó, se refutó o estaba resuelto.
- Archivos cambiados (ruta y línea) y pruebas nuevas con su resultado.
- Resultado de la suite completa (número de pasados y fallos).
- Issues de Pint heredados que quedan, sin corregir.
- Lo que no quedó cubierto, por ejemplo una parte sin prueba o una verificación manual.
- Decisiones tomadas por defecto que el usuario debe confirmar.

## Por qué este orden

Verificar antes de editar evita arreglar fantasmas, que es justo lo que pasó con la versión manual del documento. Las pruebas antes que la confianza evitan dar algo por cerrado solo porque el código parece correcto. Y no hacer commit por defecto evita mezclar tu trabajo previo con el de este lote.
