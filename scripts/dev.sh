#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKEND_DIR="$ROOT_DIR/backend"
WEB_DIR="$ROOT_DIR/web"
BACKEND_ENV="$BACKEND_DIR/.env"
WEB_ENV="$WEB_DIR/.env.local"
SQLITE_PATH_HOST="$BACKEND_DIR/database/database.sqlite"
SQLITE_PATH_CONTAINER="/var/www/backend/database/database.sqlite"
DEV_APP_KEY="base64:QZzR5jLJ2i4fA4LRQNzxg8nuEqylfQx2omW1vNulV8A="

set_env_value() {
  local file="$1"
  local key="$2"
  local value="$3"

  if grep -qE "^${key}=" "$file"; then
    perl -0pi -e "s#^${key}=.*#${key}=${value}#m" "$file"
  else
    printf "%s=%s\n" "$key" "$value" >> "$file"
  fi
}

ensure_backend_env() {
  if [[ ! -f "$BACKEND_ENV" ]]; then
    cp "$BACKEND_DIR/.env.example" "$BACKEND_ENV"
  fi

  mkdir -p "$BACKEND_DIR/database"
  touch "$SQLITE_PATH_HOST"

  set_env_value "$BACKEND_ENV" "APP_NAME" "CloudVault"
  set_env_value "$BACKEND_ENV" "APP_ENV" "local"
  set_env_value "$BACKEND_ENV" "APP_DEBUG" "true"
  set_env_value "$BACKEND_ENV" "APP_URL" "http://localhost:8080"
  set_env_value "$BACKEND_ENV" "FRONTEND_URL" "http://localhost:8080"
  set_env_value "$BACKEND_ENV" "APP_LOCALE" "en"
  set_env_value "$BACKEND_ENV" "APP_FALLBACK_LOCALE" "en"
  set_env_value "$BACKEND_ENV" "APP_KEY" "$DEV_APP_KEY"
  set_env_value "$BACKEND_ENV" "DB_CONNECTION" "sqlite"
  set_env_value "$BACKEND_ENV" "DB_DATABASE" "$SQLITE_PATH_CONTAINER"
  set_env_value "$BACKEND_ENV" "CACHE_STORE" "file"
  set_env_value "$BACKEND_ENV" "SESSION_DRIVER" "file"
  set_env_value "$BACKEND_ENV" "QUEUE_CONNECTION" "sync"
  set_env_value "$BACKEND_ENV" "FILESYSTEM_DISK" "local"
  set_env_value "$BACKEND_ENV" "MAIL_MAILER" "log"
}

ensure_web_env() {
  cat > "$WEB_ENV" <<'EOF'
VITE_API_BASE_URL=http://localhost:8080/api/v1
EOF
}

docker_compose() {
  if docker compose version >/dev/null 2>&1; then
    docker compose "$@"
    return
  fi

  if command -v docker-compose >/dev/null 2>&1; then
    docker-compose "$@"
    return
  fi

  echo "Docker Compose is required to run CloudVault locally."
  exit 1
}

ensure_backend_env
ensure_web_env

echo "CloudVault app:     http://localhost:8080"
echo "CloudVault web:     http://localhost:5173"
echo "CloudVault backend: http://localhost:8000"

docker_compose up --build
