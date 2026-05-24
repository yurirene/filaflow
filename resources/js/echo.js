import Echo from 'laravel-echo';

import Pusher from 'pusher-js';

const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;

if (pusherKey) {
    window.Pusher = Pusher;

    const host = import.meta.env.VITE_PUSHER_HOST;
    const useCustomHost = typeof host === 'string' && host.length > 0;
    const scheme = import.meta.env.VITE_PUSHER_SCHEME ?? (useCustomHost ? 'http' : 'https');
    const useTls = scheme === 'https';

    const echoConfig = {
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
        disableStats: true,
    };

    if (useCustomHost) {
        const port = Number(import.meta.env.VITE_PUSHER_PORT ?? (useTls ? 443 : 6001));

        echoConfig.wsHost = host;
        echoConfig.wsPort = port;
        echoConfig.wssPort = port;
        echoConfig.forceTLS = useTls;
        echoConfig.enabledTransports = useTls ? ['wss'] : ['ws'];
    } else {
        echoConfig.forceTLS = true;
    }

    window.Echo = new Echo(echoConfig);

    window.dispatchEvent(new CustomEvent('filaflow:echo-ready'));
}
