#!/bin/sh
# Respaldo del código de CINEFORM como tag local de git.
#   respaldo.sh crear <nombre>   foto del árbol de trabajo (commits + cambios sin commitear + archivos nuevos)
#   respaldo.sh listar           respaldos existentes
#   respaldo.sh diferencias <tag> qué cambió desde el respaldo
#   respaldo.sh sobrantes <tag>  archivos sin seguimiento que no existían en el respaldo
# No toca el árbol de trabajo, el índice ni la rama actual. Respeta .gitignore,
# así que .env y vendor/ no entran en la foto.
set -eu

raiz=$(git rev-parse --show-toplevel)
cd "$raiz"

case "${1:-}" in
  crear)
    nombre=${2:?Falta el nombre, por ejemplo: antes-d4}
    tag="respaldo/$(date +%Y-%m-%d)-$nombre"
    if git rev-parse -q --verify "refs/tags/$tag" >/dev/null; then
      echo "Ya existe $tag. Usa otro nombre." >&2
      exit 1
    fi
    # Índice temporal: el índice real del usuario queda intacto.
    indice=$(mktemp)
    trap 'rm -f "$indice"' EXIT
    cp "$(git rev-parse --git-path index)" "$indice"
    GIT_INDEX_FILE="$indice" git -c core.safecrlf=false add -A
    arbol=$(GIT_INDEX_FILE="$indice" git write-tree)
    foto=$(git commit-tree "$arbol" -p HEAD -m "Respaldo $tag (árbol de trabajo sobre $(git rev-parse --short HEAD))")
    git tag -a "$tag" "$foto" -m "Respaldo antes de: $nombre"
    echo "TAG=$tag"
    echo "BASE=$(git rev-parse --short HEAD)"
    echo "ARCHIVOS=$(git ls-tree -r --name-only "$foto" | wc -l | tr -d ' ')"
    echo "SIN_COMMITEAR=$(git diff --name-only HEAD "$foto" | wc -l | tr -d ' ')"
    ;;
  listar)
    git tag -l 'respaldo/*' --sort=-creatordate --format='%(refname:short)  %(creatordate:short)  %(contents:subject)'
    ;;
  diferencias)
    tag=${2:?Falta el tag}
    # Compara la foto contra el árbol de trabajo actual, incluidos archivos nuevos.
    indice=$(mktemp)
    trap 'rm -f "$indice"' EXIT
    cp "$(git rev-parse --git-path index)" "$indice"
    GIT_INDEX_FILE="$indice" git -c core.safecrlf=false add -A
    GIT_INDEX_FILE="$indice" git diff --cached --stat "$tag"
    ;;
  sobrantes)
    tag=${2:?Falta el tag}
    # Solo lo que apareció después del respaldo: borrar esto es seguro tras restaurar.
    en_tag=$(mktemp)
    trap 'rm -f "$en_tag"' EXIT
    git ls-tree -r --name-only "$tag" | sort >"$en_tag"
    git ls-files --others --exclude-standard | sort | comm -23 - "$en_tag"
    ;;
  *)
    echo "Uso: respaldo.sh crear <nombre> | listar | diferencias <tag> | sobrantes <tag>" >&2
    exit 2
    ;;
esac
