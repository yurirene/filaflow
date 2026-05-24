@php
    $data = $this->painelData;
    $empresa = $data['empresa'];
    $painelAtual = $data['painelAtual'];
@endphp
<div class="view active">
    <x-fila.echo-listener on-fila="onFilaAtualizada" on-senha="onSenhaChamada" />
    <div class="painel-container">
        <div class="painel-header">
            <div class="painel-brand">
                <span class="painel-logo">⚕</span>
                <span class="painel-clinic">{{ $empresa->nome }}</span>
            </div>
            <div
                class="painel-clock-area"
                x-data="{
                    clock: '--:--:--',
                    date: '',
                    tick() {
                        const now = new Date();
                        this.clock = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        this.date = now.toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                    },
                    init() {
                        this.tick();
                        setInterval(() => this.tick(), 1000);
                    },
                }"
            >
                <div class="painel-clock" x-text="clock"></div>
                <div class="painel-date" x-text="date"></div>
            </div>
            <div class="painel-selector">
                <label>{{ __('Ala') }}:</label>
                <select wire:model.live="ala">
                    <option value="all">{{ __('Todas as alas') }}</option>
                    @foreach ($data['alas'] as $alaItem)
                        <option value="{{ $alaItem->id }}">{{ $alaItem->nome }}</option>
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
                <div class="painel-current-number" wire:key="num-{{ $painelAtual['codigo'] }}">
                    {{ $painelAtual['codigo'] }}
                </div>
                <div class="painel-current-service">{{ $painelAtual['servico'] }}</div>
                <div class="painel-current-guiche">
                    <span class="guiche-label">
                        {{ ($painelAtual['tipo'] ?? 'guiche') === 'consultorio' ? __('CONSULTÓRIO') : __('GUICHÊ') }}
                    </span>
                    <span class="guiche-number">{{ $painelAtual['local'] ?? ($painelAtual['guiche'] ?? '--') }}</span>
                </div>
                @if ($painelAtual['tipo'] === 'consultorio')
                    <div class="painel-current-service">{{ $painelAtual['paciente'] }}</div>
                @endif
                <div class="painel-alert-ring" x-ref="ring"></div>
            </div>

            <div class="painel-history">
                <div class="painel-history-title">{{ __('ÚLTIMAS CHAMADAS') }}</div>
                <div class="painel-history-list">
                    @forelse (array_slice($data['historico'], 0, 8) as $idx => $item)
                        <div class="history-item {{ $idx === 0 ? 'first' : '' }}" wire:key="hist-{{ $item['codigo'] }}-{{ $item['hora'] }}">
                            <span class="history-num">{{ $item['codigo'] }}</span>
                            <div class="history-info">
                                <div class="history-service">{{ $item['servico'] }}</div>
                                <div class="history-guiche">
                                    {{ ($item['tipo'] ?? 'guiche') === 'consultorio' ? __('Consultório') : __('Guichê') }}
                                    {{ $item['local'] ?? ($item['guiche'] ?? '') }}
                                </div>
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
            @foreach ($data['servicos'] as $svc)
                @php
                    $resumo = $data['filasResumo'][$svc->id] ?? ['tamanho' => 0, 'esperaMin' => 1];
                @endphp
                <div class="painel-queue-card" wire:key="queue-{{ $svc->id }}">
                    <div class="queue-card-title">{{ $svc->nome }}</div>
                    <div class="queue-card-count">{{ $resumo['tamanho'] }}</div>
                    <div class="queue-card-label">{{ __('na fila') }}</div>
                    <div class="queue-card-wait">~{{ $resumo['esperaMin'] }} min</div>
                </div>
            @endforeach
        </div>

        <div class="painel-ticker">
            <div class="ticker-content">
                {{ $empresa->ticker }}
                &nbsp;•&nbsp;
                {{ __('Horário de atendimento') }}: {{ $empresa->hora_inicio }} {{ __('às') }} {{ $empresa->hora_fim }}
                &nbsp;•&nbsp;
                {{ __('Respeite a ordem de chamada') }}
                &nbsp;•&nbsp;
            </div>
        </div>
    </div>
</div>
