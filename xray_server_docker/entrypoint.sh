#!/bin/bash
set -euo pipefail

PANEL_URL="${PANEL_URL}"
API_TOKEN="${API_TOKEN:?API_TOKEN is required}"
CONFIG_PATH="/etc/xray/config.json"
TEMP_PATH="/tmp/xray-config.json"
XRAY_PID=""

fetch_config() {
    echo "[$(date)] Запрашиваем конфиг из Laravel ($PANEL_URL)..."

    for i in $(seq 1 30); do
        if curl -sf "$PANEL_URL/api/xray/config" \
            -H "Authorization: Bearer $API_TOKEN" \
            -o "$TEMP_PATH"; then
            echo "[$(date)] Конфиг получен"
            break
        fi
        echo "[$(date)] Laravel недоступен, ждём 5 сек... ($i/30)"
        sleep 5
    done

    if [ ! -s "$TEMP_PATH" ]; then
        echo "[$(date)] ОШИБКА: не удалось получить конфиг"
        exit 1
    fi

    if ! xray run -test -config "$TEMP_PATH" > /dev/null 2>&1; then
        echo "[$(date)] ОШИБКА: конфиг невалиден"
        cat "$TEMP_PATH"
        exit 1
    fi

    mkdir -p "$(dirname "$CONFIG_PATH")"
    cp "$TEMP_PATH" "$CONFIG_PATH"
}

start_xray() {
    echo "[$(date)] Запускаем Xray..."
    xray run -config "$CONFIG_PATH" &
    XRAY_PID=$!
    echo "[$(date)] Xray запущен, PID=$XRAY_PID"
}

stop_xray() {
    if [ -n "$XRAY_PID" ] && kill -0 "$XRAY_PID" 2>/dev/null; then
        echo "[$(date)] Останавливаем Xray (PID=$XRAY_PID)..."
        kill "$XRAY_PID" 2>/dev/null || true
        wait "$XRAY_PID" 2>/dev/null || true
    fi
}

reload() {
    echo "[$(date)] Получен SIGHUP, перезагружаем конфиг..."
    stop_xray
    fetch_config
    start_xray
    echo "[$(date)] Перезагрузка завершена"
}

trap 'reload' HUP

fetch_config
start_xray

# Запускаем агент в фоне
python3 /agent.py &
AGENT_PID=$!
echo "[$(date)] Агент запущен, PID=$AGENT_PID"

wait "$XRAY_PID"