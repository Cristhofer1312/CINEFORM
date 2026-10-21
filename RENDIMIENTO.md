# Rendimiento de CINEFORM

Resumen de la optimización aplicada en octubre de 2026 y de la operación diaria
con las cachés activas.

## Resultado

| Escenario | Antes | Después |
|---|---:|---:|
| `GET /security/login` (peticiones espaciadas) | 2,4–4,8 s | 0,29–0,36 s |
| `GET /security/home` (admin autenticado) | — | ~0,41 s |
| `GET /taller/mis-cursos` (admin autenticado) | — | ~0,41–0,54 s |
| `GET /taller/estadisticas` (admin autenticado) | — | ~0,42–0,55 s |
| `GET /taller/cursos` (admin autenticado) | — | ~0,46–0,88 s |

La primera petición después de reconstruir o reiniciar el contenedor tarda unos
4 s porque OPcache vuelve a compilar los scripts. A partir de ahí los tiempos son
los de la tabla.

## Causa

1. **OPcache validaba timestamps en cada petición.**
   El proyecto vive en un bind mount de Windows. Con
   `opcache.validate_timestamps=1` y `revalidate_freq=2`, cada petición que
   llegaba después de 2 s hacía `stat()` de cientos de archivos. Eso explicaba
   el patrón de 1 s / 2 s / 4 s y que un `404` también fuera lento.
2. **`module_path()` escaneaba los módulos repetidamente.**
   Con `modules.cache.enabled=false`, cada llamada a `module_path()` ejecutaba un
   `glob()` sobre `Modules/*/module.json`. Los cinco `*ServiceProvider` de los
   módulos lo llamaban varias veces durante `boot()`, sumando ~650 ms.

## Cambios aplicados

- `docker/php/zz-performance.ini`:
  - `opcache.validate_timestamps=0`
  - `opcache.revalidate_freq=0`
  - `opcache.file_update_protection=0`
  - `realpath_cache_ttl=600`
- `Dockerfile`: copia ese `.ini` a `/usr/local/etc/php/conf.d/`.
- `app/config/modules.php`: caché de módulos activa, `lifetime=3600`.
- Los `ServiceProvider` de `Taller`, `Security`, `Comun`, `Parametros` y
  `Registro` usan `dirname(__DIR__)` en lugar de `module_path()`, eliminando el
  escaneo por cada ruta de configuración, vistas, traducciones y migraciones.
- Cachés de Laravel generadas: `route:cache` y `view:cache`.
- Autoloader optimizado: `composer dump-autoload -o`.

`config:cache` **no** se deja activo en desarrollo: hace que PHPUnit ignore las
variables de `phpunit.xml` (por ejemplo `SESSION_DRIVER=array`), lo que produce
errores 419 de CSRF en las pruebas.

## Trabajar con las cachés

### Después de cambiar PHP, Blade, rutas o configuración

Con `opcache.validate_timestamps=0`, PHP-FPM no ve el código nuevo hasta
recargarse:

```powershell
docker compose exec app sh -c 'kill -USR2 1'
```

Si además cambió una ruta o una vista, regenerar la caché correspondiente:

```powershell
docker compose exec -u www-data app php artisan route:clear
docker compose exec -u www-data app php artisan route:cache
docker compose exec -u www-data app php artisan view:clear
docker compose exec -u www-data app php artisan view:cache
docker compose exec app sh -c 'kill -USR2 1'
```

### Después de cambiar `.env` o `config/*.php`

```powershell
docker compose exec -u www-data app php artisan config:clear
docker compose exec app sh -c 'kill -USR2 1'
```

Para producción se puede activar `config:cache`, pero en desarrollo conviene
dejarlo limpio para que los tests y los cambios de `.env` funcionen sin sorpresas.

### Después de agregar o quitar un módulo

La lista de módulos se cachea 1 hora. Invalidarla:

```powershell
docker compose exec -u www-data app php artisan cache:forget laravel-modules
```

### Después de editar `docker/php/zz-performance.ini`

El archivo se copia durante el build:

```powershell
docker compose up -d --build app
```

## Pruebas

```powershell
docker compose exec -u www-data app php artisan test
```

Resultado esperado: toda la suite pasa salvo `Tests\Feature\ExampleTest`, que
ya fallaba antes porque `GET /` responde `302` (redirección al login) y la prueba
espera `200`.

## Mejora opcional: exclusiones de Windows Defender

Si el equipo tiene Defender con protección en tiempo real, excluir el proyecto
y los datos de Docker reduce aún más el I/O. Requiere una consola de PowerShell
**como administrador**:

```powershell
Add-MpPreference -ExclusionPath (Get-Location)
Add-MpPreference -ExclusionPath "$env:LOCALAPPDATA\Docker"
Add-MpPreference -ExclusionPath "$env:APPDATA\Docker"
Add-MpPreference -ExclusionPath "C:\ProgramData\DockerDesktop"
```

Verificar:

```powershell
Get-MpPreference | Select-Object -ExpandProperty ExclusionPath
```
