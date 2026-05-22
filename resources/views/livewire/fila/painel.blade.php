<div class="view active" wire:poll.2s="tickClock">
    <div class="painel-container">
        <div class="painel-header">
            <div class="painel-brand">
                <span class="painel-logo">⚕</span>
                <span class="painel-clinic">{{ $this->filaState['clinicName'] }}</span>
            </div>
            <div class="painel-clock-area">
                <div class="painel-clock">{{ $clock }}</div>
                <div class="painel-date">{{ $date }}</div>
            </div>
            <div class="painel-selector">
                <label>{{ __('Ala') }}:</label>
                <select wire:model.live="ala">
                    <option value="all">{{ __('Todas as Alas') }}</option>
                    @foreach ($this->filaState['servicos'] as $svc)
                        <option value="{{ $svc['id'] }}">{{ $svc['nome'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="painel-main">
            <div
                class="painel-current"
                x-data
                x-on:painel-alert.window="$refs.ring.classList.remove('animate'); void $refs.ring.offsetWidth; $refs.ring.classList.add('animate'); setTimeout(() => $refs.ring.classList.remove('animate'), 4500)"
            >
                <div class="painel-current-label">{{ __('CHAMANDO AGORA') }}</div>
                <div class="painel-current-number" wire:key="num-{{ $this->filaState['painelAtual']['codigo'] }}">
                    {{ $this->filaState['painelAtual']['codigo'] }}
                </div>
                <div class="painel-current-service">{{ $this->filaState['painelAtual']['servico'] }}</div>
                <div class="painel-current-guiche">
                    <span class="guiche-label">{{ __('GUICHÊ') }}</span>
                    <span class="guiche-number">{{ $this->filaState['painelAtual']['guiche'] }}</span>
                </div>
                <div class="painel-alert-ring" x-ref="ring"></div>
            </div>

            <div class="painel-history">
                <div class="painel-history-title">{{ __('ÚLTIMAS CHAMADAS') }}</div>
                <div class="painel-history-list">
                    @forelse (array_slice($this->filaState['historico'], 0, 8) as $idx => $item)
                        <div class="history-item {{ $idx === 0 ? 'first' : '' }}" wire:key="hist-{{ $item['codigo'] }}-{{ $item['hora'] }}">
                            <span class="history-num">{{ $item['codigo'] }}</span>
                            <div class="history-info">
                                <div class="history-service">{{ $item['servico'] }}</div>
                                <div class="history-guiche">{{ __('Guichê') }} {{ $item['guiche'] }}</div>
                            </div>
                            <span class="history-time">{{ $item['hora'] }}</span>
                        </div>
                    @empty
                        <div style="color: rgba(255,255,255,.4); font-size: 14px;">{{ __('Nenhuma chamada ainda') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="painel-queues">
            @foreach ($this->filaState['servicos'] as $svc)
                @php
                    $fila = $this->filaState['filas'][$svc['id']] ?? [];
                    $espera = \App\Support\FilaState::calcEspera($this->filaState, $svc['id']);
                    $hidden = $ala !== 'all' && $ala !== $svc['id'];
                @endphp
                @if (! $hidden)
                    <div class="painel-queue-card" wire:key="queue-{{ $svc['id'] }}">
                        <div class="queue-card-title">{{ $svc['nome'] }}</div>
                        <div class="queue-card-count">{{ count($fila) }}</div>
                        <div class="queue-card-label">{{ __('na fila') }}</div>
                        <div class="queue-card-wait">~{{ $espera }} min</div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="painel-ticker">
            <div class="ticker-content">
                {{ $this->filaState['config']['ticker'] }}
                &nbsp;•&nbsp;
                {{ __('Horário de atendimento') }}: {{ $this->filaState['config']['horaInicio'] }} {{ __('às') }} {{ $this->filaState['config']['horaFim'] }}
                &nbsp;•&nbsp;
                {{ __('Respeite a ordem de chamada') }}
                &nbsp;•&nbsp;
            </div>
        </div>
    </div>
</div>
