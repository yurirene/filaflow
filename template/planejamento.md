# Planejamento — Sistema de Senhas para Clínica

## Módulos do Frontend (HTML/CSS/VanillaJS)

### 1. Totem de Emissão de Senha
- Seleção de serviço (Triagem, Coleta, Raio-X, Caixa, etc.)
- Detecção de prioridade (idoso, PCD, gestante)
- Exibição de estimativa de espera
- Impressão / exibição da senha gerada

### 2. Painel de TV (Display)
- Exibição da senha chamada + guichê
- Histórico das últimas chamadas
- Alerta sonoro ao chamar nova senha
- Suporte a múltiplos painéis por ala

### 3. Painel do Operador (Atendente)
- Chamar próxima senha
- Rechamar senha atual
- Transferir senha para outro serviço/guichê
- Visualizar fila em tempo real
- Indicador de produtividade

### 4. Painel Administrativo / Relatórios
- Configuração de serviços e guichês
- Regras de intercalação preferencial/normal
- Relatórios: tempo médio, pico, produtividade por operador
- Configuração de notificações (WhatsApp/SMS)

### 5. Agendamento Integrado
- Paciente com consulta entra na fila automaticamente
- Prioridade por horário de agendamento

## Módulos do Backend (Documentação para Cursor.ai)

### Entidades Principais
- Empresa (multi-tenant)
- Servico
- Guiche
- Operador
- Senha
- Agendamento
- Chamada
- ConfiguracaoIntercalacao
- RelatorioAtendimento

### Tecnologias Sugeridas
- Node.js + TypeScript (ou Python FastAPI)
- PostgreSQL
- Redis (filas em tempo real)
- WebSocket (atualização em tempo real)
- Bull/BullMQ (processamento de filas)
- Twilio / Z-API (WhatsApp/SMS)

## Estrutura de Telas (SPA com abas/views)
1. /totem — Emissão de senha
2. /painel — Display TV
3. /operador — Painel do atendente
4. /admin — Configurações e relatórios
