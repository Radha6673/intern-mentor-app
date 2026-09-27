import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

if (import.meta.env.VITE_REVERB_APP_KEY) {
    const isHttps = (import.meta.env.VITE_REVERB_SCHEME ?? (window.location.protocol === 'https:' ? 'https' : 'http')) === 'https';
    const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    
    // In production, connect through standard port (443 / 80) via Nginx proxy to Reverb
    const host = (import.meta.env.VITE_REVERB_HOST && !isLocalhost && import.meta.env.VITE_REVERB_HOST !== 'localhost')
        ? import.meta.env.VITE_REVERB_HOST
        : window.location.hostname;

    // In production, always use standard port (443 / 80) because Render only exposes port 443 to the public
    const port = isLocalhost
        ? (import.meta.env.VITE_REVERB_PORT ? Number(import.meta.env.VITE_REVERB_PORT) : 8085)
        : (isHttps ? 443 : 80);

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: host,
        wsPort: port,
        wssPort: port,
        forceTLS: isHttps,
        enabledTransports: ['ws', 'wss'],
    });
}
