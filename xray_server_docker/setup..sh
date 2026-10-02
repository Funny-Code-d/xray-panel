#!/bin/bash
#
# Настройка Xray-сервера для FunnyNodes.
# Запуск: sudo ./setup.sh
#
# Все параметры берутся из .env в этой же папке.
#
set -euo pipefail

# ==================== Пути ====================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="$SCRIPT_DIR/.env"
ENV_EXAMPLE="$SCRIPT_DIR/.env.example"

# ==================== Цвета ====================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log()  { echo -e "${GREEN}[+]${NC} $*"; }
warn() { echo -e "${YELLOW}[!]${NC} $*"; }
err()  { echo -e "${RED}[x]${NC} $*" >&2; exit 1; }

# ==================== Проверка root ====================

if [[ $EUID -ne 0 ]]; then
    err "Запусти скрипт от root: sudo $0"
fi

# ==================== Проверка .env ====================

if [[ ! -f "$ENV_FILE" ]]; then
    err ".env не найден в $SCRIPT_DIR

Создай его из шаблона:
  cp .env.example .env
  nano .env

И заполни переменные: PANEL_URL, XRAY_API_TOKEN, DOMAIN, LE_EMAIL, PANEL_IP"
fi

# Загружаем переменные
set -a
source "$ENV_FILE"
set +a

# Проверяем, что обязательные переменные заполнены
MISSING=()
[[ -z "${PANEL_URL:-}"     ]] && MISSING+=("PANEL_URL")
[[ -z "${XRAY_API_TOKEN:-}" ]] && MISSING+=("XRAY_API_TOKEN")
[[ -z "${DOMAIN:-}"        ]] && MISSING+=("DOMAIN")
[[ -z "${PANEL_IP:-}"      ]] && MISSING+=("PANEL_IP")

if [[ ${#MISSING[@]} -gt 0 ]]; then
    err "В .env не заполнены переменные: ${MISSING[*]}"
fi

LE_EMAIL="${LE_EMAIL:-}"

# ==================== Конфиг ====================

DOCKER_USER="${SUDO_USER:-$(whoami)}"

# ==================== Информация ====================

log "Настройка Xray-сервера"
log "Домен:       $DOMAIN"
log "Панель:      $PANEL_URL"
log "IP панели:   $PANEL_IP"
log "LE email:    ${LE_EMAIL:-(не указан)}"
echo

# ==================== 1. Установка пакетов ====================

log "Шаг 1: Установка пакетов (docker, certbot, ufw)"

apt update -qq
apt install -y -qq \
    ca-certificates \
    curl \
    gnupg \
    ufw \
    certbot \
    openssl

# Docker
if ! command -v docker &>/dev/null; then
    log "Устанавливаем Docker..."
    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL "https://download.docker.com/linux/$(. /etc/os-release && echo "$ID")/gpg" \
        | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    chmod a+r /etc/apt/keyrings/docker.gpg

    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
        https://download.docker.com/linux/$(. /etc/os-release && echo "$ID") \
        $(. /etc/os-release && echo "$VERSION_CODENAME") stable" \
        > /etc/apt/sources.list.d/docker.list

    apt update -qq
    apt install -y -qq docker-ce docker-ce-cli containerd.io docker-compose-plugin
    systemctl enable --now docker

    if [[ "$DOCKER_USER" != "root" ]]; then
        usermod -aG docker "$DOCKER_USER"
        log "Пользователь $DOCKER_USER добавлен в группу docker"
    fi
else
    log "Docker уже установлен"
fi

# ==================== 2. UFW ====================

log "Шаг 2: Настройка UFW"

ufw --force reset >/dev/null
ufw default deny incoming >/dev/null
ufw default allow outgoing >/dev/null

# Публичные порты
ufw allow 22/tcp   comment 'SSH'             >/dev/null
ufw allow 80/tcp   comment 'HTTP'            >/dev/null
ufw allow 443/tcp  comment 'VLESS Reality'   >/dev/null
ufw allow 8443/tcp comment 'VMess WS TLS'    >/dev/null
ufw allow 2053/tcp comment 'Trojan'          >/dev/null

# Только с IP панели
ufw allow from "$PANEL_IP" to any port 8080  proto tcp comment 'Agent'     >/dev/null
ufw allow from "$PANEL_IP" to any port 10085 proto tcp comment 'Xray API'  >/dev/null

ufw --force enable >/dev/null
log "UFW настроен"

# ==================== 3. Папки ====================

log "Шаг 3: Создание папок"

mkdir -p /etc/xray
mkdir -p /var/www/certbot
chmod 755 /etc/xray
chmod 755 /var/www/certbot

log "Папки созданы"

# ==================== 4. Сертификат ====================

log "Шаг 4: Выпуск сертификата для $DOMAIN"

if [[ -d "/etc/letsencrypt/live/$DOMAIN" ]]; then
    warn "Сертификат для $DOMAIN уже существует, пропускаем выпуск"
else
    CERTBOT_ARGS=("certonly" "--standalone" "-d" "$DOMAIN" "--non-interactive" "--agree-tos")

    if [[ -n "$LE_EMAIL" ]]; then
        CERTBOT_ARGS+=("--email" "$LE_EMAIL")
    else
        CERTBOT_ARGS+=("--register-unsafely-without-email")
    fi

    # Останавливаем всё, что может занимать 80 порт
    systemctl stop nginx 2>/dev/null || true
    docker stop xray-nginx 2>/dev/null || true

    certbot "${CERTBOT_ARGS[@]}"
fi

# ==================== 5. Симлинки в /etc/xray ====================

log "Шаг 5: Копирование сертификатов в /etc/xray"

cp "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" /etc/xray/cert.pem
cp "/etc/letsencrypt/live/$DOMAIN/privkey.pem"   /etc/xray/key.pem
chmod 644 /etc/xray/cert.pem
chmod 600 /etc/xray/key.pem

log "Сертификаты на месте"

# ==================== 6. Deploy-хук ====================

log "Шаг 6: Настройка автообновления сертификата"

HOOK_PATH="/etc/letsencrypt/renewal-hooks/deploy/reload-xray.sh"

cat > "$HOOK_PATH" <<EOF
#!/bin/bash
# Certbot вызывает этот скрипт после каждого успешного обновления сертификата.

DOMAIN="$DOMAIN"
XRAY_CERT="/etc/xray/cert.pem"
XRAY_KEY="/etc/xray/key.pem"
ENV_FILE="$SCRIPT_DIR/.env"

cp "/etc/letsencrypt/live/\${DOMAIN}/fullchain.pem" "\$XRAY_CERT"
cp "/etc/letsencrypt/live/\${DOMAIN}/privkey.pem"   "\$XRAY_KEY"
chmod 644 "\$XRAY_CERT"
chmod 600 "\$XRAY_KEY"

if [[ -f "\$ENV_FILE" ]]; then
    API_TOKEN=\$(grep '^XRAY_API_TOKEN=' "\$ENV_FILE" | cut -d= -f2- | tr -d '"' | tr -d "'")
    if [[ -n "\$API_TOKEN" ]]; then
        curl -sf -X POST http://127.0.0.1:8080/restart-xray \\
            -H "Authorization: Bearer \${API_TOKEN}" \\
            || echo "Agent reload failed"
    fi
fi

echo "\$(date): cert for \${DOMAIN} deployed" >> /var/log/xray-cert-reload.log
EOF

chmod +x "$HOOK_PATH"
log "Deploy-хук установлен: $HOOK_PATH"

# ==================== 7. Certbot → webroot ====================

log "Шаг 7: Переключение Certbot на webroot"

RENEWAL_CONF="/etc/letsencrypt/renewal/$DOMAIN.conf"

if [[ -f "$RENEWAL_CONF" ]]; then
    sed -i '/^authenticator = /d'       "$RENEWAL_CONF"
    sed -i '/^webroot_path = /d'        "$RENEWAL_CONF"
    sed -i '/^\[\[webroot_map\]\]/,$d'  "$RENEWAL_CONF"

    cat >> "$RENEWAL_CONF" <<EOF

authenticator = webroot
webroot_path = /var/www/certbot,
[[webroot_map]]
$DOMAIN = /var/www/certbot
EOF
    log "Certbot переключён на webroot"
fi

# ==================== 8. Запуск ====================

log "Шаг 8: Запуск Docker Compose"

cd "$SCRIPT_DIR"

docker compose down 2>/dev/null || true
docker compose up -d --build

sleep 3

docker compose ps

# ==================== Готово ====================

echo
log "===================================="
log "Готово!"
log "===================================="
echo
echo "  Домен:        $DOMAIN"
echo "  Сертификат:   /etc/xray/cert.pem"
echo "  Ключ:         /etc/xray/key.pem"
echo "  Хук обновл.:  $HOOK_PATH"
echo "  Логи Xray:    docker logs -f xray"
echo "  Логи Nginx:   docker logs -f xray-nginx"
echo
echo "  Проверь снаружи:"
echo "    curl -I http://$DOMAIN"
echo
echo "  Если что-то не работает:"
echo "    docker logs xray"
echo "    docker logs xray-nginx"
echo