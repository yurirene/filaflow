#!/usr/bin/env bash
# Painel TV em modo kiosk com autoplay liberado (Chromium/Chrome).
# Uso: ./scripts/painel-kiosk.sh [URL]
# Exemplo: ./scripts/painel-kiosk.sh http://localhost:8081/painel

set -euo pipefail

BASE_URL="${1:-http://localhost:8081/painel}"
# auto_speech=1 pula a tela de desbloqueio quando o navegador permite TTS sem gesto
URL="${BASE_URL}$(echo "${BASE_URL}" | grep -q '?' && echo '&' || echo '?')auto_speech=1"

BROWSER=""

if command -v chromium >/dev/null 2>&1; then
    BROWSER="chromium"
elif command -v chromium-browser >/dev/null 2>&1; then
    BROWSER="chromium-browser"
elif command -v google-chrome >/dev/null 2>&1; then
    BROWSER="google-chrome"
elif command -v google-chrome-stable >/dev/null 2>&1; then
    BROWSER="google-chrome-stable"
fi

if [[ -z "${BROWSER}" ]]; then
    echo "Erro: Chromium ou Google Chrome não encontrado no PATH." >&2
    exit 1
fi

exec "${BROWSER}" \
    --kiosk \
    --no-first-run \
    --disable-infobars \
    --autoplay-policy=no-user-gesture-required \
    "${URL}"
