# FilaFlow

Sistema de gerenciamento de fila para clínicas: Totem, Operador, Consultório/Médico, Painel TV e administração.

## Requisitos

- PHP 8.4+, Composer 2
- Node.js 22+ e npm
- MySQL 8.4 (ou SQLite para testes locais simples)
- Docker e Docker Compose (recomendado para produção e desenvolvimento)

## Instalação rápida (Docker)

### 1. Clonar e configurar ambiente

```bash
cp .env.example .env
```

Ajuste no `.env` (mínimo):

```env
APP_URL=http://localhost:8081
APP_PORT=8081

DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=filaflow
DB_USERNAME=filaflow
DB_PASSWORD=secret

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

> **Importante:** `PUSHER_HOST=soketi` é usado pelo PHP **dentro** do Docker. `VITE_PUSHER_HOST=localhost` é usado pelo **navegador** na sua máquina.

### 2. Subir containers

```bash
docker compose up -d --build
```

Serviços: MySQL, PHP-FPM (com Composer e Node), Nginx (`8081`) e Soketi (WebSockets, porta `6001`).

### 3. Instalar dependências e preparar app

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

No host (ou dentro do container):

```bash
npm install
npm run build
```

Dentro do container:

```bash
docker compose exec app npm install
docker compose exec app npm run build
```

### 4. Acessar

| Módulo | URL |
|--------|-----|
| Aplicação | http://localhost:8081 |
| Totem | http://localhost:8081/totem |
| Painel TV | http://localhost:8081/painel |
| Operador | http://localhost:8081/operador/login |
| Médico | http://localhost:8081/medico/login |
| Admin | http://localhost:8081/dashboard (login Laravel) |

Com seed demo (`FILA_SEED_DEMO=true`):

- **Operador:** CPF `90696573253` / senha `123`
- **Médicos demo:** CPFs do seeder (ex.: `11144477735`) / senha `senha123`

### 5. Verificar tempo real

1. Abra o Painel TV e selecione a ala.
2. No Operador, chame uma senha.
3. O painel deve atualizar **sem recarregar** (animação + voz, se habilitada).

Se não atualizar: confira se o Soketi está no ar (`docker compose ps`) e se rodou `npm run build` após alterar `VITE_*`.

## Instalação local (sem Docker completo)

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed

# WebSockets — subir só o Soketi
docker compose up -d soketi

npm install
npm run build
php artisan serve
```

Use `DB_CONNECTION=sqlite` ou MySQL local; mantenha `VITE_PUSHER_HOST=localhost`.

## Painel TV — voz e kiosk

- Na primeira abertura, clique em **Iniciar painel** para liberar a síntese de voz (Web Speech API).
- Consultório **com paciente:** anuncia nome + consultório + senha.
- Consultório **sem paciente** ou **guichê:** anuncia senha + destino.
- Cada TV pode filtrar por **Ala**; só recebe chamadas da ala selecionada.

TV dedicada (Chromium/Chrome em kiosk):

```bash
./scripts/painel-kiosk.sh http://localhost:8081/painel
```

O script usa `--autoplay-policy=no-user-gesture-required` e `?auto_speech=1`.

## Variáveis úteis

| Variável | Descrição |
|----------|-----------|
| `FILA_SEED_DEMO` | `false` em produção (sem dados fictícios) |
| `FILA_CLINICA_*` | Nome, horários e ticker da clínica no seed |
| `FILA_OPERADOR_CPF` / `FILA_OPERADOR_SENHA` | Operador criado no seed |
| `SOKETI_PORT` | Porta publicada do WebSocket (padrão `6001`) |

## Comandos úteis

```bash
php artisan fila:integrar-agendamentos   # integra agendamentos na fila
php artisan schedule:work                # scheduler (agendamentos a cada minuto)
npm run dev                              # Vite em desenvolvimento
npm run build                            # assets de produção (sempre após mudar JS/CSS ou VITE_*)
```

## Documentação completa

Após login no admin: **Documentação** no menu lateral (`/documentacao`) — fluxos, cadastros, regras de negócio e checklist de implantação.
