# filaflow

Sistema de Gerenciamento de Fila

## Docker

Subir a stack completa (MySQL, PHP, Nginx e **Soketi** para WebSockets):

```bash
docker compose up -d
```

O Soketi sobe automaticamente na porta `6001`. Não é necessário `php artisan reverb:start`.

### Variáveis de broadcast (Soketi / Pusher)

No `.env` com Docker, use **dois hosts**:

- `PUSHER_HOST=soketi` — só o PHP no container (já definido no `docker-compose.yml`)
- `VITE_PUSHER_HOST=localhost` — navegador na sua máquina (porta `6001` publicada pelo Soketi)

```env
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=filaflow
PUSHER_APP_KEY=filaflow-key
PUSHER_APP_SECRET=filaflow-secret
PUSHER_HOST=soketi
PUSHER_PORT=6001
PUSHER_SCHEME=http

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST=localhost
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME=http
```

Depois de alterar `VITE_*`, rode `npm run build` (ou `npm run dev`).

Com `composer dev` sem subir o app inteiro no Docker, inicie só o Soketi:

```bash
docker compose up -d soketi
```

Depois de alterar variáveis `VITE_*`, rode `npm run build` ou `npm run dev`.
