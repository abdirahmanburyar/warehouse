import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Configure Reverb for real-time updates
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'warehouse-key',
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT || 443,
    wssPort: import.meta.env.VITE_REVERB_PORT || 443,
    forceTLS: true,
    enabledTransports: ['wss'],
    authEndpoint: '/broadcasting/auth',
    disableStats: true,
    enableLogging: true,
    // Reverb specific options
    reverb: {
        appId: import.meta.env.VITE_REVERB_APP_ID || 'warehouse-app',
        appKey: import.meta.env.VITE_REVERB_APP_KEY || 'warehouse-key',
        appSecret: import.meta.env.VITE_REVERB_APP_SECRET || 'warehouse-secret',
    }
});

console.log(window.Echo);

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
