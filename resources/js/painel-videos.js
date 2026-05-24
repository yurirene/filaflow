const UNLOCK_STORAGE_KEY = 'filaflow_painel_speech_unlocked';

function shouldAutoUnlockFromUrl() {
    return new URLSearchParams(window.location.search).get('auto_speech') === '1';
}

export function isPainelMediaUnlocked() {
    if (shouldAutoUnlockFromUrl()) {
        return true;
    }

    try {
        return sessionStorage.getItem(UNLOCK_STORAGE_KEY) === '1';
    } catch {
        return false;
    }
}

export function persistPainelMediaUnlock() {
    try {
        sessionStorage.setItem(UNLOCK_STORAGE_KEY, '1');
    } catch {
        // sessionStorage indisponível
    }

    window.dispatchEvent(new CustomEvent('painel-audio-unlocked'));
}

export function enablePainelVideoAudio() {
    persistPainelMediaUnlock();
}

export function painelVideoPlayer(videos = []) {
    const playlist = Array.isArray(videos)
        ? videos.filter((url) => typeof url === 'string' && url.length > 0)
        : [];

    return {
        videos: playlist,
        index: 0,
        trocando: false,
        audioAtivo: isPainelMediaUnlocked(),
        pausadoPorSpeech: false,

        init() {
            if (this.total() === 0) {
                return;
            }

            const player = this.$refs.player;
            if (! player) {
                return;
            }

            player.playsInline = true;
            this.aplicarMute(player);

            player.addEventListener('ended', () => this.proximo());
            player.addEventListener('error', () => this.onErro());

            window.addEventListener('painel-audio-unlocked', () => {
                this.audioAtivo = true;
                this.aplicarMute(player);
                player.play().catch(() => {});
            });

            window.addEventListener('painel-speech-start', () => this.pausarPorSpeech());
            window.addEventListener('painel-speech-end', () => this.retomarPorSpeech());

            this.tocar();
        },

        total() {
            return this.videos.length;
        },

        proximo() {
            if (this.total() === 0) {
                return;
            }

            this.index = (Number(this.index) + 1) % this.total();
            this.tocar();
        },

        aplicarMute(player) {
            player.muted = ! this.audioAtivo;
            player.volume = this.audioAtivo ? 1 : 0;
        },

        pausarPorSpeech() {
            const player = this.$refs.player;

            if (! player || player.paused) {
                return;
            }

            this.pausadoPorSpeech = true;
            player.pause();
        },

        retomarPorSpeech() {
            const player = this.$refs.player;

            if (! player || ! this.pausadoPorSpeech) {
                return;
            }

            this.pausadoPorSpeech = false;
            player.play().catch(() => {});
        },

        tocar() {
            const player = this.$refs.player;
            const url = this.videos[this.index];

            if (! player || ! url) {
                return;
            }

            this.trocando = true;
            this.aplicarMute(player);
            player.src = url;
            player.load();

            if (this.pausadoPorSpeech) {
                this.trocando = false;

                return;
            }

            player.play()
                .then(() => {
                    this.trocando = false;
                })
                .catch(() => {
                    this.trocando = false;
                });
        },

        onErro() {
            const player = this.$refs.player;

            if (! player || this.trocando) {
                return;
            }

            if (player.error?.code === MediaError.MEDIA_ERR_ABORTED) {
                return;
            }

            this.proximo();
        },
    };
}

export function initPainelVideos() {
    window.painelVideoPlayer = painelVideoPlayer;
    window.filaflowEnablePainelVideoAudio = enablePainelVideoAudio;
    window.filaflowIsPainelMediaUnlocked = isPainelMediaUnlocked;

    if (isPainelMediaUnlocked()) {
        window.dispatchEvent(new CustomEvent('painel-audio-unlocked'));
    }
}
