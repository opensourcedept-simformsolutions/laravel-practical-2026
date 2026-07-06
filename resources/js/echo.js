import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: window.reverbKey || import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: window.location.hostname || import.meta.env.VITE_REVERB_HOST || '127.0.0.1',
    wsPort: import.meta.env.VITE_REVERB_PORT || 9000,
    wssPort: import.meta.env.VITE_REVERB_PORT || 9000,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});
