@php
    $painel = $this->medicoPainel;
    $empresa = $painel['empresa'] ?? null;
    $senhaAtual = $this->senhaAtualModel;
@endphp
<div class="view active operador-view">
    <x-fila.echo-listener />
    <header class="operador-topbar">
        <div class="operador-topbar-brand">
            <span class="operador-topbar-logo">🩺</span>
            <span>{{ $empresa?->nome ?? config('app.name') }}</span>
        </div>
        <div class="operador-topbar-actions">
            <button type="button" class="btn-tiny" wire:click="$set('showPasswordModal', true)">{{ __('Trocar senha') }}</button>
            <form method="POST" action="{{ route('medico.logout') }}">
                @csrf
                <button type="submit" class="btn-tiny">{{ __('Sair') }}</button>
            </form>
        </div>
    </header>

    <div class="operador-container">
        <div class="operador-sidebar">
            <div class="op-profile">
                <div class="op-avatar">{{ $this->medicoLogado->iniciais() }}</div>
                <div class="op-info">
                    <div class="op-name">{{ $this->medicoLogado->nome }}</div>
                    <div class="op-guiche">{{ $this->consultorioLabel }}</div>
                </div>
            </div>

            <div class="op-stats">
                <div class="op-stat-item">
                    <span class="op-stat-value">{{ $painel['atendidosHoje'] ?? 0 }}</span>
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
        </div>

        <div class="operador-main">
            <div class="op-current-card">
                <div class="op-current-label">{{ __('EM ATENDIMENTO') }}</div>
                <div class="op-current-number">{{ $senhaAtual?->codigo ?? '---' }}</div>
                @if ($senhaAtual?->paciente_nome)
                    <div class="op-current-meta">
                        <span class="op-current-service">{{ $senhaAtual->paciente_nome }}</span>
                    </div>
                @endif
                <div class="op-current-meta">
                    @if ($senhaAtual)
                        <span class="op-current-service">{{ $senhaAtual->servico?->nome }}</span>
                        <span class="op-current-priority">
                            {{ $senhaAtual->is_preferencial
                                ? $senhaAtual->prioridade->label()
                                : __('Normal') }}
                        </span>
                    @else
                        <span class="op-current-service">—</span>
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
                        @foreach (['all' => __('Todos'), 'preferencial' => __('Preferencial'), 'normal' => __('Normal')] as $f => $label)
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
        </div>

        <div class="operador-log">
            <div class="op-log-header">
                <h4>{{ __('Histórico do Turno') }}</h4>
                <button type="button" class="btn-tiny" wire:click="clearLog">{{ __('Limpar') }}</button>
            </div>
            <div class="op-log-list">
                @forelse (array_reverse(\App\Fila\MedicoSessao::log()) as $entry)
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
