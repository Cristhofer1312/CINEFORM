#!/usr/bin/env bash
# Instalador de CINEFORM para servidores Ubuntu/Debian (ver docs/instalacion-ssh.md).
#
# Uso, desde la raíz del repositorio clonado:
#   bash instalar.sh               instalación inicial (se puede repetir sin romper nada)
#   bash instalar.sh actualizar    git pull + respaldo + migraciones + cachés
#   bash instalar.sh respaldar     respaldo de la base, archivos subidos y .env
#
# Variables opcionales para no responder preguntas:
#   APP_URL=https://cineform.gob.ve MAIL_PASSWORD=xxxx PUERTO_WEB=8080 CONFIGURAR_UFW=si bash instalar.sh

set -Eeuo pipefail

RAIZ="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$RAIZ"

ENV_FILE="app/.env"
OVERRIDE="docker-compose.override.yml"
RESPALDOS="${RESPALDOS:-$HOME/respaldos}"
PUERTO_WEB="${PUERTO_WEB:-8080}"
WWW_UID=33   # www-data dentro del contenedor php-fpm

verde() { printf '\033[32m%s\033[0m\n' "$*"; }
amarillo() { printf '\033[33m%s\033[0m\n' "$*"; }
paso() { printf '\n\033[1;36m==> %s\033[0m\n' "$*"; }
falla() { printf '\033[31mError: %s\033[0m\n' "$*" >&2; exit 1; }
trap 'printf "\033[31mFalló la línea %s: %s\033[0m\n" "$LINENO" "$BASH_COMMAND" >&2' ERR

if [ "$(id -u)" -eq 0 ]; then SUDO=""; else SUDO="sudo"; fi

clave_aleatoria() {
    # Solo letras y números: se escribe en YAML y en .env sin escapar.
    LC_ALL=C tr -dc 'A-Za-z0-9' </dev/urandom | head -c 32 || true
}

# Escribe CLAVE=valor en app/.env, reemplazando la línea si ya existe.
set_env() {
    local clave="$1" valor="$2" escapado
    escapado=$(printf '%s' "$valor" | sed -e 's/[\/&|\\]/\\&/g')
    if grep -qE "^${clave}=" "$ENV_FILE"; then
        sed -i -E "s|^${clave}=.*|${clave}=${escapado}|" "$ENV_FILE"
    else
        printf '%s=%s\n' "$clave" "$valor" >>"$ENV_FILE"
    fi
}

get_env() {
    grep -E "^$1=" "$ENV_FILE" 2>/dev/null | tail -n1 | cut -d= -f2- | tr -d '"' || true
}

preguntar() {
    # preguntar VARIABLE "Texto" "valor por defecto" [oculto]
    local var="$1" texto="$2" defecto="${3:-}" oculto="${4:-}" respuesta
    if [ -n "${!var:-}" ]; then return; fi
    if [ ! -t 0 ]; then printf -v "$var" '%s' "$defecto"; return; fi
    if [ -n "$oculto" ]; then
        read -r -s -p "$texto: " respuesta; echo
    else
        read -r -p "$texto [${defecto}]: " respuesta
    fi
    printf -v "$var" '%s' "${respuesta:-$defecto}"
}

# docker sin sudo solo funciona tras volver a iniciar sesión en el grupo docker.
docker_cmd() {
    if docker info >/dev/null 2>&1; then docker "$@"; else $SUDO docker "$@"; fi
}
dc() { docker_cmd compose "$@"; }
artisan() { dc exec -T app php artisan "$@"; }

permisos_storage() {
    $SUDO chown -R "$WWW_UID:$WWW_UID" app/storage app/bootstrap/cache
}

cachear() {
    artisan config:cache
    artisan route:cache || amarillo "route:cache falló; la aplicación funciona igual, solo sin caché de rutas."
    artisan view:cache
    permisos_storage
    # OPcache no valida timestamps (docker/php/zz-performance.ini): sin reiniciar se sirve el código viejo.
    dc restart app scheduler
}

verificar() {
    paso "Verificando"
    local codigo
    codigo=$(curl -s -o /dev/null -w '%{http_code}' "http://localhost:${PUERTO_WEB}" || true)
    case "$codigo" in
        200|302) verde "La aplicación responde (HTTP $codigo) en http://localhost:${PUERTO_WEB}" ;;
        *) amarillo "La aplicación respondió HTTP '${codigo}'. Revisa: docker compose logs app"; return 1 ;;
    esac
}

respaldar() {
    paso "Respaldando en $RESPALDOS"
    mkdir -p "$RESPALDOS"
    chmod 700 "$RESPALDOS"
    local fecha
    fecha=$(date +%F-%H%M)
    dc exec -T db pg_dump -U laravel -Fc cineform >"$RESPALDOS/cineform-$fecha.dump"
    $SUDO tar czf "$RESPALDOS/storage-$fecha.tar.gz" -C "$RAIZ/app" storage/app
    $SUDO cp "$ENV_FILE" "$RESPALDOS/env-$fecha"
    $SUDO chown "$(id -u):$(id -g)" "$RESPALDOS"/*-"$fecha"*
    verde "Respaldo listo: $RESPALDOS/*-$fecha*"
}

instalar_docker() {
    paso "Docker"
    if command -v docker >/dev/null 2>&1; then
        verde "Docker ya está instalado: $(docker --version)"
    else
        curl -fsSL https://get.docker.com | $SUDO sh
        if [ -n "$SUDO" ]; then
            $SUDO usermod -aG docker "$USER"
            amarillo "Se agregó $USER al grupo docker; en esta sesión se usará sudo."
        fi
    fi
    docker_cmd compose version >/dev/null 2>&1 || falla "Falta el plugin 'docker compose'."
}

preparar_sistema() {
    paso "Paquetes del sistema"
    command -v apt-get >/dev/null 2>&1 || falla "Este script está hecho para Ubuntu/Debian (apt)."
    $SUDO apt-get update -y
    $SUDO apt-get install -y git curl ca-certificates openssl
    $SUDO timedatectl set-timezone America/Caracas 2>/dev/null || true

    preguntar CONFIGURAR_UFW "¿Configurar el cortafuegos ufw (SSH, 80, 443 y ${PUERTO_WEB})? si/no" "no"
    if [ "$CONFIGURAR_UFW" = "si" ]; then
        $SUDO apt-get install -y ufw
        $SUDO ufw allow OpenSSH   # antes de activar, para no perder la conexión
        $SUDO ufw allow 80/tcp
        $SUDO ufw allow 443/tcp
        $SUDO ufw allow "${PUERTO_WEB}/tcp"
        $SUDO ufw --force enable
    fi
}

crear_override() {
    paso "Ajustes de producción de Docker ($OVERRIDE)"
    if [ -f "$OVERRIDE" ]; then
        verde "$OVERRIDE ya existe; se conserva."
        return
    fi
    if docker_cmd volume ls -q | grep -q '_pgdata18$'; then
        amarillo "Ya existe un volumen de PostgreSQL: la clave nueva no se aplicará a esa base."
        amarillo "Si es una instalación vacía, bórralo con 'docker compose down -v' y vuelve a ejecutar."
    fi
    local clave_bd clave_pgadmin
    clave_bd=$(clave_aleatoria)
    clave_pgadmin=$(clave_aleatoria)
    cat >"$OVERRIDE" <<YAML
# Generado por instalar.sh el $(date +%F). Solo existe en este servidor: no se sube a git.
services:
  db:
    environment:
      POSTGRES_PASSWORD: ${clave_bd}
    ports: !reset []

  pgadmin:
    environment:
      PGADMIN_DEFAULT_PASSWORD: ${clave_pgadmin}
    ports: !override ["127.0.0.1:8081:80"]

  webserver:
    ports: !override ["${PUERTO_WEB}:80"]
YAML
    chmod 600 "$OVERRIDE"
    verde "Claves aleatorias de PostgreSQL y pgAdmin guardadas en $OVERRIDE (permisos 600)."
}

clave_bd_override() {
    grep -E '^\s+POSTGRES_PASSWORD:' "$OVERRIDE" | head -n1 | awk '{print $2}'
}

crear_env() {
    paso "Entorno de Laravel ($ENV_FILE)"
    if [ -f "$ENV_FILE" ]; then
        verde "$ENV_FILE ya existe; solo se completan las claves vacías."
    else
        cp env.example "$ENV_FILE"
        local ip_defecto
        ip_defecto="http://$(hostname -I 2>/dev/null | awk '{print $1}'):${PUERTO_WEB}"
        preguntar APP_URL "URL pública del sistema" "$ip_defecto"
        preguntar MAIL_PASSWORD "Contraseña de aplicación de Gmail (Enter para dejarla vacía)" "" oculto

        set_env APP_ENV production
        set_env APP_DEBUG false
        set_env APP_URL "$APP_URL"
        set_env LOG_LEVEL warning
        set_env DB_CONNECTION pgsql
        set_env DB_HOST db
        set_env DB_PORT 5432
        set_env DB_DATABASE cineform
        set_env DB_USERNAME laravel
        set_env DB_PASSWORD "$(clave_bd_override)"
        # El proyecto no tiene migración de la tabla "cache".
        set_env CACHE_STORE file
        set_env MAIL_PASSWORD "${MAIL_PASSWORD:-}"
    fi
    if [ -z "$(get_env MAIL_PASSWORD)" ]; then
        amarillo "MAIL_PASSWORD está vacío: el sistema no enviará correos hasta completarlo."
    fi
    # El host lo edita; www-data (grupo 33) solo lo lee.
    $SUDO chown "$(id -u):$WWW_UID" "$ENV_FILE"
    chmod 640 "$ENV_FILE"
}

base_tiene_datos() {
    [ "$(dc exec -T db psql -U laravel -d cineform -tAc \
        "select to_regclass('security.users') is not null and exists (select 1 from security.users)" 2>/dev/null \
        | tr -d '[:space:]')" = "t" ] 2>/dev/null
}

instalar() {
    [ -f docker-compose.yml ] && [ -f env.example ] || falla "Ejecuta el script desde la raíz del repositorio CINEFORM."

    preparar_sistema
    instalar_docker
    crear_override
    crear_env

    paso "Construyendo y levantando contenedores"
    permisos_storage
    dc build
    dc up -d --wait

    paso "Dependencias de PHP"
    dc exec -T app composer install --no-dev --optimize-autoloader --no-interaction

    paso "Aplicación"
    if [ -z "$(get_env APP_KEY)" ]; then
        artisan key:generate --force
    fi
    artisan migrate --force
    if base_tiene_datos; then
        verde "La base ya tiene usuarios: se omite db:seed."
    else
        artisan db:seed --force
        SEMBRADO=si
    fi
    [ -L app/public/storage ] || artisan storage:link
    cachear

    verificar || true

    paso "Listo"
    verde "CINEFORM instalado en $(get_env APP_URL)"
    echo "  Claves de PostgreSQL y pgAdmin: $RAIZ/$OVERRIDE"
    echo "  pgAdmin (por túnel): ssh -L 8081:127.0.0.1:8081 $USER@<servidor>  y abrir http://localhost:8081"
    if [ "${SEMBRADO:-}" = "si" ]; then
        amarillo "  Cambia YA las contraseñas de los usuarios sembrados (están en PersonasSeeder.php)."
    fi
    echo "  Respaldo: bash instalar.sh respaldar    Actualizar: bash instalar.sh actualizar"
    echo "  HTTPS con dominio: docs/instalacion-ssh.md, paso 10."
}

actualizar() {
    [ -f "$ENV_FILE" ] || falla "No hay $ENV_FILE: ejecuta primero 'bash instalar.sh'."
    paso "Descargando la versión nueva"
    git pull --ff-only
    respaldar
    paso "Contenedores"
    dc build
    dc up -d --wait
    paso "Dependencias y migraciones"
    dc exec -T app composer install --no-dev --optimize-autoloader --no-interaction
    artisan migrate --force
    cachear
    verificar
}

case "${1:-instalar}" in
    instalar) instalar ;;
    actualizar) actualizar ;;
    respaldar) respaldar ;;
    -h|--help|ayuda) sed -n '2,11p' "$0" | sed 's/^# \{0,1\}//' ;;
    *) falla "Opción desconocida '$1'. Usa: instalar | actualizar | respaldar" ;;
esac
