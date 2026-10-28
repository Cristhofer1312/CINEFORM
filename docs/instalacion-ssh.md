# Instalación de CINEFORM en un servidor por SSH

Guía paso a paso para instalar el sistema desde cero en un servidor Linux al que se accede por SSH. El sistema corre en contenedores Docker (`docker-compose.yml` en la raíz): PHP-FPM 8.5 (`app`), tareas programadas (`scheduler`), nginx (`webserver`), PostgreSQL 18 (`db`) y pgAdmin (`pgadmin`).

Los comandos están pensados para **Ubuntu 22.04/24.04 o Debian 12**. Las líneas que empiezan con `$` se ejecutan **en el servidor**, salvo que diga lo contrario.

---

## Instalación automática (`instalar.sh`)

El script `instalar.sh`, en la raíz del repositorio, ejecuta los pasos 2 a 9 de esta guía. Después de conectarte (paso 1) y clonar el repositorio (paso 4):

```bash
$ cd /opt/cineform
$ bash instalar.sh
```

Te pide la URL pública, la contraseña de aplicación de Gmail y si quieres configurar el cortafuegos. Luego:

- instala Docker si falta;
- genera `docker-compose.override.yml` con claves aleatorias para PostgreSQL y pgAdmin;
- crea `app/.env` para producción;
- levanta los contenedores e instala las dependencias;
- ejecuta las migraciones y los seeders (los seeders solo si la base está vacía);
- deja las cachés listas y comprueba que la aplicación responda.

Puedes ejecutarlo de nuevo sin perder datos: conserva el override y el `.env` que ya existan.

Otras órdenes:

```bash
$ bash instalar.sh actualizar    # git pull, respaldo, migraciones, cachés y reinicio (paso 11)
$ bash instalar.sh respaldar     # respaldo de la base, archivos subidos y .env (paso 12)
```

Para instalar sin preguntas: `APP_URL=https://tu-dominio MAIL_PASSWORD=xxxx CONFIGURAR_UFW=si bash instalar.sh`.

El HTTPS con dominio (paso 10) y el respaldo diario por `cron` (paso 12) siguen siendo manuales. Las secciones siguientes explican cada paso por si prefieres hacerlo a mano o algo falla.

---

## 0. Requisitos

| Qué | Mínimo recomendado |
|---|---|
| Servidor | Ubuntu 22.04/24.04 o Debian 12, 64 bits |
| Recursos | 2 vCPU, 4 GB de RAM, 20 GB de disco |
| Acceso | Usuario con `sudo` y acceso SSH |
| Red | Puertos 22 (SSH) y 80/443 (web) abiertos; 8080 si no se usa dominio |
| Repositorio | Acceso de lectura a `github.com/Cristhofer1312/CINEFORM` |
| Correo | Una contraseña de aplicación de Gmail **nueva** para `tallercnac@gmail.com` |

Datos que conviene tener a mano: IP o dominio del servidor, usuario SSH, y las contraseñas nuevas que se van a definir (base de datos y pgAdmin).

---

## 1. Conectarse al servidor

Desde tu equipo (PowerShell, Git Bash o una terminal Linux/macOS):

```bash
ssh usuario@IP_DEL_SERVIDOR
```

### Opcional: entrar con llave en vez de contraseña

En tu equipo:

```bash
ssh-keygen -t ed25519 -C "cineform"
# Linux/macOS:
ssh-copy-id usuario@IP_DEL_SERVIDOR
# Windows (PowerShell):
type $env:USERPROFILE\.ssh\id_ed25519.pub | ssh usuario@IP_DEL_SERVIDOR "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys"
```

---

## 2. Preparar el servidor

```bash
$ sudo apt update && sudo apt upgrade -y
$ sudo apt install -y git curl ca-certificates ufw
$ sudo timedatectl set-timezone America/Caracas
```

Cortafuegos (deja SSH abierto **antes** de activarlo, o perderás la conexión):

```bash
$ sudo ufw allow OpenSSH
$ sudo ufw allow 80/tcp
$ sudo ufw allow 443/tcp
$ sudo ufw allow 8080/tcp     # solo si vas a entrar por http://IP:8080 sin dominio
$ sudo ufw enable
$ sudo ufw status
```

> Docker publica sus puertos saltándose `ufw`. Por eso en el paso 5 se cierran desde Docker los puertos de PostgreSQL (5432) y pgAdmin (8081).

---

## 3. Instalar Docker

Script oficial de Docker (instala Docker Engine y el plugin `docker compose`):

```bash
$ curl -fsSL https://get.docker.com | sudo sh
$ sudo usermod -aG docker $USER
$ exit
```

Vuelve a conectarte por SSH para que el grupo `docker` surta efecto y comprueba:

```bash
$ docker --version
$ docker compose version     # debe ser 2.24.4 o superior (se usan !reset y !override en el paso 5)
```

---

## 4. Descargar el código

```bash
$ sudo mkdir -p /opt/cineform
$ sudo chown $USER:$USER /opt/cineform
$ git clone https://github.com/Cristhofer1312/CINEFORM.git /opt/cineform
$ cd /opt/cineform
```

Si el repositorio es privado, GitHub pedirá usuario y un **token personal** (no la contraseña). Alternativa recomendada para servidores: una *deploy key* de solo lectura:

```bash
$ ssh-keygen -t ed25519 -f ~/.ssh/cineform_deploy -N ""
$ cat ~/.ssh/cineform_deploy.pub
# Pegar en GitHub → repositorio → Settings → Deploy keys → Add deploy key (sin permiso de escritura)
$ GIT_SSH_COMMAND="ssh -i ~/.ssh/cineform_deploy" git clone git@github.com:Cristhofer1312/CINEFORM.git /opt/cineform
```

A partir de aquí, todos los comandos se ejecutan dentro de `/opt/cineform`.

---

## 5. Ajustes de producción para Docker

`docker-compose.yml` trae valores de desarrollo: contraseña `123456` en PostgreSQL, PostgreSQL y pgAdmin expuestos a internet. **No se edita ese archivo**: se crea un `docker-compose.override.yml` solo en el servidor, que Docker Compose combina automáticamente.

```bash
$ nano docker-compose.override.yml
```

```yaml
services:
  db:
    environment:
      POSTGRES_PASSWORD: CAMBIA_ESTA_CLAVE_BD        # la misma que DB_PASSWORD en app/.env
    ports: !reset []                                # PostgreSQL deja de escuchar fuera de Docker

  pgadmin:
    environment:
      PGADMIN_DEFAULT_PASSWORD: CAMBIA_ESTA_CLAVE_PGADMIN
    ports: !override ["127.0.0.1:8081:80"]             # solo accesible por túnel SSH (ver paso 12)

  webserver:
    ports: !override ["8080:80"]                       # usa "127.0.0.1:8080:80" si pones nginx + HTTPS delante (paso 10)
```

> La contraseña de PostgreSQL solo se aplica **la primera vez** que se crea el volumen `pgdata18`. Si ya levantaste la base con otra clave, cámbiala dentro de PostgreSQL o borra el volumen (solo en una instalación vacía): `docker compose down -v`.

---

## 6. Configurar el entorno de Laravel (`app/.env`)

El proyecto Laravel vive en `app/`. La plantilla está en la raíz:

```bash
$ cp env.example app/.env
$ nano app/.env
```

Cambia estas variables (el resto puede quedar igual):

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=http://IP_DEL_SERVIDOR:8080      # o https://tu-dominio cuando tengas HTTPS
LOG_LEVEL=warning

DB_CONNECTION=pgsql
DB_HOST=db                               # nombre del servicio en docker-compose, no 127.0.0.1
DB_PORT=5432
DB_DATABASE=cineform
DB_USERNAME=laravel
DB_PASSWORD=CAMBIA_ESTA_CLAVE_BD         # la misma del override

CACHE_STORE=file                         # el proyecto no tiene migración de la tabla "cache"

MAIL_PASSWORD=CONTRASEÑA_DE_APLICACION_NUEVA
```

Importante:

- `APP_DEBUG=false` siempre en producción: con `true` se muestran rutas y mensajes internos.
- No reutilices la contraseña de Gmail que estuvo en `env.example`: está en el historial de git. Revócala y genera una nueva en la cuenta de Google (Seguridad → Contraseñas de aplicaciones).
- No pongas `CAPTCHA_DISABLE=true` en producción.
- `APP_KEY` se genera en el paso 8; déjala vacía por ahora.

Protege el archivo:

```bash
$ chmod 640 app/.env
```

---

## 7. Construir y levantar los contenedores

```bash
$ docker compose build
$ docker compose up -d
$ docker compose ps
```

Todos los servicios deben quedar en `running`. `db` y `app` deben mostrar `healthy` tras unos segundos. Si `app` se reinicia en bucle, revisa `docker compose logs app`.

---

## 8. Instalar dependencias e inicializar la aplicación

Permisos de escritura para PHP-FPM (usuario `www-data`, UID 33 dentro del contenedor):

```bash
$ sudo chown -R 33:33 app/storage app/bootstrap/cache
```

Dependencias de PHP (sin las de desarrollo):

```bash
$ docker compose exec -T app composer install --no-dev --optimize-autoloader --no-interaction
```

Clave de la aplicación, base de datos y enlace de archivos públicos:

```bash
$ docker compose exec -T app php artisan key:generate --force
$ docker compose exec -T app php artisan migrate --force
$ docker compose exec -T app php artisan db:seed --force
$ docker compose exec -T app php artisan storage:link
```

- `migrate` crea los esquemas `security`, `taller`, `comun` y `public`, incluidas las migraciones de los módulos (`app/Modules/*/Database/Migrations`).
- `db:seed` carga los catálogos (géneros, geografía, estados del curso…), los perfiles, los permisos y los usuarios iniciales. **Ejecútalo una sola vez**: no está pensado para correr sobre una base con datos.

Cachés de producción:

```bash
$ docker compose exec -T app php artisan config:cache
$ docker compose exec -T app php artisan route:cache
$ docker compose exec -T app php artisan view:cache
```

Reinicia PHP para que OPcache cargue el código nuevo (ver la nota del paso 11):

```bash
$ docker compose restart app scheduler
```

---

## 9. Verificar la instalación

```bash
$ curl -I http://localhost:8080
```

Debe responder `HTTP/1.1 200` o `302`. Luego, desde el navegador: `http://IP_DEL_SERVIDOR:8080`.

1. Inicia sesión con el usuario administrador que crea `PersonasSeeder` (`app/database/seeders/PersonasSeeder.php`).
2. **Cambia de inmediato las contraseñas de todos los usuarios sembrados.** Sus claves iniciales están escritas en el código fuente.
3. Revisa que el menú cargue los módulos (Seguridad, Registro, Taller) y que se pueda abrir el listado de cursos.
4. Prueba la recuperación de contraseña para confirmar que el correo sale.

Las tareas programadas (`taller:limpiar-wizards`, diaria) las ejecuta el contenedor `scheduler`. Comprueba que está vivo:

```bash
$ docker compose logs --tail=20 scheduler
```

---

## 10. Opcional: dominio y HTTPS

Con un dominio apuntando a la IP del servidor, pon nginx del sistema delante del contenedor y obtén el certificado con Let's Encrypt.

1. En `docker-compose.override.yml`, publica el contenedor solo en local: `ports: !override ["127.0.0.1:8080:80"]`, y aplica con `docker compose up -d`.
2. Instala nginx y certbot:

   ```bash
   $ sudo apt install -y nginx certbot python3-certbot-nginx
   $ sudo nano /etc/nginx/sites-available/cineform
   ```

   ```nginx
   server {
       listen 80;
       server_name tu-dominio.gob.ve;

       # Igual que nginx/nginx.conf: plantillas de certificado y PDF de hasta 20 MB.
       client_max_body_size 20M;

       location / {
           proxy_pass http://127.0.0.1:8080;
           proxy_set_header Host $host;
           proxy_set_header X-Real-IP $remote_addr;
           proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
           proxy_set_header X-Forwarded-Proto $scheme;
           proxy_read_timeout 120s;
       }
   }
   ```

   ```bash
   $ sudo ln -s /etc/nginx/sites-available/cineform /etc/nginx/sites-enabled/
   $ sudo rm -f /etc/nginx/sites-enabled/default
   $ sudo nginx -t && sudo systemctl reload nginx
   $ sudo certbot --nginx -d tu-dominio.gob.ve
   ```

3. En `app/.env` pon `APP_URL=https://tu-dominio.gob.ve` y vuelve a cachear:

   ```bash
   $ docker compose exec -T app php artisan config:cache
   $ docker compose restart app scheduler
   ```

4. Cierra el puerto 8080 en el cortafuegos: `sudo ufw delete allow 8080/tcp`.

> Si tras activar HTTPS los enlaces o formularios salen con `http://`, falta que Laravel confíe en el proxy: en `app/app/Http/Middleware/TrustProxies.php` pon `protected $proxies = '*';`. PHP recibe las peticiones desde la IP interna del contenedor nginx, no desde `127.0.0.1`; confiar en todos es seguro solo si el contenedor está publicado en `127.0.0.1:8080` (punto 1).

---

## 11. Actualizar a una versión nueva

```bash
$ cd /opt/cineform
$ git pull
$ docker compose build                      # solo si cambió el Dockerfile
$ docker compose up -d
$ docker compose exec -T app composer install --no-dev --optimize-autoloader --no-interaction
$ docker compose exec -T app php artisan migrate --force
$ docker compose exec -T app php artisan config:cache
$ docker compose exec -T app php artisan route:cache
$ docker compose exec -T app php artisan view:cache
$ docker compose restart app scheduler
```

> **OPcache no revisa si los archivos cambiaron** (`docker/php/zz-performance.ini`, `opcache.validate_timestamps=0`). Después de cualquier `git pull` o cambio en `.env`, PHP sigue sirviendo el código viejo hasta que reinicies `app` (o recargues PHP-FPM con `docker compose exec app sh -c 'kill -USR2 1'`).

Haz un respaldo (paso 12) **antes** de cada actualización que traiga migraciones.

---

## 12. Respaldos y acceso a la base de datos

### Respaldo manual

```bash
$ mkdir -p ~/respaldos
$ docker compose exec -T db pg_dump -U laravel -Fc cineform > ~/respaldos/cineform-$(date +%F).dump
$ sudo tar czf ~/respaldos/storage-$(date +%F).tar.gz -C /opt/cineform/app storage/app
$ cp /opt/cineform/app/.env ~/respaldos/env-$(date +%F)
```

El `.dump` guarda la base; el `.tar.gz` guarda los archivos subidos (requisitos, plantillas y certificados). El `.env` contiene `APP_KEY`: sin ella no se pueden leer los datos cifrados.

### Respaldo diario automático

```bash
$ crontab -e
```

```cron
30 2 * * * cd /opt/cineform && docker compose exec -T db pg_dump -U laravel -Fc cineform > $HOME/respaldos/cineform-$(date +\%F).dump && find $HOME/respaldos -name '*.dump' -mtime +14 -delete
```

Copia los respaldos fuera del servidor con regularidad (`scp` o `rsync` hacia otro equipo).

### Restaurar

```bash
$ docker compose exec -T db pg_restore -U laravel -d cineform --clean --if-exists < ~/respaldos/cineform-AAAA-MM-DD.dump
$ docker compose restart app scheduler
```

### pgAdmin por túnel SSH

pgAdmin solo escucha en `127.0.0.1:8081` del servidor. Para usarlo desde tu equipo:

```bash
ssh -L 8081:127.0.0.1:8081 usuario@IP_DEL_SERVIDOR
```

Abre `http://localhost:8081` en tu navegador. El servidor de base de datos que hay que registrar en pgAdmin es `db`, puerto `5432`, usuario `laravel`.

---

## 13. Problemas comunes

| Síntoma | Causa probable | Solución |
|---|---|---|
| Los cambios no se ven tras `git pull` | OPcache sin validación de timestamps | `docker compose restart app scheduler` |
| `502 Bad Gateway` | `app` caído o reiniciándose | `docker compose ps`, `docker compose logs app` |
| `500` al entrar | Error de Laravel | `docker compose exec app tail -n 50 storage/logs/laravel.log` |
| `Permission denied` en `storage/logs` | Dueño incorrecto | `sudo chown -R 33:33 app/storage app/bootstrap/cache` |
| `SQLSTATE[08006]` / no conecta a la BD | `DB_HOST` no es `db` o la clave no coincide con el override | Revisa `app/.env` y luego `php artisan config:cache` |
| `413 Request Entity Too Large` al subir | Límite de nginx | `client_max_body_size 20M` también en el nginx del sistema (paso 10) |
| No llegan correos | Contraseña de aplicación inválida o revocada | Genera una nueva y actualiza `MAIL_PASSWORD`, luego `config:cache` |
| `port is already allocated` | Otro servicio usa 8080/8081/5432 | Cambia el puerto en `docker-compose.override.yml` |
| Cambié `.env` y no aplica | Configuración cacheada | `php artisan config:cache` y reiniciar `app` |

Comandos útiles:

```bash
$ docker compose ps                         # estado de los servicios
$ docker compose logs -f --tail=100 app     # logs en vivo
$ docker compose exec app bash              # consola dentro del contenedor
$ docker compose exec app php artisan about # versión de Laravel/PHP y configuración
$ docker compose down                       # detener (los datos se conservan en el volumen)
```

> Nunca uses `docker compose down -v` en un servidor con datos: `-v` borra el volumen de PostgreSQL.
