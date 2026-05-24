@php
    $data = $this->painelData;
    $empresa = $data['empresa'];
    $painelAtual = $data['painelAtual'];
    $temVideos = count($data['videos'] ?? []) > 0;
@endphp
<div
    class="view active"
    x-data="{
        speechSupported: false,
        hasVideos: @js($temVideos),
        needsUnlock: false,
        unlocked: false,
        unlocking: false,
        unlockError: false,
        init() {
            this.speechSupported = window.filaflowIsSpeechSupported?.() ?? false;
            this.needsUnlock = this.speechSupported || this.hasVideos;
            this.unlocked = ! this.needsUnlock || (window.filaflowIsPainelMediaUnlocked?.() ?? false);
        },
        async iniciarPainel() {
            if (! this.needsUnlock || this.unlocked || this.unlocking) {
                return;
            }
            this.unlocking = true;
            this.unlockError = false;
            try {
                window.filaflowEnablePainelVideoAudio?.();

                if (this.speechSupported) {
                    await window.filaflowUnlockPainelSpeech();
                }

                this.unlocked = true;
            } catch {
                if (window.filaflowIsPainelMediaUnlocked?.()) {
                    this.unlocked = true;
                } else {
                    this.unlockError = true;
                }
            } finally {
                this.unlocking = false;
            }
        },
    }"
>
    <div
        x-show="needsUnlock && ! unlocked"
        x-cloak
        class="painel-speech-unlock"
        role="dialog"
        aria-modal="true"
        aria-labelledby="painel-speech-unlock-title"
    >
        <div class="painel-speech-unlock-card">
            <span class="painel-speech-unlock-icon" aria-hidden="true">🔊</span>
            <h2 id="painel-speech-unlock-title" class="painel-speech-unlock-title">{{ __('Ativar áudio do painel') }}</h2>
            <p class="painel-speech-unlock-text">
                {{ __('Toque no botão abaixo para habilitar o áudio dos vídeos e os avisos por voz neste dispositivo. Necessário apenas uma vez por sessão do navegador.') }}
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

            @if (count($data['videos']) > 0)
                <div
                    wire:ignore
                    class="painel-videos"
                    data-playlist='@json($data['videos'])'
                    x-data="window.painelVideoPlayer(JSON.parse($el.dataset.playlist || '[]'))"
                    x-init="init()"
                >
                    <div class="painel-videos-frame">
                        <video
                            x-ref="player"
                            class="painel-videos-player"
                            muted
                            playsinline
                            autoplay
                            preload="auto"
                        ></video>
                    </div>
                </div>
            @else
                <div class="painel-videos">
                    <div class="painel-videos-empty">
                        <span class="painel-videos-empty-icon" aria-hidden="true">▶</span>
                        <p>{{ __('Nenhum vídeo em') }} <code>storage/videos</code></p>
                    </div>
                </div>
            @endif
        </div>

        <div class="painel-historico">
            @forelse (array_slice($data['historico'], 0, 4) as $idx => $item)
                <div class="painel-historico-card {{ $idx === 0 ? 'first' : '' }}" wire:key="hist-{{ $item['codigo'] }}-{{ $item['hora'] }}">
                    <div class="painel-historico-meta">
                        {{ ($item['tipo'] ?? 'guiche') === 'consultorio' ? __('Consultório') : __('Guichê') }}
                        {{ $item['local'] ?? ($item['guiche'] ?? '') }}
                    </div>
                    <div class="painel-historico-codigo">{{ $item['codigo'] }}</div>
                    <div class="painel-historico-servico">{{ $item['servico'] }}</div>
                    <div class="painel-historico-hora">{{ $item['hora'] }}</div>
                </div>
            @empty
                <div class="painel-historico-empty">{{ __('Nenhuma chamada ainda') }}</div>
            @endforelse
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
