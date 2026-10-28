---
name: cineform-respaldo
description: Crea, lista o restaura un respaldo del código de CINEFORM como tag local de git, incluidos los cambios sin commitear, sin tocar el árbol de trabajo. Úsalo antes de cualquier cambio amplio o difícil de revertir (D4 sistema de diseño, migraciones, refactors de varias pantallas), y siempre que el usuario diga "respaldo", "backup", "punto de restauración", "por si hay que revertir", "vuelve a como estaba" o "revierte el lote".
---

# Respaldo del código de CINEFORM

Este skill guarda una foto del código antes de un cambio grande y permite volver a ella. **Solo respalda código.** La base de datos no se respalda: es un ambiente de pruebas y el usuario lo decidió así. No ofrezcas `pg_dump` salvo que lo pida.

La foto es un tag anotado `respaldo/<fecha>-<nombre>`. Se crea desde un índice temporal, así que incluye los commits, los cambios sin commitear y los archivos nuevos, y no cambia la rama, el índice ni el árbol de trabajo del usuario. Respeta `.gitignore`, así que `.env`, `vendor/` y `node_modules/` no entran en la foto.

Script: `.claude/skills/cineform-respaldo/scripts/respaldo.sh` (ejecutar con `sh` desde la herramienta Bash).

## Crear un respaldo

1. Elige un nombre corto que diga qué viene después: `antes-d4`, `antes-d6-migracion`.
2. Ejecuta:
   ```
   sh .claude/skills/cineform-respaldo/scripts/respaldo.sh crear <nombre>
   ```
   Devuelve `TAG`, `BASE` (commit sobre el que se hizo), `ARCHIVOS` y `SIN_COMMITEAR` (archivos que difieren de HEAD).
3. Comprueba que `git status --short` es igual que antes de ejecutarlo.
4. Informa el tag al usuario. **No hagas push del tag** salvo que lo pida. El tag es local y basta para revertir en esta máquina.

## Ver qué cambió desde un respaldo

```
sh .claude/skills/cineform-respaldo/scripts/respaldo.sh listar
sh .claude/skills/cineform-respaldo/scripts/respaldo.sh diferencias <tag>
```

## Restaurar

Restaurar sobrescribe archivos. **Confirma siempre con el usuario antes**, y muéstrale primero el resultado de `diferencias` para que vea qué se pierde.

1. Si hay trabajo actual que quiera conservar, crea antes otro respaldo (`crear antes-de-revertir`). Así la restauración también se puede deshacer.
2. Restaura los archivos que conoce git:
   ```
   git restore --source=<tag> --worktree -- :/
   ```
   Esto devuelve cada archivo a su contenido del respaldo y quita los archivos con seguimiento que no existían en él. El índice y la rama no cambian.
3. Lista los archivos nuevos que aparecieron después del respaldo:
   ```
   sh .claude/skills/cineform-respaldo/scripts/respaldo.sh sobrantes <tag>
   ```
   Muestra la lista y bórralos solo si el usuario lo confirma. **No uses `git clean`**: también borraría archivos que no estaban commiteados pero sí estaban en el respaldo.
4. Verifica con `respaldo.sh diferencias <tag>`: no debe mostrar cambios.
5. Ejecuta la suite en Docker (`docker compose exec -T app php artisan test --colors=never`) e informa el resultado. Si el lote revertido tenía migraciones ya aplicadas, avisa de que la base de pruebas sigue con ese esquema y pregunta si hay que ejecutar `php artisan migrate:rollback` o `migrate:fresh --seed`.

## Revertir una sola pantalla o archivo

No hace falta restaurar todo:
```
git restore --source=<tag> --worktree -- <ruta>
```

## Limpieza

Los tags de respaldo se acumulan. Cuando el usuario commitee un lote y confirme que funciona, sugiere borrar los respaldos anteriores a ese commit (`git tag -d <tag>`). No los borres sin su confirmación.

## Informe

Responde con el tag, el commit base, cuántos archivos sin commitear incluye la foto y el comando exacto para revertir.
