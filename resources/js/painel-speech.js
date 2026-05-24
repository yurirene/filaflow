const MODO_CONSULTORIO = 'consultorio';
const MODO_GUICHE = 'guiche';
const UNLOCK_STORAGE_KEY = 'filaflow_painel_speech_unlocked';
const WARMUP_TEXT = 'Painel de chamadas iniciado.';

let unlocked = false;
let pendingPainel = null;
let activeChamadaSpeechId = 0;

function localNumber(local) {
    const parsed = parseInt(local, 10);

    return Number.isNaN(parsed) ? local : parsed;
}

function pickPtVoice() {
    const voices = window.speechSynthesis.getVoices();

    return voices.find((voice) => voice.lang === 'pt-BR')
        ?? voices.find((voice) => voice.lang.startsWith('pt'))
        ?? null;
}

function buildTexto({ paciente, local, codigo, tipo }) {
    const localNum = localNumber(local);
    const pacienteTrim = (paciente ?? '').trim();

    if (tipo === MODO_CONSULTORIO && pacienteTrim) {
        return `Paciente ${pacienteTrim}, favor dirigir-se ao consultório ${localNum}. Senha ${codigo}.`;
    }

    if (tipo === MODO_CONSULTORIO) {
        return `Senha ${codigo}, favor dirigir-se ao consultório ${localNum}.`;
    }

    return `Senha ${codigo}, favor dirigir-se ao guichê ${localNum}.`;
}

function shouldFalarChamada(painel) {
    if (! painel || (painel.tipo !== MODO_CONSULTORIO && painel.tipo !== MODO_GUICHE)) {
        return false;
    }

    const codigo = (painel.codigo ?? '').trim();

    return codigo !== '' && codigo !== '---';
}

export function isSpeechSupported() {
    return typeof window !== 'undefined' && 'speechSynthesis' in window;
}

export function isPainelSpeechUnlocked() {
    if (unlocked) {
        return true;
    }

    try {
        return sessionStorage.getItem(UNLOCK_STORAGE_KEY) === '1';
    } catch {
        return false;
    }
}

function persistUnlocked() {
    unlocked = true;

    try {
        sessionStorage.setItem(UNLOCK_STORAGE_KEY, '1');
    } catch {
        // sessionStorage indisponível (modo privado restrito)
    }

    window.filaflowEnablePainelVideoAudio?.();
}

function speakText(text, { volume = 1, chamada = false } = {}) {
    return new Promise((resolve, reject) => {
        if (! isSpeechSupported()) {
            reject(new Error('speech_not_supported'));

            return;
        }

        const speechId = chamada ? ++activeChamadaSpeechId : null;

        if (chamada) {
            window.dispatchEvent(new CustomEvent('painel-speech-start'));
        }

        window.speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'pt-BR';
        utterance.rate = 0.92;
        utterance.volume = volume;

        const voice = pickPtVoice();

        if (voice) {
            utterance.voice = voice;
        }

        const finish = (callback) => {
            if (chamada && speechId === activeChamadaSpeechId) {
                window.dispatchEvent(new CustomEvent('painel-speech-end'));
            }

            callback();
        };

        utterance.onend = () => finish(() => resolve());
        utterance.onerror = (event) => finish(() => reject(event.error ?? new Error('speech_error')));

        window.speechSynthesis.speak(utterance);
    });
}

function ensureVoicesLoaded(callback) {
    if (! isSpeechSupported()) {
        return;
    }

    if (window.speechSynthesis.getVoices().length > 0) {
        callback();

        return;
    }

    window.speechSynthesis.addEventListener('voiceschanged', callback, { once: true });
}

function preloadVoices() {
    ensureVoicesLoaded(() => pickPtVoice());
}

function shouldAutoUnlockFromUrl() {
    return new URLSearchParams(window.location.search).get('auto_speech') === '1';
}

export function falarChamadaPainel(painel) {
    if (! isSpeechSupported() || ! shouldFalarChamada(painel)) {
        return;
    }

    if (! isPainelSpeechUnlocked()) {
        pendingPainel = painel;

        return;
    }

    ensureVoicesLoaded(() => {
        speakText(buildTexto(painel), { chamada: true }).catch(() => {});
    });
}

export function unlockPainelSpeech() {
    return new Promise((resolve, reject) => {
        if (! isSpeechSupported()) {
            reject(new Error('speech_not_supported'));

            return;
        }

        ensureVoicesLoaded(() => {
            speakText(WARMUP_TEXT)
                .then(() => {
                    persistUnlocked();
                    resolve(true);

                    if (pendingPainel) {
                        const painel = pendingPainel;
                        pendingPainel = null;
                        falarChamadaPainel(painel);
                    }
                })
                .catch(reject);
        });
    });
}

export function initPainelSpeech() {
    if (! isSpeechSupported()) {
        window.filaflowPainelSpeech = () => {};
        window.filaflowIsPainelSpeechUnlocked = () => false;
        window.filaflowIsSpeechSupported = () => false;
        window.filaflowUnlockPainelSpeech = () => Promise.reject(new Error('speech_not_supported'));

        return;
    }

    if (shouldAutoUnlockFromUrl() || isPainelSpeechUnlocked()) {
        unlocked = isPainelSpeechUnlocked() || shouldAutoUnlockFromUrl();

        if (shouldAutoUnlockFromUrl()) {
            persistUnlocked();
        }

        preloadVoices();
    }

    window.filaflowIsSpeechSupported = isSpeechSupported;
    window.filaflowIsPainelSpeechUnlocked = isPainelSpeechUnlocked;
    window.filaflowUnlockPainelSpeech = unlockPainelSpeech;
    window.filaflowPainelSpeech = (painel) => {
        ensureVoicesLoaded(() => falarChamadaPainel(painel));
    };
}
