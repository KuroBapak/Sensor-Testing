#!/usr/bin/env bash
# Build the image on THIS machine, push it to GHCR, optionally tell Coolify to redeploy.
# Run from Git Bash / WSL / Linux / macOS. Windows PowerShell: use build-push.ps1.
#
# Usage (from the app root, next to the Dockerfile):
#   ./build-push.sh
#
# Settings are environment variables, so one script serves several images:
#   IMAGE      image name, lowercase            default: read from docker-compose.yaml
#   CONTEXT    build context folder             default: .
#   DOCKERFILE path to the Dockerfile           default: $CONTEXT/Dockerfile
#   PUSH=0     build only, do not push/deploy   default: 1
#   NO_CACHE=1 add --no-cache (only to debug stale layers; much slower)
#   COOLIFY_WEBHOOK + COOLIFY_TOKEN  if both are set, redeploy after the push
#
# Example, second image (AI server) from a subfolder:
#   CONTEXT=AIServer IMAGE=ghcr.io/you/your-app-ai ./build-push.sh
#
# One-time before the first push:
#   echo "$GH_TOKEN" | docker login ghcr.io -u <github-user> --password-stdin
#   (classic token with write:packages)

set -euo pipefail

IMAGE="${IMAGE:-}"
CONTEXT="${CONTEXT:-.}"
DOCKERFILE="${DOCKERFILE:-$CONTEXT/Dockerfile}"
BUILD_ENV_FILE="${BUILD_ENV_FILE:-$CONTEXT/.env.build}"
PUSH="${PUSH:-1}"
NO_CACHE="${NO_CACHE:-0}"

# --- image name: ONE place only, the first active `image:` line of docker-compose.yaml
# (init.php fills it in). IMAGE=... overrides it, e.g. for a second image.
if [ -z "$IMAGE" ]; then
    for compose in "$CONTEXT/docker-compose.yaml" "$CONTEXT/docker-compose.yml"; do
        if [ -f "$compose" ]; then
            IMAGE="$(sed -n 's/^[[:space:]]*image:[[:space:]]*//p' "$compose" | head -1 | tr -d '"'"'"'\r' \
                | sed -E 's/:\$\{.*$//; s#(/[^/:]+):[^/]+$#\1#')"
            break
        fi
    done
fi
case "$IMAGE" in
    ''|*your-github-user*)
        echo "Image name not set. Edit the image: line in $CONTEXT/docker-compose.yaml (or run init.php), or pass IMAGE=ghcr.io/<user>/<name>." >&2
        exit 1
        ;;
esac

# --- immutable tag: sha-<commit>, plus -dirty when files are uncommitted ----
if git -C "$CONTEXT" rev-parse --short HEAD >/dev/null 2>&1; then
    TAG="sha-$(git -C "$CONTEXT" rev-parse --short HEAD)"
    if [ -n "$(git -C "$CONTEXT" status --porcelain -- . 2>/dev/null)" ]; then
        TAG="$TAG-dirty"
    fi
else
    TAG="local-$(date +%Y%m%d%H%M%S)"
fi

# --- VITE_* build args (compiled into the JS bundle) ------------------------
BUILD_ARGS=()
if [ -f "$BUILD_ENV_FILE" ]; then
    while IFS= read -r line || [ -n "$line" ]; do
        line="${line%$'\r'}"
        case "$line" in
            ''|\#*) continue ;;
            VITE_*=*)
                value="${line#*=}"
                value="${value%\"}"
                value="${value#\"}"
                BUILD_ARGS+=(--build-arg "${line%%=*}=$value")
                ;;
            *) echo "Ignoring non-VITE line in $BUILD_ENV_FILE: ${line%%=*}" ;;
        esac
    done < "$BUILD_ENV_FILE"
fi

CACHE_FLAG=()
[ "$NO_CACHE" = "1" ] && CACHE_FLAG=(--no-cache)

echo "==> Building $IMAGE:$TAG (and :latest)"
docker build --platform linux/amd64 \
    ${CACHE_FLAG[@]+"${CACHE_FLAG[@]}"} \
    ${BUILD_ARGS[@]+"${BUILD_ARGS[@]}"} \
    -t "$IMAGE:$TAG" -t "$IMAGE:latest" \
    -f "$DOCKERFILE" "$CONTEXT"

if [ "$PUSH" != "1" ]; then
    echo "==> PUSH=0: built only. Nothing pushed."
    exit 0
fi

# sha tag first, so :latest never points at an image that was not pushed
echo "==> Pushing"
docker push "$IMAGE:$TAG"
docker push "$IMAGE:latest"

if [ -n "${COOLIFY_WEBHOOK:-}" ] && [ -n "${COOLIFY_TOKEN:-}" ]; then
    echo "==> Triggering Coolify deploy"
    curl --fail --silent --show-error --request POST "$COOLIFY_WEBHOOK" \
        --header "Authorization: Bearer $COOLIFY_TOKEN"
    echo
else
    echo "==> Pushed. Now click Deploy / Redeploy in Coolify."
fi

echo "==> Done: $IMAGE:$TAG"
echo "    Roll back later with APP_IMAGE_TAG=$TAG in the Coolify environment."
