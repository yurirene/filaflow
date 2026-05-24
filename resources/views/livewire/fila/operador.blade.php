@php
    $painel = $this->operadorPainel;
    $empresa = $painel['empresa'];
    $servicos = $painel['servicos'];
    $senhaAtual = $this->senhaAtualModel;
@endphp
<div class="view active operador-view">
    <x-fila.echo-listener />
    <header class="operador-topbar">
        <div class="operador-topbar-brand">
            <span class="operador-topbar-logo">⚕</span>
            <span>{{ $empresa->nome }}</span>
        </div>
        <div class="operador-modo-toggle">
            <button
                type="button"
                class="modo-btn {{ $modo === 'guiche' ? 'active' : '' }}"
                wire:click="alternarModo('guiche')"
            >{{ __('Guichê') }}</button>
            <button
                type="button"
                class="modo-btn {{ $modo === 'consultorio' ? 'active' : '' }}"
                wire:click="alternarModo('consultorio')"
            >{{ __('Consultório') }}</button>
        </div>
        <div class="operador-topbar-actions">
            <button type="button" class="btn-tiny" wire:click="$set('showPasswordModal', true)">{{ __('Trocar senha') }}</button>
            <form method="POST" action="{{ route('operador.logout') }}">
                @csrf
                <button type="submit" class="btn-tiny">{{ __('Sair') }}</button>
            </form>
        </div>
    </header>

    <div class="operador-container">
        <div class="operador-sidebar">
            <div class="op-profile">
                <div class="op-avatar">{{ $this->operadorLogado->iniciais() }}</div>
                <div class="op-info">
                    <div class="op-name">{{ $this->operadorLogado->nome }}</div>
                    @if ($modo === 'consultorio')
                        <div class="op-guiche">{{ $this->consultorioAtualLabel }}</div>
                    @else
                        <div class="op-guiche">{{ __('Guichê') }} <span>{{ str_pad((string) $guiche, 2, '0', STR_PAD_LEFT) }}</span></div>
                    @endif
                    <div class="op-service">{{ $this->servicoAtualNome }}</div>
                </div>
            </div>

            <div class="op-stats">
                <div class="op-stat-item">
                    <span class="op-stat-value">{{ $painel['atendidosHoje'] }}</span>
                    <span class="op-stat-label">{{ __('Atendidos hoje') }}</span>
                </div>
                <div class="op-stat-item">
                    <span class="op-stat-value">{{ $this->tMedio }}</span>
                    <span class="op-stat-label">{{ __('Tempo médio') }}</span>
                </div>
                <div class="op-stat-item">
                    <span class="op-stat-value">{{ $this->filaAguardando->count() }}</span>
                    <span class="op-stat-label">{{ __('Na fila') }}</span>
                </div>
            </div>

            @if ($modo === 'guiche')
                <div class="op-guiche-selector">
                    <label class="op-label">{{ __('Meu Guichê') }}</label>
                    <select wire:model.live="guiche">
                        @foreach ($this->guichesDaAlaAtual as $g)
                            <option value="{{ $g->numero }}">{{ __('Guichê') }} {{ str_pad((string) $g->numero, 2, '0', STR_PAD_LEFT) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="op-service-selector">
                    <label class="op-label">{{ __('Serviço') }}</label>
                    <select wire:model.live="servico">
                        @foreach ($servicos as $svc)
                            <option value="{{ $svc->id }}">{{ $svc->nome }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="op-guiche-selector">
                    <label class="op-label">{{ __('Consultório') }}</label>
                    <select wire:model.live="consultorio">
                        @foreach ($this->consultoriosDisponiveis as $c)
                            <option value="{{ $c->id }}">
                                {{ \App\Fila\MedicoSessao::labelConsultorio($c) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="op-service-selector">
                    <label class="op-label">{{ __('Serviço (opcional)') }}</label>
                    <select wire:model.live="servico">
                        <option value="">{{ __('Todos da fila') }}</option>
                        @foreach ($this->servicosConsultorio as $svc)
                            <option value="{{ $svc->id }}">{{ $svc->nome }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="op-intercalacao">
                <label class="op-label">{{ __('Intercalação ativa') }}</label>
                <div class="intercalacao-badge">{{ $this->intercalacaoBadge }}</div>
            </div>
        </div>

        <div class="operador-main">
            <div class="op-current-card">
                <div class="op-current-label">{{ __('EM ATENDIMENTO') }}</div>
                <div class="op-current-number">{{ $senhaAtual?->codigo ?? '---' }}</div>
                <div class="op-current-meta">
                    <span class="op-current-service">{{ $senhaAtual ? $this->servicoAtualNome : '—' }}</span>
                    @if ($senhaAtual)
                        <span class="op-current-priority">
                            {{ $senhaAtual->is_preferencial
                                ? $senhaAtual->prioridade->label()
                                : __('Normal') }}
                        </span>
                    @endif
                </div>
                <div class="op-current-timer">
                    <span class="timer-icon">⏱</span>
                    <x-fila.timer :chamada-em="$senhaAtual?->chamada_em" />
                </div>
                <div class="op-actions">
                    <button type="button" class="op-btn op-btn-call" wire:click="chamarProxima">
                        <span>📢</span> {{ __('Chamar Próxima') }}
                    </button>
                    <button type="button" class="op-btn op-btn-recall" wire:click="rechamarAtual" @disabled(! $this->temSenhaAtual)>
                        <span>🔁</span> {{ __('Rechamar') }}
                    </button>
                    @if ($modo === 'guiche')
                        <button type="button" class="op-btn op-btn-transfer" wire:click="abrirEncaminhar" @disabled(! $this->temSenhaAtual)>
                            <span>🏥</span> {{ __('Encaminhar') }}
                        </button>
                        <button type="button" class="op-btn op-btn-transfer" wire:click="abrirTransferir" @disabled(! $this->temSenhaAtual)>
                            <span>↗</span> {{ __('Trocar fila') }}
                        </button>
                    @endif
                    <button type="button" class="op-btn op-btn-finish" wire:click="finalizarAtendimento" @disabled(! $this->temSenhaAtual)>
                        <span>✓</span> {{ __('Finalizar') }}
                    </button>
                    <button type="button" class="op-btn op-btn-absent" wire:click="marcarAusente" @disabled(! $this->temSenhaAtual)>
                        <span>✗</span> {{ __('Ausente') }}
                    </button>
                </div>
            </div>

            <div class="op-queue-section">
                <div class="op-queue-header">
                    <h3>{{ __('Fila de Espera') }}</h3>
                    <div class="op-queue-filters">
                        @foreach (['all' => __('Todos'), 'preferencial' => __('Preferencial'), 'normal' => __('Normal'), 'agendado' => __('Agendado')] as $f => $label)
                            <button
                                type="button"
                                class="filter-btn {{ $queueFilter === $f ? 'active' : '' }}"
                                wire:click="filterQueue('{{ $f }}')"
                            >{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="op-queue-list">
                    @forelse ($this->filaFiltrada as $idx => $senha)
                        @php
                            $minutos = (int) now()->diffInMinutes($senha->emitida_em);
                        @endphp
                        <div class="queue-item" wire:key="fila-{{ $senha->id }}">
                            <span class="queue-item-pos">{{ $idx + 1 }}</span>
                            <span class="queue-item-num">{{ $senha->codigo }}</span>
                            <div class="queue-item-info">
                                @if ($senha->paciente_nome)
                                    <span class="queue-item-service">{{ $senha->paciente_nome }}</span>
                                @endif
                                <span class="queue-item-service">{{ $senha->servico?->nome ?? '' }}</span>
                                <span class="queue-item-time">{{ __('Aguardando há') }} {{ $minutos < 1 ? __('menos de 1') : $minutos }} min</span>
                            </div>
                            <span class="queue-item-badge {{ $senha->is_preferencial ? 'badge-preferencial' : 'badge-normal' }}">
                                {{ $senha->is_preferencial ? $senha->prioridade->label() : __('Normal') }}
                            </span>
                        </div>
                    @empty
                        <div class="queue-empty">{{ __('Fila vazia') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="op-schedule-section">
                <div class="op-schedule-header">
                    <h3>{{ __('Agendamentos de Hoje') }}</h3>
                </div>
                <div class="op-schedule-list">
                    @forelse ($painel['agendamentos'] as $ag)
                        <div class="schedule-item" wire:key="ag-{{ $ag->id }}">
                            <span class="schedule-time">{{ $ag->data_hora->format('H:i') }}</span>
                            <div class="schedule-info">
                                <span class="schedule-name">{{ $ag->paciente_nome }}</span>
                                <span class="schedule-service">{{ $ag->servico?->nome }}</span>
                            </div>
                            <span class="schedule-status status-{{ \App\Fila\Queries\OperadorPainelQuery::agendamentoStatusLabel($ag->status) }}">{{ $ag->status->value }}</span>
                        </div>
                    @empty
                        <div class="queue-empty">{{ __('Nenhum agendamento') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="operador-log">
            <div class="op-log-header">
                <h4>{{ __('Histórico do Turno') }}</h4>
                <button type="button" class="btn-tiny" wire:click="clearLog">{{ __('Limpar') }}</button>
            </div>
            <div class="op-log-list">
                @forelse (array_reverse(\App\Fila\OperadorSessao::log()) as $entry)
                    <div class="log-item log-{{ $entry['tipo'] ?? 'info' }}" wire:key="log-{{ $entry['hora'] }}-{{ $entry['msg'] }}">
                        <div class="log-time">{{ $entry['hora'] }}</div>
                        <div class="log-text">{{ $entry['msg'] }}</div>
                    </div>
                @empty
                    <div class="queue-empty">{{ __('Nenhum registro') }}</div>
                @endforelse
            </div>
        </div>
    </div>

    @if ($showEncaminharModal)
        @teleport('body')
        <div class="modal-overlay" wire:click.self="$set('showEncaminharModal', false)">
            <div class="modal" role="dialog" aria-modal="true">
                <div class="modal-header">
                    <h3>{{ __('Encaminhar para consultório') }}</h3>
                    <button type="button" class="modal-close" wire:click="$set('showEncaminharModal', false)">✕</button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Senha') }}: <strong>{{ $senhaAtual?->codigo ?? '---' }}</strong></p>
                    <div class="modal-field">
                        <label>{{ __('Ala') }}</label>
                        <select wire:model.live="encAla">
                            @forelse ($this->alasConsultorio as $ala)
                                <option value="{{ $ala->id }}">{{ $ala->nome }}</option>
                            @empty
                                <option value="">{{ __('Nenhuma ala de consultório') }}</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="modal-field">
                        <label>{{ __('Consultório') }}</label>
                        <select wire:model.live="encConsultorio">
                            @foreach ($this->consultoriosEncaminhar as $c)
                                <option value="{{ $c->id }}">
                                    {{ \App\Fila\MedicoSessao::labelConsultorio($c) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-field">
                        <label>{{ __('Serviço de destino') }}</label>
                        <select wire:model="encServico">
                            @foreach ($this->servicosEncaminhar as $svc)
                                <option value="{{ $svc->id }}">{{ $svc->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-field">
                        <label for="enc-paciente-nome">{{ __('Nome completo do paciente') }} <span class="text-red-600">*</span></label>
                        <input
                            id="enc-paciente-nome"
                            type="text"
                            wire:model="encPacienteNome"
                            placeholder="{{ __('Ex: Maria da Silva Santos') }}"
                            autocomplete="name"
                            required
                        />
                        @error('encPacienteNome')
                            <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-primary" wire:click="confirmarEncaminhamento">{{ __('Confirmar') }}</button>
                    <button type="button" class="btn-secondary" wire:click="$set('showEncaminharModal', false)">{{ __('Cancelar') }}</button>
                </div>
            </div>
        </div>
        @endteleport
    @endif

    @if ($showTransferModal)
        @teleport('body')
        <div class="modal-overlay" wire:click.self="$set('showTransferModal', false)">
            <div class="modal" role="dialog" aria-modal="true">
                <div class="modal-header">
                    <h3>{{ __('Trocar fila no guichê') }}</h3>
                    <button type="button" class="modal-close" wire:click="$set('showTransferModal', false)">✕</button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Senha') }}: <strong>{{ $senhaAtual?->codigo ?? '---' }}</strong></p>
                    <p class="modal-hint">{{ __('A senha permanece na recepção (guichê), apenas muda de serviço.') }}</p>
                    <div class="modal-field">
                        <label>{{ __('Ala') }}</label>
                        <select wire:model.live="transferAla">
                            @foreach ($this->alasAtivas as $ala)
                                <option value="{{ $ala->id }}">{{ $ala->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-field">
                        <label>{{ __('Novo serviço') }}</label>
                        <select wire:model="transferServico">
                            @foreach ($this->servicosTransferir as $svc)
                                <option value="{{ $svc->id }}">{{ $svc->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-field">
                        <label>{{ __('Motivo (opcional)') }}</label>
                        <input type="text" wire:model="transferMotivo" placeholder="{{ __('Ex: Encaminhamento médico') }}" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-primary" wire:click="confirmarTransferencia">{{ __('Confirmar') }}</button>
                    <button type="button" class="btn-secondary" wire:click="$set('showTransferModal', false)">{{ __('Cancelar') }}</button>
                </div>
            </div>
        </div>
        @endteleport
    @endif

    @if ($showPasswordModal)
        @teleport('body')
        <div class="modal-overlay" wire:click.self="$set('showPasswordModal', false)">
            <div class="modal" role="dialog" aria-modal="true">
                <div class="modal-header">
                    <h3>{{ __('Trocar senha') }}</h3>
                    <button type="button" class="modal-close" wire:click="$set('showPasswordModal', false)">✕</button>
                </div>
                <form wire:submit="trocarSenha" class="modal-body">
                    <div class="modal-field">
                        <label>{{ __('Senha atual') }}</label>
                        <input type="password" wire:model="senhaAtual" required autocomplete="current-password" />
                        @error('senhaAtual')<span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>@enderror
                    </div>
                    <div class="modal-field">
                        <label>{{ __('Nova senha') }}</label>
                        <input type="password" wire:model="senhaNova" required autocomplete="new-password" />
                        @error('senhaNova')<span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>@enderror
                    </div>
                    <div class="modal-field">
                        <label>{{ __('Confirmar nova senha') }}</label>
                        <input type="password" wire:model="senhaNovaConfirmation" required autocomplete="new-password" />
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn-primary">{{ __('Salvar senha') }}</button>
                        <button type="button" class="btn-secondary" wire:click="$set('showPasswordModal', false)">{{ __('Cancelar') }}</button>
                    </div>
                </form>
            </div>
        </div>
        @endteleport
    @endif
</div>
