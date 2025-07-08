import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
// Configure Reverb for real-time updates
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'your-reverb-key',
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT || 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    disableStats: true,
    enableLogging: true // Enable Reverb debugging
});

// Log when Echo is ready
console.log('Echo initialized with Reverb config:', {
    key: import.meta.env.VITE_REVERB_APP_KEY || 'your-reverb-key',
    host: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    port: import.meta.env.VITE_REVERB_PORT || 8080
});