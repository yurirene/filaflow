import Echo from 'laravel-echo';

import Pusher from 'pusher-js';

const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;

if (pusherKey) {
    window.Pusher = Pusher;

    const scheme = import.meta.env.VITE_PUSHER_SCHEME ?? 'http';
    const port = Number(import.meta.env.VITE_PUSHER_PORT ?? 6001);
    const useTls = scheme === 'https';

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
        wsHost: import.meta.env.VITE_PUSHER_HOST ?? 'localhost',
        wsPort: port,
        wssPort: port,
        forceTLS: useTls,
        enabledTransports: useTls ? ['wss'] : ['ws'],
        disableStats: true,
    });

    window.dispatchEvent(new CustomEvent('filaflow:echo-ready'));
}
