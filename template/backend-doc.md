# Documentação Técnica do Backend — Sistema de Senhas para Clínica (Multi-Tenant)

Esta documentação foi projetada especificamente para que o **Cursor.ai** (ou qualquer outro assistente de IA) possa gerar o código-fonte completo do backend de forma rápida, robusta e padronizada. O sistema utiliza os princípios de **Clean Architecture** simplificada, garantindo separação de conceitos, testabilidade e facilidade de manutenção.

---

## 1. Visão Geral da Arquitetura

A arquitetura do sistema é dividida em quatro camadas principais, seguindo o padrão de **Clean Architecture**:

```
┌────────────────────────────────================─────────┐
│                       Frameworks & Drivers              │
│  (Express/FastAPI, PostgreSQL, Redis, WebSockets, SMS)  │
│    ┌───────────────────────────────────────────────┐    │
│    │              Interface Adapters               │    │
│    │     (Controllers, Presenters, Repositories)   │    │
│    │    ┌─────────────────────────────────────┐    │    │
│    │    │             Use Cases               │    │    │
│    │    │        (Regras de Negócio)          │    │    │
│    │    │    ┌───────────────────────────┐    │    │    │
│    │    │    │          Entities         │    │    │    │
│    │    │    │     (Modelos de Domínio)  │    │    │    │
│    │    │    └───────────────────────────┘    │    │    │
│    │    └─────────────────────────────────────┘    │    │
│    └───────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────┘
```

### Divisão de Pastas Recomendada

```
src/
├── domain/
│   ├── entities/          # Modelos ricos de domínio
│   └── repositories/      # Interfaces de repositório (Portas)
├── use-cases/
│   ├── dtos/              # Data Transfer Objects (Entrada/Saída)
│   ├── errors/            # Exceções de negócio
│   └── [use-case-name]/   # Implementação dos casos de uso
├── infrastructure/
│   ├── database/          # Modelos ORM (Prisma/TypeORM/SQLAlchemy) e Migrations
│   ├── repositories/      # Implementação concreta dos repositórios (Adaptadores)
│   ├── web/               # Servidor HTTP, Rotas, Controllers e WebSockets
│   └── services/          # Integrações externas (WhatsApp, SMS, Notificações)
└── main.ts                # Ponto de entrada e composição (Dependency Injection)
```

---

## 2. Estrutura do Banco de Dados (PostgreSQL)

Todas as tabelas possuem o campo `empresa_id` para garantir o isolamento **Multi-Tenant**. Índices compostos utilizando `empresa_id` são obrigatórios para otimização de consultas.

| Tabela | Descrição | Chaves Estrangeiras | Índices Recomendados |
| :--- | :--- | :--- | :--- |
| **empresas** | Cadastro das clínicas clientes do sistema. | Nenhuma | `id` |
| **servicos** | Serviços oferecidos (Triagem, Coleta, etc.). | `empresa_id` | `(empresa_id, id)`, `(empresa_id, ativo)` |
| **guiches** | Guichês físicos de atendimento. | `empresa_id` | `(empresa_id, numero)` |
| **operadores** | Usuários/atendentes do sistema. | `empresa_id` | `(empresa_id, email)` |
| **senhas** | Registro de todas as senhas emitidas. | `empresa_id`, `servico_id` | `(empresa_id, status)`, `(empresa_id, criada_em)` |
| **chamadas** | Histórico de chamadas de senhas nos guichês. | `empresa_id`, `senha_id`, `guiche_id`, `operador_id` | `(empresa_id, chamada_em)` |
| **agendamentos** | Consultas integradas que entram na fila. | `empresa_id`, `servico_id` | `(empresa_id, data_hora)` |
| **regras_intercalacao** | Configuração de proporção de atendimento. | `empresa_id`, `servico_id` | `(empresa_id, servico_id)` |

### DDL SQL (PostgreSQL)

```sql
-- Habilitar extensão para UUIDs se necessário
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 1. Empresas (Tenants)
CREATE TABLE empresas (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nome VARCHAR(150) NOT NULL,
    cnpj VARCHAR(18) UNIQUE,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 2. Serviços
CREATE TABLE servicos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    empresa_id UUID NOT NULL REFERENCES empresas(id) ON DELETE CASCADE,
    nome VARCHAR(100) NOT NULL,
    prefixo VARCHAR(2) NOT NULL,
    ala VARCHAR(50),
    tempo_medio_minutos INTEGER DEFAULT 10,
    cor VARCHAR(7) DEFAULT '#2563eb',
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_prefixo_empresa UNIQUE (empresa_id, prefixo)
);
CREATE INDEX idx_servicos_tenant ON servicos(empresa_id, ativo);

-- 3. Guichês
CREATE TABLE guiches (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    empresa_id UUID NOT NULL REFERENCES empresas(id) ON DELETE CASCADE,
    numero INTEGER NOT NULL,
    descricao VARCHAR(100),
    servico_padrao_id UUID REFERENCES servicos(id) ON DELETE SET NULL,
    ativo BOOLEAN DEFAULT TRUE,
    CONSTRAINT unique_guiche_empresa UNIQUE (empresa_id, numero)
);
CREATE INDEX idx_guiches_tenant ON guiches(empresa_id);

-- 4. Operadores
CREATE TABLE operadores (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    empresa_id UUID NOT NULL REFERENCES empresas(id) ON DELETE CASCADE,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_email_empresa UNIQUE (empresa_id, email)
);
CREATE INDEX idx_operadores_tenant ON operadores(empresa_id);

-- 5. Senhas
CREATE TYPE prioridade_tipo AS ENUM ('normal', 'idoso', 'pcd', 'gestante');
CREATE TYPE senha_status AS ENUM ('aguardando', 'chamado', 'atendimento', 'finalizado', 'ausente');

CREATE TABLE senhas (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    empresa_id UUID NOT NULL REFERENCES empresas(id) ON DELETE CASCADE,
    codigo VARCHAR(10) NOT NULL,
    servico_id UUID NOT NULL REFERENCES servicos(id) ON DELETE RESTRICT,
    prioridade prioridade_tipo DEFAULT 'normal',
    is_preferencial BOOLEAN DEFAULT FALSE,
    is_agendado BOOLEAN DEFAULT FALSE,
    status senha_status DEFAULT 'aguardando',
    emitida_em TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    chamada_em TIMESTAMP WITH TIME ZONE,
    finalizada_em TIMESTAMP WITH TIME ZONE
);
CREATE INDEX idx_senhas_fila ON senhas(empresa_id, servico_id, status, is_preferencial, emitida_em);

-- 6. Chamadas
CREATE TABLE chamadas (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    empresa_id UUID NOT NULL REFERENCES empresas(id) ON DELETE CASCADE,
    senha_id UUID NOT NULL REFERENCES senhas(id) ON DELETE CASCADE,
    guiche_id UUID NOT NULL REFERENCES guiches(id) ON DELETE RESTRICT,
    operador_id UUID NOT NULL REFERENCES operadores(id) ON DELETE RESTRICT,
    chamada_em TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    rechamada_vezes INTEGER DEFAULT 0
);
CREATE INDEX idx_chamadas_recentes ON chamadas(empresa_id, chamada_em DESC);

-- 7. Agendamentos
CREATE TABLE agendamentos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    empresa_id UUID NOT NULL REFERENCES empresas(id) ON DELETE CASCADE,
    paciente_nome VARCHAR(150) NOT NULL,
    paciente_celular VARCHAR(20),
    servico_id UUID NOT NULL REFERENCES servicos(id) ON DELETE RESTRICT,
    data_hora TIMESTAMP WITH TIME ZONE NOT NULL,
    status VARCHAR(30) DEFAULT 'agendado' -- agendado, na_fila, atendido, cancelado
);
CREATE INDEX idx_agendamentos_busca ON agendamentos(empresa_id, data_hora);

-- 8. Regras de Intercalação
CREATE TABLE regras_intercalacao (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    empresa_id UUID NOT NULL REFERENCES empresas(id) ON DELETE CASCADE,
    servico_id UUID NOT NULL REFERENCES servicos(id) ON DELETE CASCADE,
    normais_por_ciclo INTEGER DEFAULT 2,
    preferenciais_por_ciclo INTEGER DEFAULT 1,
    ciclo_atual INTEGER DEFAULT 0,
    CONSTRAINT unique_regra_servico UNIQUE (empresa_id, servico_id)
);
```

---

## 3. Data Transfer Objects (DTOs)

Os DTOs garantem a validação estrita dos dados na entrada e na saída da aplicação.

### DTOs de Emissão de Senha (Totem)

```typescript
// Entrada: Emissão de Senha Manual
interface EmitirSenhaInputDTO {
  empresa_id: string;
  servico_id: string;
  prioridade: 'normal' | 'idoso' | 'pcd' | 'gestante';
  paciente_celular?: string; // Opcional para WhatsApp/SMS
}

// Saída: Senha Emitida
interface EmitirSenhaOutputDTO {
  id: string;
  codigo: string;
  servico_nome: string;
  prioridade: string;
  espera_estimada_minutos: number;
  posicao_fila: number;
  emitida_em: string;
}
```

### DTOs de Atendimento (Operador)

```typescript
// Entrada: Chamar Próxima Senha
interface ChamarProximaInputDTO {
  empresa_id: string;
  guiche_id: string;
  operador_id: string;
  servico_id: string;
}

// Saída: Senha Chamada
interface ChamadaSenhaOutputDTO {
  senha_id: string;
  codigo: string;
  servico_nome: string;
  prioridade: string;
  is_preferencial: boolean;
  guiche_numero: number;
  chamada_em: string;
}

// Entrada: Transferir Senha
interface TransferirSenhaInputDTO {
  empresa_id: string;
  senha_id: string;
  servico_destino_id: string;
  motivo?: string;
}
```

---

## 4. Casos de Uso (Use Cases)

Esta seção descreve a lógica de negócio central que o Cursor.ai deve implementar.

### Use Case 1: Emissão de Senha com Estimativa de Espera
1. **Validação**: Verifica se a `empresa_id` e o `servico_id` existem e estão ativos.
2. **Geração de Código**:
   - Busca o prefixo do serviço.
   - Incrementa o contador diário do serviço para a empresa (pode ser feito via Redis `INCR` ou tabela de controle).
   - Formata o código (ex: `T042`).
3. **Cálculo de Posição**: Conta quantas senhas com status `aguardando` existem na fila daquele serviço.
4. **Cálculo de Espera Estimada**:
   - `espera = posicao_fila * tempo_medio_minutos_do_servico`.
5. **Persistência**: Salva a entidade `Senha` no banco de dados.
6. **Notificação**: Se o celular foi informado, dispara evento de agendamento de notificação.

### Use Case 2: Chamada de Próxima Senha (Intercalação)
1. **Busca Regra de Intercalação**: Recupera a regra cadastrada para o serviço da empresa.
2. **Algoritmo de Seleção**:
   - Obtém a fila de `aguardando` do serviço.
   - Se não houver regra ou não houver preferenciais, chama a primeira da fila por ordem de chegada (`emitida_em`).
   - Se houver preferenciais e normais:
     - Calcula a posição no ciclo: `posicao_ciclo = ciclo_atual % (normais_por_ciclo + preferenciais_por_ciclo)`.
     - Se `posicao_ciclo < normais_por_ciclo`: seleciona a primeira senha **Normal**. Se não houver normal, seleciona a primeira **Preferencial**.
     - Se `posicao_ciclo >= normais_por_ciclo`: seleciona a primeira senha **Preferencial**. Se não houver preferencial, seleciona a primeira **Normal**.
3. **Atualização de Estado**:
   - Altera o status da senha selecionada para `chamado`.
   - Incrementa o `ciclo_atual` na regra de intercalação.
   - Registra a chamada na tabela `chamadas`.
4. **Notificação em Tempo Real**: Dispara evento via **WebSocket** para atualizar o Painel de TV e o Painel do Operador.

### Use Case 3: Integração de Agendamento
1. **Verificação**: Periodicamente (ou via trigger), o sistema busca agendamentos do dia cuja hora de atendimento esteja próxima (ex: 15 minutos de antecedência).
2. **Entrada Automática**:
   - Cria uma senha na fila do serviço correspondente.
   - Define `is_agendado = TRUE`.
   - Insere a senha na fila logo após as preferenciais ativas, garantindo prioridade de horário sobre as senhas normais emitidas na hora.

---

## 5. Especificações de Comunicação em Tempo Real (WebSockets)

Para garantir que o Painel de TV e os operadores recebam atualizações instantâneas sem necessidade de polling, o backend deve implementar uma camada de WebSockets (Socket.io ou FastAPI WebSockets).

### Canais (Rooms)
- Cada empresa possui sua própria sala para isolamento multi-tenant: `empresa:{empresa_id}`.
- Painéis de TV podem se inscrever em sub-salas por ala: `empresa:{empresa_id}:ala:{ala_nome}`.

### Eventos Emitidos pelo Servidor

#### `senha_chamada`
Enviado para o Painel de TV e operadores quando uma senha é chamada.
```json
{
  "event": "senha_chamada",
  "data": {
    "codigo": "T042",
    "servico": "Triagem",
    "guiche": 3,
    "is_preferencial": true,
    "timestamp": "2026-05-20T15:30:00Z"
  }
}
```

#### `fila_atualizada`
Enviado sempre que uma senha é emitida, chamada, transferida ou finalizada.
```json
{
  "event": "fila_atualizada",
  "data": {
    "servico_id": "uuid-servico",
    "tamanho_fila": 8,
    "espera_estimada": 64
  }
}
```

---

## 6. Prompt de Instrução para o Cursor.ai

Copie e cole o prompt abaixo no Cursor.ai para gerar o código do backend:

```text
Aja como um desenvolvedor backend sênior especialista em Clean Architecture, TypeScript e Node.js (ou Python/FastAPI se preferir).
Gere o código-fonte completo do backend para o Sistema de Senhas de Clínica Multi-Tenant com base na documentação técnica fornecida.

Requisitos de Implementação:
1. Siga estritamente a estrutura de pastas da Clean Architecture descrita na documentação.
2. Implemente o banco de dados utilizando um ORM moderno (Prisma/TypeORM para Node.js ou SQLModel/SQLAlchemy para Python).
3. Garanta que TODAS as queries possuam o filtro de 'empresa_id' para isolamento multi-tenant.
4. Implemente os DTOs com validação de entrada rigorosa (usando Zod/class-validator para Node.js ou Pydantic para Python).
5. Escreva o algoritmo de intercalação preferencial exatamente como especificado no Use Case 2.
6. Implemente a comunicação em tempo real via WebSockets para os eventos 'senha_chamada' e 'fila_atualizada'.
7. Adicione tratamento de erros global mapeando exceções de negócio para códigos HTTP corretos.
8. Escreva testes unitários para o caso de uso de intercalação de senhas.
```
