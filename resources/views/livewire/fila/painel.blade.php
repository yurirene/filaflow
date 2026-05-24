@php
    $data = $this->painelData;
    $empresa = $data['empresa'];
    $painelAtual = $data['painelAtual'];
@endphp
<div
    class="view active"
    x-data="{
        speechSupported: false,
        unlocked: false,
        unlocking: false,
        unlockError: false,
        init() {
            this.speechSupported = window.filaflowIsSpeechSupported?.() ?? false;
            this.unlocked = ! this.speechSupported || (window.filaflowIsPainelSpeechUnlocked?.() ?? false);
        },
        async iniciarPainel() {
            if (! this.speechSupported || this.unlocked || this.unlocking) {
                return;
            }
            this.unlocking = true;
            this.unlockError = false;
            try {
                await window.filaflowUnlockPainelSpeech();
                this.unlocked = true;
            } catch {
                this.unlockError = true;
            } finally {
                this.unlocking = false;
            }
        },
    }"
>
    <div
        x-show="speechSupported && ! unlocked"
        x-cloak
        class="painel-speech-unlock"
        role="dialog"
        aria-modal="true"
        aria-labelledby="painel-speech-unlock-title"
    >
        <div class="painel-speech-unlock-card">
            <span class="painel-speech-unlock-icon" aria-hidden="true">🔊</span>
            <h2 id="painel-speech-unlock-title" class="painel-speech-unlock-title">{{ __('Ativar painel de chamadas') }}</h2>
            <p class="painel-speech-unlock-text">
                {{ __('Toque no botão abaixo para habilitar os avisos por voz neste dispositivo. Necessário apenas uma vez por sessão do navegador.') }}
            </p>
            <button
                type="button"
                class="painel-speech-unlock-btn"
                x-on:click="iniciarPainel()"
                x-bind:disabled="unlocking"
            >
                <span x-show="! unlocking">{{ __('Iniciar painel') }}</span>
                <span x-show="unlocking">{{ __('Ativando...') }}</span>
            </button>
            <p x-show="unlockError" class="painel-speech-unlock-error" role="alert">
                {{ __('Não foi possível ativar o áudio. Verifique o volume do dispositivo e tente novamente.') }}
            </p>
            <p class="painel-speech-unlock-hint">
                {{ __('TV dedicada: use o script') }} <code>scripts/painel-kiosk.sh</code> {{ __('com') }} <code>?auto_speech=1</code> {{ __('para pular esta tela.') }}
            </p>
        </div>
    </div>

    <x-fila.echo-listener on-fila="onFilaAtualizada" on-senha="onSenhaChamada" :filterAla="true" />
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
                x-data="{
                    alertRing() {
                        this.$refs.ring.classList.remove('is-alerting');
                        void this.$refs.ring.offsetWidth;
                        this.$refs.ring.classList.add('is-alerting');
                        setTimeout(() => this.$refs.ring.classList.remove('is-alerting'), 4500);

                        const numberEl = this.$refs.currentNumber;
                        if (numberEl) {
                            numberEl.classList.remove('is-changing');
                            void numberEl.offsetWidth;
                            numberEl.classList.add('is-changing');
                            setTimeout(() => numberEl.classList.remove('is-changing'), 300);
                        }
                    },
                    onPainelAlert(event) {
                        this.alertRing();
                        window.filaflowPainelSpeech?.(event.detail?.painel);
                    },
                }"
                x-on:painel-alert.window="onPainelAlert($event)"
            >
                <div class="painel-current-label">{{ __('CHAMANDO AGORA') }}</div>
                <div class="painel-current-number" wire:key="num-{{ $painelAtual['codigo'] }}" x-ref="currentNumber">
                    {{ $painelAtual['codigo'] }}
                </div>
                <div class="painel-current-service">{{ $painelAtual['servico'] }}</div>
                <div class="painel-current-guiche">
                    <span class="guiche-label">
                        {{ ($painelAtual['tipo'] ?? 'guiche') === 'consultorio' ? __('CONSULTÓRIO') : __('GUICHÊ') }}
                    </span>
                    <span class="guiche-number">{{ $painelAtual['local'] ?? ($painelAtual['guiche'] ?? '--') }}</span>
                </div>
                @if ($painelAtual['tipo'] === 'consultorio' && ! empty($painelAtual['paciente']))
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
