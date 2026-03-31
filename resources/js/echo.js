import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const runtimeConfigSource = document.querySelector('[data-customer-chat-widget], [data-staff-chat-widget]');
const runtimeKey = runtimeConfigSource?.dataset.reverbKey;
const runtimeHost = runtimeConfigSource?.dataset.reverbHost;
const runtimePort = Number(runtimeConfigSource?.dataset.reverbPort || 0);
const runtimeScheme = runtimeConfigSource?.dataset.reverbScheme;

const pageProtocol = window.location.protocol === 'https:' ? 'https' : 'http';
const envScheme = import.meta.env.VITE_REVERB_SCHEME;
const envHost = import.meta.env.VITE_REVERB_HOST;
const envPort = Number(import.meta.env.VITE_REVERB_PORT || 0);
const pageHost = window.location.hostname;
const isLocalPage = ['localhost', '127.0.0.1'].includes(pageHost);
const scheme = runtimeScheme || envScheme || pageProtocol;
const host = isLocalPage
    ? pageHost
    : (runtimeHost || (!envHost || envHost.includes('${') ? pageHost : envHost));
const port = isLocalPage
    ? (runtimePort || envPort || 8080)
    : (runtimePort || envPort || (scheme === 'https' ? 443 : 80));

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: runtimeKey || import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: host,
    wsPort: port,
    wssPort: port,
    forceTLS: scheme === 'https',
    authEndpoint: '/broadcasting/auth',
    enabledTransports: ['ws', 'wss'],
});
