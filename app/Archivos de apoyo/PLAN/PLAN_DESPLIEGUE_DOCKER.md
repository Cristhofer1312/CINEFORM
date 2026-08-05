# PLAN DE DESPLIEGUE CON DOCKER — CINEFORM

Guía paso a paso para instalar y poner en marcha el proyecto **CINEFORM** en un servidor **Debian 13 (Trixie)** usando **Docker**.

> **¿Para quién es esta guía?** Para alguien que nunca ha trabajado con Docker ni con servidores. Cada comando viene explicado y con su forma de verificar que funcionó. Copia y pega los comandos, no los escribas a mano.

> **Duración estimada:** 1 a 2 horas (depende de la velocidad del servidor e internet).

---

## Contenido

1. [¿Qué es Docker y por qué lo usamos?](#1-qué-es-docker-y-por-qué-lo-usamos)
2. [¿Qué contenedores usa este proyecto?](#2-qué-contenedores-usa-este-proyecto)
3. [Requisitos previos](#3-requisitos-previos)
4. [Paso 1: Conectar al servidor por SSH](#paso-1-conectar-al-servidor-por-ssh)
5. [Paso 2: Actualizar el sistema](#paso-2-actualizar-el-sistema)
6. [Paso 3: Instalar Docker y Docker Compose](#paso-3-instalar-docker-y-docker-compose)
7. [Paso 4: Corregir los archivos del proyecto](#paso-4-corregir-los-archivos-del-proyecto)
8. [Paso 5: Subir el código al servidor](#paso-5-subir-el-código-al-servidor)
9. [Paso 6: Crear el archivo `.env` de producción](#paso-6-crear-el-archivo-env-de-producción)
10. [Paso 7: Levantar los contenedores](#paso-7-levantar-los-contenedores)
11. [Paso 8: Instalar dependencias y crear la base de datos](#paso-8-instalar-dependencias-y-crear-la-base-de-datos)
12. [Paso 9: Verificar la instalación](#paso-9-verificar-la-instalación)
13. [Paso 10: Seguridad y mantenimiento](#paso-10-seguridad-y-mantenimiento)
14. [Solución de problemas (FAQ)](#14-solución-de-problemas-faq)
15. [Referencias](#15-referencias)

---

## 1. ¿Qué es Docker y por qué lo usamos?

Docker es un programa que permite **empaquetar una aplicación con todo lo que necesita para funcionar** (PHP, Nginx, base de datos, librerías) dentro de "cajas" llamadas **contenedores**.

Piénsalo como **mudanzas con cajas etiquetadas**:

- Cada caja (contenedor) trae TODO lo que necesita adentro: nada se instala "a mano" en el servidor.
- Si la caja se rompe, se destruye y se crea otra igual en segundos.
- Todas las cajas viven juntas en el mismo servidor pero sin estorbarse entre sí.

**Ventajas para este proyecto:**

- **Escalabilidad:** si el sistema crece, se pueden levantar varias copias de los contenedores para repartir la carga.
- **Seguridad:** cada servicio (web, base de datos, panel admin) está aislado. Si uno falla, los demás siguen funcionando.
- **Reproducibilidad:** la configuración queda escrita en archivos del proyecto (`Dockerfile` y `docker-compose.yml`). Cualquier otro servidor se levanta igual, sin "trucos que solo funcionan en mi máquina".

---

## 2. ¿Qué contenedores usa este proyecto?

El archivo `docker-compose.yml` define 4 servicios. Son como 4 cajas comunicadas entre sí por una red interna llamada `app-network`:

| Contenedor | Imagen | ¿Qué hace? | Puerto |
|---|---|---|---|
| `app` | `php:8.2-fpm` | Ejecuta PHP con Laravel. Se construye con el `Dockerfile` del proyecto | 9000 (interno) |
| `webserver` | `nginx:latest` | Servidor web: recibe las peticiones del navegador y las pasa a PHP | **8080** (acceso público) |
| `db` | `postgres:17` | Base de datos PostgreSQL. Guarda los datos en un volumen llamado `pgdata` | 5432 (interno) |
| `pgadmin` | `dpage/pgadmin4` | Panel gráfico para administrar la base de datos (opcional) | 8081 (solo localhost) |

```
            Navegador
               │  http://IP:8080
               ▼
        ┌───────────────┐
        │  webserver    │  Nginx
        └───────┬───────┘
                │  red interna
        ┌───────▼───────┐        ┌────────────┐
        │  app (PHP)    │◄──────►│ db (PG17)  │
        └───────────────┘        └────────────┘
```

El código de la aplicación está en la carpeta `app/` del proyecto y se comparte ("monta") dentro de los contenedores `app` y `webserver`, para que ambos vean los mismos archivos.

---

## 3. Requisitos previos

Antes de empezar, asegúrate de tener esto:

- **Servidor con Debian 13 (Trixie)** recién instalado, con acceso de administrador (`root` o un usuario con `sudo`).
- **Conexión SSH** desde tu computadora (o acceso directo por consola del proveedor).
- **Al menos 2 GB de RAM** y **2 vCPU**.
- **Puerto 8080 libre** (es el que usará el sitio por defecto).
- **El código del proyecto** en un repositorio Git (GitHub/GitLab) o en tu computadora para copiarlo.
- **Datos que deberás tener a mano** (los marcas como `???` en esta guía):
  - IP pública del servidor.
  - Correo y contraseña de Gmail usados por el sistema para enviar correos (SMTP).
  - Si el proyecto usará el CAPTCHA de Google reCAPTCHA, las claves `SITEKEY` y `SECRET`. **Nota:** este proyecto tiene su propio CAPTCHA interno (GD), así que por ahora puedes dejar esas claves vacías.

> **Si trabajas desde Windows:** abre **PowerShell** y usa el comando SSH directamente. También funciona con Visual Studio Code (extensión "Remote - SSH") o con el programa PuTTY.

---

## Paso 1: Conectar al servidor por SSH

SSH es el método seguro para "entrar" a la terminal del servidor desde tu computadora.

En tu computadora (PowerShell o terminal), escribe:

```bash
ssh tu_usuario@IP_DEL_SERVIDOR
```

- Cambia `tu_usuario` por el usuario del servidor (por ejemplo `root` o `debian`).
- Cambia `IP_DEL_SERVIDOR` por la IP pública del servidor.
- Te pedirá la contraseña: escríbela (no se ve mientras escribes, es normal).

**Verifica que estás en Debian 13:**

```bash
lsb_release -a
```

Debe aparecer `Description: Debian GNU/Linux 13 (trixie)`.

> A partir de aquí, **todos los comandos se ejecutan dentro del servidor**, en esta misma terminal.

---

## Paso 2: Actualizar el sistema

Es una buena práctica actualizar los paquetes del servidor antes de instalar nada.

```bash
sudo apt update
sudo apt upgrade -y
```

- `apt update`: actualiza la lista de programas disponibles.
- `apt upgrade -y`: instala las actualizaciones disponibles (el `-y` responde "sí" automáticamente).

**Verificación:** el comando termina sin errores rojos. Si el sistema te pide reiniciar, hazlo con `sudo reboot` y vuelve a conectar por SSH.

---

## Paso 3: Instalar Docker y Docker Compose

Ejecuta estos comandos **uno por uno** (copia y pega cada bloque). Instalan Docker desde el repositorio oficial y agregan tu usuario al grupo `docker` para no tener que escribir `sudo` en cada comando.

**3.1. Instalar dependencias:**

```bash
sudo apt install -y ca-certificates curl git
```

**3.2. Agregar la clave y el repositorio oficial de Docker:**

```bash
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/debian/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
```

```bash
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/debian $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
```

**3.3. Instalar Docker Engine + Compose v2:**

```bash
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

**3.4. Activar Docker para que arranque solo y añadir tu usuario al grupo `docker`:**

```bash
sudo systemctl enable --now docker
sudo usermod -aG docker $USER
```

> El segundo comando evita escribir `sudo` delante de `docker`. **Debes cerrar la sesión SSH y volver a entrar** (escribe `exit` y conecta de nuevo) para que el cambio surta efecto.

**Verificación** (después de reconectar):

```bash
docker --version
docker compose version
```

Deben mostrar las versiones instaladas, por ejemplo `Docker version 27.x` y `Docker Compose version v2.x`.

Para comprobar que Docker funciona de verdad:

```bash
docker run hello-world
```

Debe imprimir un mensaje de bienvenida. Ese contenedor de prueba se elimina automáticamente o lo puedes borrar luego.

---

## Paso 4: Corregir los archivos del proyecto

El proyecto ya trae los archivos de Docker, pero tienen **3 huecos** que hay que corregir antes de desplegar en producción. Son archivos de texto; puedes editarlos con `nano` en el servidor (`sudo nano archivo`).

> **Importante:** estas correcciones hay que hacerlas **en el repositorio** (idealmente en tu computadora y luego subirlas con Git), para que queden guardadas. Si no, se pierden.

### 4.1. `Dockerfile` — faltan ImageMagick, Ghostscript y Node.js

**Problema:** el contenedor PHP no tiene la extensión `imagick` (necesaria para poner la marca de agua en los certificados PDF) ni `node`/`npm` (necesarios para compilar los estilos con Vite). Además trae Xdebug, que es solo para desarrollo.

**Solución:** reemplaza TODO el contenido del `Dockerfile` por este:

```dockerfile
# Dockerfile de producción para CINEFORM (PHP-FPM 8.2 + Laravel 10)

FROM php:8.2-fpm

# Herramientas del sistema y librerías para las extensiones PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libmagickwand-dev \
    ghostscript \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql pgsql gd zip \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Node.js y npm para compilar los assets con Vite (npm run build)
RUN apt-get update && apt-get install -y nodejs npm

# Instalar Composer (gestor de dependencias de PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Crear y dar permisos a los directorios que Laravel necesita escribir
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
```

**Qué agrega cada parte:**
- `libmagickwand-dev` + `pecl install imagick` → habilita la extensión `Imagick` en PHP (marcas de agua en PDF).
- `ghostscript` → permite que ImageMagick lea archivos PDF.
- `nodejs npm` → permite ejecutar `npm run build` dentro del contenedor.
- Se eliminó `xdebug` (herramienta de depuración, no debe ir a producción).

### 4.2. `docker-compose.yml` — quitar Xdebug, endurecer contraseñas y añadir `restart`

**Problemas:** monta un archivo `xdebug.ini` que no existe (rompe el arranque), usa la contraseña por defecto `secret` en la base de datos, y expone pgAdmin a internet.

**Solución:** reemplaza TODO el contenido por este:

```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    restart: unless-stopped
    volumes:
      - ./app:/var/www/html
    depends_on:
      - db
    networks:
      - app-network

  webserver:
    image: nginx:latest
    restart: unless-stopped
    ports:
      - "8080:80"
    volumes:
      - ./app:/var/www/html
      - ./nginx/nginx.conf:/etc/nginx/conf.d/default.conf:ro
    depends_on:
      - app
    networks:
      - app-network

  db:
    image: postgres:17
    restart: unless-stopped
    environment:
      POSTGRES_USER: laravel
      POSTGRES_PASSWORD: CAMBIA_ESTA_CLAVE_BD
      POSTGRES_DB: cineform
    volumes:
      - pgdata:/var/lib/postgresql/data
    networks:
      - app-network

  pgadmin:
    image: dpage/pgadmin4
    restart: unless-stopped
    environment:
      PGADMIN_DEFAULT_EMAIL: admin@cineform.gob.ve
      PGADMIN_DEFAULT_PASSWORD: CAMBIA_ESTA_CLAVE_ADMIN
    ports:
      - "127.0.0.1:8081:80"
    depends_on:
      - db
    networks:
      - app-network

volumes:
  pgdata:

networks:
  app-network:
    driver: bridge
```

**Qué cambia:**
- **Se eliminó** la línea que montaba `./docker/php/conf.d/xdebug.ini` (no existía y provocaba error).
- `restart: unless-stopped` → los contenedores se reinician solos si el servidor se reinicia o si fallan.
- `POSTGRES_PASSWORD` y pgAdmin con contraseñas que **debes cambiar** (anota la de la BD, la necesitarás en el paso 6).
- pgAdmin solo se expone en `127.0.0.1` (solo el servidor puede verlo, no internet).

### 4.3. Crear el archivo `.dockerignore`

Este archivo le dice a Docker qué carpetas **no** debe empaquetar al construir, para que el build sea rápido y limpio.

Crea un archivo llamado `.dockerignore` en la **raíz del proyecto** (junto a `docker-compose.yml`) con este contenido:

```
.git
.vscode
.devcontainer
node_modules
**/node_modules
vendor
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
app/.env
*.log
```

---

## Paso 5: Subir el código al servidor

Tienes dos formas de llevar el proyecto al servidor. Elige una.

### Opción A: Con Git (recomendada)

En el servidor:

```bash
cd /opt
sudo git clone URL_DEL_REPOSITORIO cineform
sudo chown -R $USER:$USER /opt/cineform
cd cineform
```

- `cd /opt` → entra a la carpeta donde se suelen guardar las aplicaciones.
- `git clone ...` → descarga el código (cambia `URL_DEL_REPOSITORIO`).
- `chown` → te da permiso para trabajar con los archivos.

### Opción B: Copiar desde tu computadora (SCP)

Si el código está en tu computadora y no en un repositorio, desde tu computadora ejecuta:

```bash
scp -r C:\ruta\al\proyecto\CINEFORM tu_usuario@IP_DEL_SERVIDOR:/opt/cineform
```

**Verifica la estructura final** (debe verse así):

```bash
cd /opt/cineform
ls
```

Debe aparecer: `app`, `Dockerfile`, `docker-compose.yml`, `nginx`, `env.example`, `xdebug.ini`, `.dockerignore`, etc.

---

## Paso 6: Crear el archivo `.env` de producción

El archivo `.env` contiene la configuración secreta de Laravel (claves, contraseñas, conexión a la BD). **No debe ir al repositorio.**

Crea el archivo partiendo del ejemplo:

```bash
cd /opt/cineform/app
cp .env.example .env
```

Luego edítalo:

```bash
nano .env
```

Y déjalo con estos valores (cambia todo lo que lleve `???`). **El punto más importante:** `DB_HOST` debe ser `db` (el nombre del contenedor), NO `127.0.0.1`.

```ini
APP_NAME="CNAC"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=America/Caracas
APP_URL=http://IP_DEL_SERVIDOR:8080

API_FILES_ROUTE=http://IP_DEL_SERVIDOR/api/

APP_LOCALE=es
APP_FALLBACK_LOCALE=en

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=cineform
DB_USERNAME=laravel
DB_PASSWORD=CAMBIA_ESTA_CLAVE_BD

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_DRIVER=file

QUEUE_CONNECTION=sync

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

CACHE_STORE=database
CACHE_PREFIX=

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu.correo@gmail.com
MAIL_PASSWORD=TU_APP_PASSWORD_NUEVA
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu.correo@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_DEFAULT_PASSWORD=CAMBIA_ESTA_CLAVE_ADMIN_INICIAL
ADMIN_EMAIL=admin@cineform.gob.ve

NOCAPTCHA_SITEKEY=
NOCAPTCHA_SECRET=
```

**Explicación de los valores clave:**

| Variable | Valor | Por qué |
|---|---|---|
| `APP_ENV=production` | `production` | Modo producción (más rendimiento, menos errores visibles) |
| `APP_KEY=` | **vacío** | Se genera automáticamente en el paso 8. Nunca reutilices la del `env.example` (está publicada) |
| `APP_DEBUG=false` | `false` | No muestra errores internos a los visitantes |
| `DB_HOST=db` | `db` | Es el nombre del contenedor de la base de datos |
| `DB_PASSWORD` | la misma que pusiste en `docker-compose.yml` | Deben coincidir, o la aplicación no podrá conectar |
| `ADMIN_DEFAULT_PASSWORD` | una contraseña fuerte | Es la del usuario administrador inicial, se usa SOLO al migrar |
| `MAIL_*` | tu correo Gmail y **app password nueva** | La contraseña que había en `env.example` está expuesta; genera una nueva en tu cuenta de Google (Configuración → Seguridad → Contraseñas de aplicaciones) |
| `NOCAPTCHA_*` | vacío | El sistema tiene su propio CAPTCHA interno; si más adelante quieres reCAPTCHA de Google, llena estas claves |

Para guardar en `nano`: `Ctrl + O`, Enter, y `Ctrl + X`.

> **¿Cambiaste la clave de la BD en el paso 4?** Revisa que `POSTGRES_PASSWORD` en `docker-compose.yml` y `DB_PASSWORD` en `.env` sean exactamente la misma.

---

## Paso 7: Levantar los contenedores

Vuelve a la raíz del proyecto y construye las imágenes:

```bash
cd /opt/cineform
docker compose build
```

- `build` compila la imagen de PHP según el `Dockerfile`. La primera vez tarda unos minutos (descarga paquetes).

Luego arranca todos los contenedores en segundo plano:

```bash
docker compose up -d
```

- `up` crea y arranca los contenedores.
- `-d` los deja corriendo en segundo plano ("detached"), para que sigas usando la terminal.

**Verificación:** mira el estado de los contenedores:

```bash
docker compose ps
```

Los 4 servicios deben aparecer en estado `Up` (arriba). Algo así:

```
NAME        STATUS        PORTS
app         Up 2 minutes  9000
webserver   Up 2 minutes  0.0.0.0:8080->80/tcp
db          Up 2 minutes  5432
pgadmin     Up 2 minutes  127.0.0.1:8081->80/tcp
```

Si alguno está en `Restarting` o `Exited`, revisa la sección [Solución de problemas](#14-solución-de-problemas-faq).

---

## Paso 8: Instalar dependencias y crear la base de datos

Ahora entra **dentro** del contenedor `app` para ejecutar los comandos de Laravel:

```bash
docker compose exec app bash
```

Estás dentro del contenedor (el nombre de la terminal cambia). Ejecuta:

**8.1. Instalar las dependencias de PHP (Laravel):**

```bash
composer install --no-dev --optimize-autoloader
```

- `composer` instala los paquetes de PHP del proyecto (los descarga desde internet).
- `--no-dev` omite las herramientas de desarrollo.
- Tarda un poco. Debe terminar sin errores.

**8.2. Generar la clave de seguridad de la aplicación:**

```bash
php artisan key:generate
```

Esto rellena la variable `APP_KEY` en tu `.env` (por eso la dejamos vacía).

**8.3. Crear las tablas y los datos iniciales en la base de datos:**

```bash
php artisan migrate --seed
```

- `migrate` crea todas las tablas (incluidos los 5 esquemas: `security`, `comun`, `taller`, `registro`, `parametros`).
- `--seed` inserta los datos iniciales (perfiles, permisos, catálogos) y crea el usuario administrador con la clave de `ADMIN_DEFAULT_PASSWORD`.

**8.4. Instalar y compilar los estilos (Vite):**

```bash
npm install
npm run build
```

- `npm install` descarga las librerías de JavaScript del proyecto.
- `npm run build` genera los archivos CSS/JS compilados (el sitio se vería sin estilos si omitieras este paso).

**8.5. Vincular la carpeta de archivos subidos:**

```bash
php artisan storage:link
```

Crea un acceso directo para que los archivos subidos (fotos, PDFs) sean visibles en la web.

**8.6. Corregir permisos de escritura:**

```bash
chown -R www-data:www-data storage bootstrap/cache
```

- Laravel escribe en esas carpetas (sesiones, caché, logs). Sin permisos daría error 500.

**8.7. Salir del contenedor:**

```bash
exit
```

> **¿En un futuro despliegue solo cambiaron tablas?** Basta con `docker compose exec app php artisan migrate` (sin `--seed`).

---

## Paso 9: Verificar la instalación

Desde tu computadora, abre el navegador y entra a:

```
http://IP_DEL_SERVIDOR:8080
```

(Reemplaza `IP_DEL_SERVIDOR` por la IP pública del servidor).

**Pruebas recomendadas:**

1. Se carga la página principal (con sus estilos).
2. La página de registro funciona y muestra el CAPTCHA interno.
3. Puedes iniciar sesión con el usuario administrador (`ADMIN_EMAIL` / `ADMIN_DEFAULT_PASSWORD`).
4. Si subes un certificado PDF, se ve con su marca de agua (esto confirma que ImageMagick funciona).

**Si algo falla, revisa los registros (logs) de los contenedores:**

```bash
docker compose logs -f app
```

- `-f` muestra los registros en tiempo real (para salir: `Ctrl + C`).
- Los errores de Laravel también quedan en `app/storage/logs/laravel.log`.

**Comandos útiles de gestión:**

```bash
docker compose ps              # ver estado
docker compose restart app     # reiniciar un contenedor
docker compose logs webserver  # ver registros del servidor web
docker compose down            # detener TODO (borra los contenedores, NO los datos)
docker compose up -d           # volver a levantar
```

> **Importante:** `down` no borra la base de datos (está en el volumen `pgdata`). Tampoco borra el código (está en la carpeta `app/`).

---

## Paso 10: Seguridad y mantenimiento

### 10.1. Contraseñas que SÍ o SÍ debes cambiar ya

| Quién | Dónde | Qué |
|---|---|---|
| Base de datos | `docker-compose.yml` (`POSTGRES_PASSWORD`) | La `secret` original ya no debe existir |
| pgAdmin | `docker-compose.yml` (`PGADMIN_DEFAULT_PASSWORD`) | Usar una contraseña fuerte |
| Administrador del sistema | `.env` (`ADMIN_DEFAULT_PASSWORD`) | Cambiar tras el primer inicio de sesión |
| Correo Gmail | Cuenta de Google | La app password que estaba en `env.example` está expuesta: generar una nueva |
| Clave de la app | `.env` (`APP_KEY`) | Ya regenerada en el paso 8 |

### 10.2. Copias de seguridad (backups) de la base de datos

Los datos de PostgreSQL están en el volumen `pgdata`. Para protegerlos, haz una copia periódica con `pg_dump`.

**Prueba manual** (desde la raíz del proyecto):

```bash
docker compose exec db pg_dump -U laravel cineform > backup_cineform_$(date +%Y%m%d).sql
```

Esto crea un archivo `.sql` con todo el contenido de la base de datos.

**Automatización con cron** (una copia cada día a las 3 AM):

```bash
sudo mkdir -p /backups
sudo crontab -e
```

Agrega esta línea al final y guarda:

```cron
0 3 * * * cd /opt/cineform && docker compose exec -T db pg_dump -U laravel cineform > /backups/cineform_$(date +\%Y\%m\%d).sql 2>> /backups/errores.log
```

> El `-T` es necesario para que el comando funcione sin terminal interactiva (como en cron). No olvides descargar esas copias a otro sitio (tu computadora o almacenamiento en la nube): un backup que vive en el mismo servidor no te protege de que el servidor falle.

### 10.3. HTTPS (conexión segura)

Por defecto el sitio funciona con `http`. Para producción es recomendable (casi obligatorio) usar `https`. La forma más fácil es instalar **Caddy**, un proxy inverso que genera certificados automáticos y gratis.

```bash
sudo apt install -y caddy
```

Crea el archivo `/etc/caddy/Caddyfile`:

```
tudominio.com {
    reverse_proxy 127.0.0.1:8080
}
```

Recarga Caddy:

```bash
sudo systemctl reload caddy
```

Ahora tu sitio se verá en `https://tudominio.com` con candado de seguridad. En `.env` cambia `APP_URL` a `https://tudominio.com` y vuelve a compilar/limpiar caché:

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
```

> Si no tienes dominio, Caddy también sirve con HTTPS usando la IP, pero es menos práctico. Lo mínimo es mantenerlo en `http://IP:8080` para uso interno.

### 10.4. Actualizaciones periódicas

Mensualmente (o cuando lo decida el equipo):

```bash
sudo apt update && sudo apt upgrade -y
```

Y para el proyecto (cuando haya cambios en el código o en `Dockerfile`):

```bash
cd /opt/cineform
git pull
docker compose build
docker compose up -d
docker compose exec app php artisan migrate
docker compose exec app npm install && docker compose exec app npm run build
```

---

## 14. Solución de problemas (FAQ)

| Síntoma | Causa probable | Solución |
|---|---|---|
| `docker: command not found` | Docker no está instalado o no se ha vuelto a entrar a la sesión | Repetir el Paso 3 y cerrar/reabrir SSH |
| `Got permission denied while trying to connect to the Docker daemon socket` | Tu usuario no está en el grupo `docker` | `sudo usermod -aG docker $USER` y reconectar |
| El contenedor `app` se queda en `Restarting` | El Dockerfile aún es el viejo (sin las correcciones) | `docker compose build --no-cache app` y `docker compose up -d` |
| `Class "Imagick" not found` | Falta la extensión imagick en el contenedor | Confirmar que el `Dockerfile` incluye `pecl install imagick` y reconstruir la imagen |
| Error de conexión a la base de datos (`Connection refused`) | `DB_HOST` no es `db`, o la contraseña no coincide con la de compose | Revisar `.env`: `DB_HOST=db`, `DB_PASSWORD` igual en ambos archivos |
| Página en blanco / error 500 | Caché de Laravel con valores viejos o permisos | `docker compose exec app php artisan config:clear && php artisan cache:clear` y verificar `chown -R www-data:www-data storage bootstrap/cache` |
| El sitio se ve sin estilos ni JS | No se ejecutó el build de Vite | `docker compose exec app npm install && npm run build` |
| `address already in use` al levantar | El puerto 8080 está ocupado por otro programa | Cambiar `"8080:80"` por `"8081:80"` en `docker-compose.yml` (y ajustar `APP_URL`) |
| `The stream or file "laravel.log" could not be opened in append mode` | Permisos de `storage/logs` | Ejecutar `chown -R www-data:www-data storage bootstrap/cache` |
| No llegan correos | La contraseña de Gmail no es una "app password" o está expuesta | Generar una nueva app password en Google y actualizar `MAIL_*` en `.env` |
| `No application encryption key` | No se generó `APP_KEY` | `docker compose exec app php artisan key:generate` |

---

## 15. Referencias

- Documentación oficial de Docker: https://docs.docker.com/
- Documentación de Docker Compose: https://docs.docker.com/compose/
- Imágenes oficiales usadas: `php:8.2-fpm`, `nginx:latest`, `postgres:17`, `dpage/pgadmin4`
- Documentación de Laravel (despliegue): https://laravel.com/docs/10.x/deployment
- Descripción técnica completa del sistema: [`ESTRUCTURA_SISTEMA.md`](./ESTRUCTURA_SISTEMA.md)

---

*Fin del plan. Después de completar el Paso 9, el sistema CINEFORM debería estar operativo en el servidor.*
