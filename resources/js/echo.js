import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const pageProtocol = window.location.protocol === 'https:' ? 'https' : 'http';
const envScheme = import.meta.env.VITE_REVERB_SCHEME;
const envHost = import.meta.env.VITE_REVERB_HOST;
const envPort = Number(import.meta.env.VITE_REVERB_PORT || 0);
const pageHost = window.location.hostname;
const isLocalPage = ['localhost', '127.0.0.1'].includes(pageHost);
const scheme = envScheme || pageProtocol;
const host = isLocalPage
    ? pageHost
    : ((!envHost || envHost.includes('${')) ? pageHost : envHost);
const port = isLocalPage
    ? (envPort || 8080)
    : (scheme === 'https' ? 443 : 80);

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: host,
    wsPort: port,
    wssPort: port,
    forceTLS: scheme === 'https',
    authEndpoint: '/broadcasting/auth',
    enabledTransports: ['ws', 'wss'],
});
